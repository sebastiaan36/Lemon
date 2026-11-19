<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { useResizeObserver } from '@vueuse/core';
import type Hls from 'hls.js';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

defineOptions({ inheritAttrs: false });

const props = defineProps<{
    src?: string | null;
    /**
     * Weergavebreedte voor de poster-srcset, zoals `sizes` op een <img>.
     * Moet gelijk zijn aan de `preloadSizes` van de pagina als de poster in
     * de <head> wordt gepreload; anders de gemeten breedte van de video.
     */
    posterSizes?: string;
}>();

const page = usePage<{
    mediaSources?: Record<string, { poster?: string; posterSrcset?: string }>;
}>();

const posterSource = computed(() =>
    props.src ? page.props.mediaSources?.[props.src] : undefined,
);

const videoRef = ref<HTMLVideoElement | null>(null);

// Poster (lichte WebP of Bunny-thumbnail) staat direct in beeld terwijl de
// video laadt. Uit de srcset laten we de browser zelf kiezen via een <img>
// met dezelfde srcset/sizes als de preload, zodat hij uit de cache komt.
const poster = ref<string | undefined>();
const posterPosition = ref('50% 50%');

/**
 * De poster staat ook als achtergrond onder de video. Safari verbergt het
 * `poster`-beeld al zodra hls.js de stream koppelt, ruim voordat het eerste
 * frame er is; zonder achtergrond zie je dan tussendoor een zwart vlak.
 * Met `object-fit: cover` dekt het eerste videoframe de achtergrond af.
 */
const posterBackground = computed(() =>
    poster.value
        ? {
              backgroundImage: `url("${poster.value}")`,
              backgroundPosition: posterPosition.value,
              backgroundSize: 'cover',
              backgroundRepeat: 'no-repeat',
          }
        : undefined,
);

function choosePoster(): void {
    const source = posterSource.value;
    const el = videoRef.value;

    if (el) {
        posterPosition.value = getComputedStyle(el).objectPosition || '50% 50%';
    }

    if (!source?.posterSrcset || !el) {
        poster.value = source?.poster;

        return;
    }

    const width = Math.round(el.clientWidth);
    const picker = new Image();
    picker.sizes = props.posterSizes ?? (width > 0 ? `${width}px` : '100vw');
    picker.srcset = source.posterSrcset;
    picker.src = source.poster ?? '';
    picker.decode().then(
        () => {
            if (posterSource.value === source) {
                poster.value = picker.currentSrc || source.poster;
            }
        },
        () => {
            if (posterSource.value === source) {
                poster.value = source.poster;
            }
        },
    );
}

watch(posterSource, choosePoster);

let hls: Hls | null = null;
let loadToken = 0;

/** Schermscherpte telt hooguit 1,5× mee: zwaardere versies voegen op een telefoon weinig toe. */
const MAX_PIXEL_RATIO = 1.5;

/** Een versie mag tot 25% uitvergroot worden voordat een zwaardere nodig is. */
const UPSCALE_TOLERANCE = 1.25;

/** Nooit lager dan 480p: daaronder oogt een video op een telefoonscherm wazig. */
const MIN_LEVEL_HEIGHT = 480;

/**
 * Kleinste versie die (bijna) niet uitvergroot hoeft te worden. Houdt
 * rekening met `object-fit: cover`: dan telt de as die het meest wordt
 * bijgesneden (bij een liggende video in een staande kaart de hoogte).
 * hls.js' eigen capLevelToPlayerSize kijkt alleen naar de breedte en koos
 * daardoor op mobiel 360p/480p voor een schermvullende video.
 */
function fittingLevel(el: HTMLVideoElement, player: Hls): number | null {
    const ratio = Math.min(window.devicePixelRatio || 1, MAX_PIXEL_RATIO);
    const width = el.clientWidth * ratio;
    const height = el.clientHeight * ratio;

    if (width === 0 || height === 0 || player.levels.length === 0) {
        return null;
    }

    const cover = getComputedStyle(el).objectFit === 'cover';
    const bySize = player.levels
        .map((level, index) => ({ level, index }))
        .sort((a, b) => a.level.height - b.level.height);
    const fitting = bySize.find(({ level }) => {
        if (
            level.height < MIN_LEVEL_HEIGHT &&
            bySize.some((other) => other.level.height >= MIN_LEVEL_HEIGHT)
        ) {
            return false;
        }

        const scaleX = width / level.width;
        const scaleY = height / level.height;
        const needed = cover
            ? Math.max(scaleX, scaleY)
            : Math.min(scaleX, scaleY);

        return needed <= UPSCALE_TOLERANCE;
    });

    return fitting?.index ?? bySize[bySize.length - 1].index;
}

/**
 * Korte loops bestaan uit één segment: hls.js kiest één keer een versie en
 * herhaalt die daarna uit de buffer, zonder ooit op te schalen. Daarom
 * starten we direct op de passende versie en schakelen we meteen op zodra
 * het element groter wordt (kaart → volledig scherm).
 */
function updateLevelCap(): void {
    const el = videoRef.value;
    const level = el && hls ? fittingLevel(el, hls) : null;

    if (!hls || level === null) {
        return;
    }

    hls.autoLevelCapping = level;

    const current = hls.levels[hls.currentLevel];

    if (current && current.height < hls.levels[level].height) {
        hls.currentLevel = level;
    }
}

useResizeObserver(videoRef, updateLevelCap);

function destroyHls(): void {
    hls?.destroy();
    hls = null;
}

function isHlsSource(src: string): boolean {
    return src.toLowerCase().includes('.m3u8');
}

/**
 * Native HLS alleen waar geen MediaSource bestaat (iPhone-Safari). Chrome
 * meldt sinds kort ook native HLS-ondersteuning, maar die speler faalt op
 * Bunny-streams van 4K-bronnen (1440p-variant); hls.js speelt alles en
 * kiest bovendien een kwaliteit die past bij de grootte van de video.
 */
function prefersNativeHls(el: HTMLVideoElement): boolean {
    return (
        typeof window.MediaSource === 'undefined' &&
        el.canPlayType('application/vnd.apple.mpegurl') !== ''
    );
}

async function attachHls(
    el: HTMLVideoElement,
    src: string,
    token: number,
): Promise<boolean> {
    // hls.js (~500 KB) alleen laden wanneer het echt nodig is.
    const { default: HlsConstructor } = await import('hls.js');

    if (token !== loadToken || !HlsConstructor.isSupported()) {
        return false;
    }

    hls = new HlsConstructor({
        enableWorker: true,
        lowLatencyMode: false,
        // Start op een redelijke kwaliteit i.p.v. de laagste (standaard
        // 500 kbps-schatting gaf seconden lang een wazig beeld).
        abrEwmaDefaultEstimate: 3_000_000,
    });
    hls.on(HlsConstructor.Events.MANIFEST_PARSED, () => {
        const player = hls;
        const level = player ? fittingLevel(el, player) : null;

        if (player && level !== null) {
            player.autoLevelCapping = level;
            player.startLevel = level;
        }

        if (el.autoplay) {
            void el.play().catch(() => undefined);
        }
    });
    hls.loadSource(src);
    hls.attachMedia(el);

    return true;
}

async function initVideo(src: string | null | undefined): Promise<void> {
    destroyHls();
    const token = ++loadToken;
    const el = videoRef.value;

    if (!el || !src) {
        return;
    }

    if (!isHlsSource(src)) {
        el.src = src;

        return;
    }

    if (!prefersNativeHls(el) && (await attachHls(el, src, token))) {
        return;
    }

    if (token !== loadToken) {
        return;
    }

    // Native HLS (iPhone), met hls.js als vangnet als de native speler faalt.
    el.addEventListener(
        'error',
        () => {
            if (token === loadToken && !hls) {
                el.removeAttribute('src');
                void attachHls(el, src, token);
            }
        },
        { once: true },
    );
    el.src = src;
}

onMounted(() => {
    choosePoster();
    void initVideo(props.src);
});
onUnmounted(() => {
    loadToken++;
    destroyHls();
});
watch(
    () => props.src,
    (src) => {
        void initVideo(src);
    },
);

// Expose the raw video element so parent components can call play/pause
defineExpose({
    get videoEl(): HTMLVideoElement | null {
        return videoRef.value;
    },
});
</script>

<template>
    <video
        ref="videoRef"
        :poster="poster"
        :style="posterBackground"
        v-bind="$attrs"
    />
</template>
