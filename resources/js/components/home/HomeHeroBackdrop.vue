<script setup lang="ts">
import { computed } from 'vue';
import type { CSSProperties } from 'vue';
import CmsImage from '@/components/CmsImage.vue';
import HlsVideo from '@/components/HlsVideo.vue';
import {
    FIGMA_DESKTOP_HEIGHT,
    FIGMA_DESKTOP_WIDTH,
    MOBILE_LAYOUT_MAX_WIDTH,
    ease,
    isVideoUrl,
    lerp,
    mobileFrame,
    rangeProgress,
} from '@/lib/scrollAnimation';

const props = defineProps<{
    bgImage: string | null;
    heroProgress: number;
    hidden: boolean;
    viewportWidth: number;
    viewportHeight: number;
}>();

// Hero video: always position:fixed covering the full viewport (z-index 2, behind overlay content).
// A clip-path reveals only the card area initially and expands to full screen on scroll.
// State 1 (node 1:4):  clip = 716×419 at left:calc(16.67%+186.5px), top:201px, radius:20px
// State 2 (node 1:104): clip = none (full viewport)
const videoStyle = computed<CSSProperties>(() => {
    if (props.hidden) {
        return { display: 'none' };
    }

    const e = 1 - ease(rangeProgress(props.heroProgress, 0, 0.1));

    if (props.viewportWidth < MOBILE_LAYOUT_MAX_WIDTH) {
        return mobileVideoStyle(e);
    }

    const left = (459.8364 / FIGMA_DESKTOP_WIDTH) * props.viewportWidth;
    const top = (208.7368 / FIGMA_DESKTOP_HEIGHT) * props.viewportHeight;
    const width = (720.3271 / FIGMA_DESKTOP_WIDTH) * props.viewportWidth;
    const height = (426.9324 / FIGMA_DESKTOP_HEIGHT) * props.viewportHeight;
    const right = Math.max(0, props.viewportWidth - left - width);
    const bottom = Math.max(0, props.viewportHeight - top - height);
    const clipPath =
        e <= 0
            ? 'none'
            : `inset(${(top * e).toFixed(1)}px ${(right * e).toFixed(1)}px ${(bottom * e).toFixed(1)}px ${(left * e).toFixed(1)}px round ${(20 * e).toFixed(1)}px)`;

    return {
        position: 'fixed',
        top: '0',
        left: '0',
        width: '100vw',
        height: '100vh',
        zIndex: 2,
        overflow: 'hidden',
        clipPath,
        pointerEvents: 'none',
    };
});

// Mobiel (Figma node 1:3/1:4, 269×350 op x 135,6 / y 226 in het 540×900-frame):
// de video vult de kaart zelf en groeit bij scroll uit naar volledig scherm.
// Een clip-path-venster op een schermvullende video zou op een staand scherm
// alleen een sterk ingezoomd middenstuk van de (liggende) video tonen.
function mobileVideoStyle(e: number): CSSProperties {
    const frame = mobileFrame(props.viewportWidth, props.viewportHeight);
    const card = {
        left: frame.x(135.57),
        top: frame.y(226),
        width: 269.426 * frame.scale,
        height: 350.197 * frame.scale,
    };

    return {
        position: 'fixed',
        left: `${lerp(0, card.left, e).toFixed(1)}px`,
        top: `${lerp(0, card.top, e).toFixed(1)}px`,
        width: `${lerp(props.viewportWidth, card.width, e).toFixed(1)}px`,
        height: `${lerp(props.viewportHeight, card.height, e).toFixed(1)}px`,
        borderRadius: `${(15 * frame.scale * e).toFixed(1)}px`,
        zIndex: 2,
        overflow: 'hidden',
        pointerEvents: 'none',
    };
}

const overlayOpacity = computed(() =>
    ease(rangeProgress(props.heroProgress, 0.1, 0.35)),
);
const scrollIndicatorOpacity = computed(
    () => 1 - ease(rangeProgress(props.heroProgress, 0.95, 1.0)),
);
</script>

<template>
    <!-- ═══════════════════════════════════════════════
         HERO VIDEO — fixed, z-index 2, clip-path zoomt van kaart naar volledig scherm
    ════════════════════════════════════════════════ -->
    <div :style="videoStyle">
        <HlsVideo
            v-if="bgImage && isVideoUrl(bgImage)"
            :src="bgImage"
            autoplay
            loop
            muted
            playsinline
            class="h-full w-full object-cover"
        />
        <CmsImage
            priority
            v-else-if="bgImage"
            :src="bgImage"
            alt=""
            class="h-full w-full object-cover"
        />
        <div
            v-else
            class="flex h-full w-full items-center justify-center bg-neutral-800"
        >
            <span class="text-sm text-white/20">Hoofdvideo</span>
        </div>
    </div>

    <!-- Hero video zwarte overlay — fadeert in wanneer video full-screen is -->
    <div
        v-if="overlayOpacity > 0"
        :style="{
            position: 'fixed',
            inset: '0',
            zIndex: 2,
            background: 'black',
            opacity: overlayOpacity * 0.55,
            pointerEvents: 'none',
        }"
    />

    <!-- Donkere verloop onderaan de hero — zorgt dat de gele scroll-pijl op zwart leesbaar blijft -->
    <div
        v-if="scrollIndicatorOpacity > 0"
        :style="{
            position: 'fixed',
            left: '0',
            right: '0',
            bottom: '0',
            height: '240px',
            background:
                'linear-gradient(to top, rgba(12,12,12,0.92) 0%, rgba(12,12,12,0.55) 45%, transparent 100%)',
            zIndex: 2,
            pointerEvents: 'none',
            opacity: scrollIndicatorOpacity,
        }"
    />
</template>
