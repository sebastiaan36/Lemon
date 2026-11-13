<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { useResizeObserver } from '@vueuse/core';
import type Hls from 'hls.js';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

defineOptions({ inheritAttrs: false });

const props = defineProps<{
    src?: string | null;
}>();

const page = usePage<{
    mediaSources?: Record<string, { poster?: string }>;
}>();

// Bunny Stream-video's krijgen hun thumbnail als poster, zodat er direct
// beeld staat terwijl de stream start.
const poster = computed(() =>
    props.src ? page.props.mediaSources?.[props.src]?.poster : undefined,
);

const videoRef = ref<HTMLVideoElement | null>(null);
let hls: Hls | null = null;
let loadToken = 0;

/** Schermscherpte telt hooguit 2× mee: zwaardere versies voegen op een telefoon weinig toe. */
const MAX_PIXEL_RATIO = 2;

/**
 * Begrens de automatische kwaliteitskeuze op de kleinste versie die niet
 * uitvergroot hoeft te worden. Houdt rekening met `object-fit: cover`: dan
 * telt de as die het meest wordt bijgesneden (bij een liggende video in een
 * staande kaart de hoogte). hls.js' eigen capLevelToPlayerSize kijkt alleen
 * naar de breedte en koos daardoor op mobiel 360p voor een schermvullende video.
 */
function updateLevelCap(): void {
    const el = videoRef.value;

    if (!hls || !el || hls.levels.length === 0) {
        return;
    }

    const ratio = Math.min(window.devicePixelRatio || 1, MAX_PIXEL_RATIO);
    const width = el.clientWidth * ratio;
    const height = el.clientHeight * ratio;

    if (width === 0 || height === 0) {
        return;
    }

    const cover = getComputedStyle(el).objectFit === 'cover';
    const bySize = hls.levels
        .map((level, index) => ({ level, index }))
        .sort((a, b) => a.level.height - b.level.height);
    const fitting = bySize.find(({ level }) => {
        const scaleX = width / level.width;
        const scaleY = height / level.height;

        return (
            (cover ? Math.max(scaleX, scaleY) : Math.min(scaleX, scaleY)) <= 1
        );
    });

    hls.autoLevelCapping = fitting?.index ?? bySize[bySize.length - 1].index;
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
        updateLevelCap();

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
    <video ref="videoRef" :poster="poster" v-bind="$attrs" />
</template>
