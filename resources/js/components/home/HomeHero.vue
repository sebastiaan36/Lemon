<script setup lang="ts">
import { computed } from 'vue';
import type { CSSProperties } from 'vue';
import HlsVideo from '@/components/HlsVideo.vue';
import {
    FIGMA_DESKTOP_HEIGHT,
    ease,
    isVideoUrl,
    lerp,
    rangeProgress,
    scaleX,
    scaleY,
} from '@/lib/scrollAnimation';

const props = defineProps<{
    title: string;
    bodyText: string;
    floatingImages: (string | null)[];
    heroProgress: number;
    viewportWidth: number;
    viewportHeight: number;
}>();

const HERO_TEXT_LOCK_HEIGHT = 249.5;
const HERO_TEXT_LOCK_WIDTH = 1169.4;
const HERO_TEXT_LOCK_FONT_SIZE = 48.03;
const HERO_TEXT_LOCK_LINE_HEIGHT = 60;
const HERO_TEXT_LOCK_LETTER_SPACING = -1.44;
const HERO_TEXT_START_TOP = 744;
const HERO_TEXT_SCALE_START = 0.065;
const HERO_TEXT_SCALE_END = 0.095;

// Basismaten van de 4 zijkaarten (nodes 1:7–1:14) en hun scatter-richting.
// Indices 0–3: top-right, top-left, bottom-right, bottom-left.
const FLOATING_CARDS = [
    {
        width: 476,
        height: 278.888,
        left: 944.955,
        top: -168.811,
        x: 195,
        y: -231,
        rotate: 1.98,
        placeholderClass: 'bg-neutral-800',
    },
    {
        width: 477,
        height: 279.474,
        left: 110.446,
        top: -196.814,
        x: -335,
        y: -330,
        rotate: 2.16,
        placeholderClass: 'bg-neutral-800',
    },
    {
        width: 407.18,
        height: 238.566,
        left: 1430.007,
        top: 542.01,
        x: 386,
        y: 405,
        rotate: 1.41,
        placeholderClass: 'bg-neutral-700',
    },
    {
        width: 463.915,
        height: 271.807,
        left: 0,
        top: 780,
        x: -270,
        y: 275,
        rotate: 0,
        placeholderClass: 'bg-neutral-800',
    },
] as const;

function sx(value: number): string {
    return scaleX(value, props.viewportWidth);
}

function sy(value: number): string {
    return scaleY(value, props.viewportHeight);
}

const scatterT = computed(() =>
    ease(rangeProgress(props.heroProgress, 0, 0.1)),
);

// Zijkaarten verspreiden zich buiten beeld zodra de hoofdkaart expandeert.
function floatingCardStyle(index: number): CSSProperties {
    const card = FLOATING_CARDS[index] ?? FLOATING_CARDS[0];
    const t = scatterT.value;

    return {
        width: sx(card.width),
        height: sy(card.height),
        left: sx(card.left),
        top: sy(card.top),
        overflow: 'hidden',
        filter: 'blur(17px)',
        opacity: 1 - t,
        transform: `translate(${(t * card.x).toFixed(1)}px, ${(t * card.y).toFixed(1)}px) rotate(${card.rotate}deg)`,
    };
}

// Title: 120px → 82.493px as card expands (node 1:4 → 1:104), then scrolls off (node 1:104 → 1:155)
const titleStyle = computed<CSSProperties>(() => {
    const p = props.heroProgress;
    const phase1 = ease(rangeProgress(p, 0, 0.1)); // title shrinks as video expands
    const phase2 = ease(rangeProgress(p, 0.1, 0.2)); // title snel uit beeld

    return {
        fontSize: `${(120 - 37.507 * phase1).toFixed(2)}px`,
        lineHeight: `${(70 - 21.879 * phase1).toFixed(2)}px`,
        letterSpacing: `${(-4.8 + 1.5003 * phase1).toFixed(4)}px`,
        transform: `translateY(${(28 * phase1 - 553 * phase2).toFixed(1)}px)`,
        opacity: 1 - ease(rangeProgress(p, 0.13, 0.2)),
    };
});

// Hero body text: staat vast op zijn startpositie; alleen schaal verandert tijdens de video-expansie.
const bodyTextScaleT = computed(() =>
    ease(
        rangeProgress(
            props.heroProgress,
            HERO_TEXT_SCALE_START,
            HERO_TEXT_SCALE_END,
        ),
    ),
);
const isBodyTextLocked = computed(
    () => props.heroProgress >= HERO_TEXT_SCALE_END,
);

const bodyTextStyle = computed<CSSProperties>(() => {
    const t = bodyTextScaleT.value;
    const locked = isBodyTextLocked.value;

    return {
        fontWeight: '400',
        fontSize: locked
            ? `${HERO_TEXT_LOCK_FONT_SIZE}px`
            : `${lerp(29.407, HERO_TEXT_LOCK_FONT_SIZE, t).toFixed(2)}px`,
        lineHeight: locked
            ? `${HERO_TEXT_LOCK_LINE_HEIGHT}px`
            : `${lerp(36.759, HERO_TEXT_LOCK_LINE_HEIGHT, t).toFixed(2)}px`,
        letterSpacing: locked
            ? `${HERO_TEXT_LOCK_LETTER_SPACING}px`
            : `${lerp(-0.8822, HERO_TEXT_LOCK_LETTER_SPACING, t).toFixed(4)}px`,
        color: 'white',
        transform: 'translateY(-0px)',
    };
});

const bodyTextWidth = computed(() => {
    if (isBodyTextLocked.value) {
        return `min(${sx(HERO_TEXT_LOCK_WIDTH)}, 90vw)`;
    }

    const phaseAWidth = lerp(
        701.36,
        HERO_TEXT_LOCK_WIDTH,
        bodyTextScaleT.value,
    );

    return `min(${sx(phaseAWidth)}, 90vw)`;
});

const bodyTextTop = computed(() => {
    const baseTop =
        (HERO_TEXT_START_TOP / FIGMA_DESKTOP_HEIGHT) * props.viewportHeight;
    const t = rangeProgress(props.heroProgress, 0.085, 0.99);
    const endTop = -(HERO_TEXT_LOCK_HEIGHT + 40);

    return `${lerp(baseTop, endTop, t).toFixed(1)}px`;
});

const bodyTextOpacity = computed(() => {
    if (props.heroProgress <= 0.995) {
        return 1;
    }

    return 1 - ease(rangeProgress(props.heroProgress, 0.995, 1));
});

// Full-viewport container met gradient mask (viewport-relatief: 30% boven/onder)
const bodyTextContainerStyle = computed<CSSProperties>(() => {
    const gradient =
        'linear-gradient(180deg, transparent 0%, black 30%, black 55%, transparent 95%)';

    return {
        position: 'fixed',
        top: 0,
        left: 0,
        width: '100%',
        height: '100%',
        pointerEvents: 'none',
        opacity: bodyTextOpacity.value,
        zIndex: 3,
        maskImage: gradient,
        WebkitMaskImage: gradient,
    };
});

// Inner wrapper: positioneert de tekst op de juiste viewport-positie
const bodyTextInnerStyle = computed<CSSProperties>(() => ({
    position: 'absolute',
    left: '50%',
    top: bodyTextTop.value,
    width: bodyTextWidth.value,
    overflow: 'visible',
    transform: 'translate(-50%, 0)',
}));

const scrollIndicatorOpacity = computed(
    () => 1 - ease(rangeProgress(props.heroProgress, 0.95, 1.0)),
);

function goToServices(): void {
    document.getElementById('services')?.scrollIntoView({ behavior: 'smooth' });
}
</script>

<template>
    <!-- Hero sticky overlay: transparante achtergrond (video is een apart fixed element), z-index boven video -->
    <div class="sticky top-0 h-screen" :style="{ zIndex: 3 }">
        <!-- Kaarten 1–4: scatter bij scroll -->
        <div
            v-for="(card, index) in FLOATING_CARDS"
            :key="index"
            class="absolute rounded-[20px]"
            :style="floatingCardStyle(index)"
        >
            <HlsVideo
                v-if="
                    floatingImages[index] && isVideoUrl(floatingImages[index])
                "
                :src="floatingImages[index]"
                autoplay
                loop
                muted
                playsinline
                class="h-full w-full object-cover"
            />
            <img
                v-else-if="floatingImages[index]"
                :src="floatingImages[index]!"
                class="h-full w-full object-cover"
                alt=""
            />
            <div v-else class="h-full w-full" :class="card.placeholderClass" />
        </div>

        <!-- Titel: 120px→82px terwijl kaart expandeert, scrollt daarna weg (node 1:4→1:104→1:155) -->
        <div
            class="pointer-events-none absolute right-0 left-0"
            :style="{ top: sy(387) }"
        >
            <h1
                class="text-center font-avenir"
                style="font-weight: 900; font-style: oblique; color: #ffc700"
                :style="titleStyle"
            >
                {{ title }}
            </h1>
        </div>

        <!-- Bodytekst: full-viewport container met gradient mask (viewport-relatief 30% boven/onder) -->
        <div :style="bodyTextContainerStyle">
            <div :style="bodyTextInnerStyle">
                <p class="text-center font-avenir" :style="bodyTextStyle">
                    {{ bodyText }}
                </p>
            </div>
        </div>

        <!-- Scroll indicator: ring vult zich naarmate heroProgress vordert; klik → services -->
        <button
            class="cursor-pointer"
            :style="{
                position: 'fixed',
                left: sx(819.667),
                bottom: '8px',
                transform: 'translateX(-50%)',
                opacity: scrollIndicatorOpacity,
                background: 'none',
                border: 'none',
                padding: 0,
                zIndex: 3,
            }"
            aria-label="Scroll naar services"
            @click="goToServices"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                width="66"
                height="80"
                viewBox="0 0 105 127"
                fill="none"
                aria-hidden="true"
            >
                <g filter="url(#filter_hero_scroll)">
                    <rect
                        x="20.1202"
                        y="30.3433"
                        width="64.3133"
                        height="64.3133"
                        rx="32.1567"
                        stroke="white"
                        stroke-opacity="0.3"
                        stroke-width="0.686695"
                        shape-rendering="crispEdges"
                    />
                </g>
                <path
                    d="M45.4885 66.0251L52.7772 73.3138L60.3922 65.6988"
                    stroke="#FFC700"
                    stroke-width="1.53846"
                />
                <path
                    d="M52.777 72.8786V50.6863"
                    stroke="#FFC700"
                    stroke-width="1.53846"
                />
                <!-- Dim witte buitenring -->
                <circle
                    opacity="0.2"
                    cx="52.2769"
                    cy="62.5"
                    r="32"
                    stroke="white"
                />
                <!-- Gele voortgangsring: vult van 0% → 100% naarmate heroProgress toeneemt -->
                <circle
                    cx="52.2769"
                    cy="62.5"
                    r="32"
                    stroke="#FFC700"
                    stroke-width="1"
                    fill="none"
                    :stroke-dasharray="201.062"
                    :stroke-dashoffset="201.062 * (1 - heroProgress)"
                    transform="rotate(-90 52.2769 62.5)"
                />
                <defs>
                    <filter
                        id="filter_hero_scroll"
                        x="3.05176e-05"
                        y="17.6395"
                        width="104.554"
                        height="108.674"
                        filterUnits="userSpaceOnUse"
                        color-interpolation-filters="sRGB"
                    >
                        <feFlood
                            flood-opacity="0"
                            result="BackgroundImageFix"
                        />
                        <feColorMatrix
                            in="SourceAlpha"
                            type="matrix"
                            values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0"
                            result="hardAlpha"
                        />
                        <feOffset dy="11.5365" />
                        <feGaussianBlur stdDeviation="9.88841" />
                        <feComposite in2="hardAlpha" operator="out" />
                        <feColorMatrix
                            type="matrix"
                            values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.05 0"
                        />
                        <feBlend
                            mode="normal"
                            in2="BackgroundImageFix"
                            result="effect1_dropShadow_scroll"
                        />
                        <feBlend
                            mode="normal"
                            in="SourceGraphic"
                            in2="effect1_dropShadow_scroll"
                            result="shape"
                        />
                    </filter>
                </defs>
            </svg>
        </button>
    </div>
</template>
