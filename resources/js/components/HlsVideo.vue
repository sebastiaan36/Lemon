<script setup lang="ts">
import Hls from 'hls.js'
import { onMounted, onUnmounted, ref, watch } from 'vue'

defineOptions({ inheritAttrs: false })

const props = defineProps<{
    src?: string | null
}>()

const videoRef = ref<HTMLVideoElement | null>(null)
let hls: Hls | null = null

function destroyHls(): void {
    hls?.destroy()
    hls = null
}

function initVideo(src: string | null | undefined): void {
    destroyHls()
    const el = videoRef.value
    if (!el || !src) return

    if (src.includes('.m3u8')) {
        if (Hls.isSupported()) {
            hls = new Hls({ enableWorker: true, lowLatencyMode: false })
            hls.loadSource(src)
            hls.attachMedia(el)
        } else if (el.canPlayType('application/vnd.apple.mpegurl')) {
            // Safari native HLS
            el.src = src
        }
    } else {
        el.src = src
    }
}

onMounted(() => initVideo(props.src))
onUnmounted(destroyHls)
watch(() => props.src, initVideo)

// Expose the raw video element so parent components can call play/pause
defineExpose({
    get videoEl(): HTMLVideoElement | null {
        return videoRef.value
    },
})
</script>

<template>
    <video ref="videoRef" v-bind="$attrs" />
</template>
