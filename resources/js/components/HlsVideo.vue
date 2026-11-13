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

async function initVideo(src: string | null | undefined): Promise<void> {
    destroyHls();
    const token = ++loadToken;
    const el = videoRef.value;

    if (!el || !src) {
        return;
    }

    if (
        !isHlsSource(src) ||
        el.canPlayType('application/vnd.apple.mpegurl') !== ''
    ) {
        // Gewone mp4/webm, of native HLS (Safari/iOS): geen hls.js nodig.
        el.src = src;

        return;
    }

    // hls.js (~500 KB) alleen laden wanneer het echt nodig is.
    const { default: HlsConstructor } = await import('hls.js');

    if (token !== loadToken || !HlsConstructor.isSupported()) {
        return;
    }

    hls = new HlsConstructor({ enableWorker: true, lowLatencyMode: false });
    hls.loadSource(src);
    hls.attachMedia(el);
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
