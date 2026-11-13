<script setup lang="ts">
import { computed } from 'vue';
import type { CSSProperties } from 'vue';
import CmsImage from '@/components/CmsImage.vue';
import HlsVideo from '@/components/HlsVideo.vue';
import { isVideoUrl } from '@/lib/scrollAnimation';

const props = defineProps<{
    description: string;
    buttonText: string;
    buttonHref: string;
    photos: string[];
    /** 0 = sectie komt onderin beeld, 1 = sectie verlaat de bovenkant. */
    passProgress: number;
    viewportWidth: number;
    viewportHeight: number;
}>();

type Rect = {
    x: number;
    y: number;
    width: number;
    height: number;
    rotate: number;
    /** Ligt de foto boven ("front") of achter de "Team Lemon"-tekst. */
    front: boolean;
};

type TeamLayout = {
    /** Breedte van het Figma-frame; alle maten schalen mee met de viewportbreedte. */
    frameWidth: number;
    /** Hoogte van de sectie in Figma-eenheden. */
    frameHeight: number;
    title: { centerX: number; top: number; fontSize: number };
    description: { top: number; width: number; fontSize: number };
    button: { top: number; width: number; height: number; fontSize: number };
    photos: Rect[];
};

// Figma desktop: frame "Team Lemon" (node 1:1773), 1640 breed.
const DESKTOP_LAYOUT: TeamLayout = {
    frameWidth: 1640,
    frameHeight: 1092,
    title: { centerX: 789.5, top: 222, fontSize: 339.39 },
    description: { top: 777, width: 390, fontSize: 18 },
    button: { top: 927, width: 155, height: 65, fontSize: 23 },
    photos: [
        { x: 200, y: 180, width: 196, height: 261, rotate: 2.66, front: false },
        { x: 1120, y: 18, width: 214, height: 289, rotate: 1.87, front: true },
        { x: 1513, y: 313, width: 154, height: 205, rotate: 0, front: true },
        { x: -13, y: 593, width: 227, height: 325, rotate: -2.14, front: true },
        {
            x: 1153,
            y: 689,
            width: 265,
            height: 357,
            rotate: -2.67,
            front: true,
        },
    ],
};

// Figma mobiel: frame 342:16673, 540 breed.
const MOBILE_LAYOUT: TeamLayout = {
    frameWidth: 540,
    frameHeight: 980,
    title: { centerX: 271.8, top: 342, fontSize: 131 },
    description: { top: 539, width: 390, fontSize: 18 },
    button: { top: 689, width: 155, height: 65, fontSize: 23 },
    photos: [
        { x: -18, y: 281, width: 128, height: 170, rotate: 2.66, front: false },
        { x: 214, y: -30, width: 178, height: 236, rotate: 1.87, front: false },
        { x: 444, y: 197, width: 121, height: 160, rotate: 0, front: true },
        {
            x: -12,
            y: 754,
            width: 146,
            height: 203,
            rotate: -2.14,
            front: false,
        },
        {
            x: 421,
            y: 690,
            width: 175,
            height: 235,
            rotate: -2.67,
            front: false,
        },
    ],
};

// Figma: regelafstand 196.3px bij 339.4px fontgrootte.
const TITLE_LINE_HEIGHT = 0.5784;

const PLACEHOLDER_CLASSES = [
    'bg-neutral-900',
    'bg-neutral-800',
    'bg-neutral-700',
    'bg-neutral-900',
    'bg-neutral-800',
] as const;

// Subtiele parallax: iedere foto drijft net iets anders omhoog.
const TEAM_PHOTO_DRIFT_VH = [0.05, 0.065, 0.08, 0.095, 0.11];

const layout = computed(() =>
    props.viewportWidth < 768 ? MOBILE_LAYOUT : DESKTOP_LAYOUT,
);

/** Figma-eenheid → px op de huidige viewportbreedte. */
function unit(value: number): string {
    return `${((value / layout.value.frameWidth) * props.viewportWidth).toFixed(2)}px`;
}

const containerStyle = computed<CSSProperties>(() => ({
    height: unit(layout.value.frameHeight),
}));

const titleStyle = computed<CSSProperties>(() => {
    const { title } = layout.value;

    return {
        left: unit(title.centerX),
        top: unit(title.top),
        fontSize: unit(title.fontSize),
        lineHeight: TITLE_LINE_HEIGHT,
        letterSpacing: '-0.04em',
        transform: `translate3d(-50%, ${(-props.passProgress * props.viewportHeight * 0.035).toFixed(1)}px, 0)`,
    };
});

const descriptionStyle = computed<CSSProperties>(() => {
    const { description } = layout.value;

    return {
        top: unit(description.top),
        width: `max(${unit(description.width)}, min(320px, calc(100vw - 48px)))`,
        fontSize: `max(13px, ${unit(description.fontSize)})`,
        lineHeight: 1.33,
        letterSpacing: '-0.01em',
    };
});

const buttonStyle = computed<CSSProperties>(() => {
    const { button } = layout.value;

    return {
        top: unit(button.top),
        width: unit(button.width),
        height: unit(button.height),
        fontSize: unit(button.fontSize),
        letterSpacing: '-0.03em',
    };
});

function photoStyle(index: number): CSSProperties {
    const photo = layout.value.photos[index];
    // Op mobiel liggen foto's dicht op de tekst: minder drift zodat ze er niet overheen schuiven.
    const driftScale = props.viewportWidth < 768 ? 0.3 : 1;
    const drift =
        (TEAM_PHOTO_DRIFT_VH[index] ?? 0.08) *
        props.viewportHeight *
        props.passProgress *
        driftScale;

    return {
        left: unit(photo.x),
        top: unit(photo.y),
        width: unit(photo.width),
        height: unit(photo.height),
        zIndex: photo.front ? 20 : 0,
        transform: `translate3d(0, ${(-drift).toFixed(1)}px, 0) rotate(${photo.rotate}deg)`,
        willChange: 'transform',
        backfaceVisibility: 'hidden',
    };
}
</script>

<template>
    <div class="relative w-full overflow-clip" :style="containerStyle">
        <!-- "Team Lemon" — grote achtergrondtekst (Figma node 1:1777) -->
        <p
            class="pointer-events-none absolute z-10 text-center font-avenir whitespace-nowrap text-black select-none"
            style="font-weight: 900; font-style: oblique"
            :style="titleStyle"
            aria-hidden="true"
        >
            Team<br />Lemon
        </p>
        <h2 class="sr-only">Team Lemon</h2>

        <!-- Zwevende foto's (5 stuks) -->
        <div
            v-for="(_, i) in layout.photos"
            :key="i"
            class="absolute overflow-hidden rounded-[14px]"
            :style="photoStyle(i)"
        >
            <template v-if="photos[i]">
                <HlsVideo
                    v-if="isVideoUrl(photos[i])"
                    :src="photos[i]"
                    class="h-full w-full object-cover"
                    autoplay
                    loop
                    muted
                    playsinline
                />
                <CmsImage
                    sizes="(min-width: 768px) 30vw, 60vw"
                    v-else
                    :src="photos[i]"
                    :alt="`Team Lemon — foto ${i + 1}`"
                    class="h-full w-full object-cover"
                />
            </template>
            <div v-else class="h-full w-full" :class="PLACEHOLDER_CLASSES[i]" />
        </div>

        <!-- Beschrijving + knop -->
        <p
            class="absolute left-1/2 z-10 -translate-x-1/2 text-center font-avenir text-black"
            :style="descriptionStyle"
        >
            {{ description }}
        </p>
        <a
            :href="buttonHref || '#about'"
            class="absolute left-1/2 z-10 flex min-h-[44px] min-w-[112px] -translate-x-1/2 items-center justify-center rounded-[34px] border border-black font-avenir text-black transition-colors hover:bg-black hover:text-[#ffc700]"
            :style="buttonStyle"
        >
            {{ buttonText || 'About us' }}
        </a>
    </div>
</template>
