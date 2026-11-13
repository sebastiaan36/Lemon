<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
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
        capLevelToPlayerSize: true,
    });
    hls.on(HlsConstructor.Events.MANIFEST_PARSED, () => {
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
