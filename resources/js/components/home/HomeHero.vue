<script setup lang="ts">
import { useResizeObserver } from '@vueuse/core';
import { computed, onMounted, ref } from 'vue';
import type { CSSProperties } from 'vue';
import CmsImage from '@/components/CmsImage.vue';
import HlsVideo from '@/components/HlsVideo.vue';
import {
    FIGMA_DESKTOP_HEIGHT,
    FIGMA_MOBILE_HEIGHT,
    MOBILE_LAYOUT_MAX_WIDTH,
    ease,
    isVideoUrl,
    lerp,
    mobileFrame,
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

/** Maten van de scrollknop-SVG (viewBox 105×127, ring 64,3px, middelpunt y 62,5). */
const SCROLL_SVG = { width: 105, height: 127, ring: 64.31, centerY: 62.5 };

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

// Mobiel ontwerp (540×900): dezelfde volgorde top-right, top-left,
// bottom-right, bottom-left (nodes 1:9, 1:5, 1:14, 1:7), zonder rotatie.
const MOBILE_FLOATING_CARDS = [
    { width: 254.668, height: 149.21, left: 315, top: -25 },
    { width: 201.626, height: 311.376, left: -22, top: -158 },
    { width: 318.82, height: 186.796, left: 457, top: 631 },
    { width: 219.915, height: 339.621, left: -38, top: 794 },
] as const;

// Mobiele typografie en posities (nodes 1:12 titel, 1:37 bodytekst, 1:38 scrollknop).
const MOBILE_TITLE = { top: 327, width: 360, fontSize: 80 };
const MOBILE_BODY = {
    top: 647,
    width: 279,
    fontSize: 22,
    lineHeight: 28,
    letterSpacing: -0.66,
    boxHeight: 199,
};
// Midden van de scrollring ligt 86,5px boven de onderkant van het frame;
// de ring is 65px breed (de SVG heeft schaduw-padding rondom).
const MOBILE_SCROLL = { centerFromBottom: 86.5, ringSize: 65 };

function sx(value: number): string {
    return scaleX(value, props.viewportWidth);
}

function sy(value: number): string {
    return scaleY(value, props.viewportHeight);
}

// Typografie schaalt mee met de viewportbreedte: 1 op desktop (≥1100px),
// richting ~0,4 op mobiel (Figma mobiel: titel ~48px, bodytekst ~23px).
const typeScale = computed(() =>
    Math.min(1, Math.max(props.viewportWidth / 1100, 0.4)),
);
const isCompact = computed(() => props.viewportWidth < MOBILE_LAYOUT_MAX_WIDTH);
const frame = computed(() =>
    mobileFrame(props.viewportWidth, props.viewportHeight),
);

const bodyTextRef = ref<HTMLElement | null>(null);
const bodyTextHeight = ref(HERO_TEXT_LOCK_HEIGHT);

function measureBodyText(): void {
    bodyTextHeight.value =
        bodyTextRef.value?.offsetHeight ?? HERO_TEXT_LOCK_HEIGHT;
}

useResizeObserver(bodyTextRef, measureBodyText);
onMounted(measureBodyText);

const scatterT = computed(() =>
    ease(rangeProgress(props.heroProgress, 0, 0.1)),
);

// Zijkaarten verspreiden zich buiten beeld zodra de hoofdkaart expandeert.
function floatingCardStyle(index: number): CSSProperties {
    const card = FLOATING_CARDS[index] ?? FLOATING_CARDS[0];
    const t = scatterT.value;

    if (isCompact.value) {
        const mobile = MOBILE_FLOATING_CARDS[index] ?? MOBILE_FLOATING_CARDS[0];
        const { scale, x, y } = frame.value;

        return {
            width: `${(mobile.width * scale).toFixed(1)}px`,
            height: `${(mobile.height * scale).toFixed(1)}px`,
            left: `${x(mobile.left).toFixed(1)}px`,
            top: `${y(mobile.top).toFixed(1)}px`,
            borderRadius: `${(15 * scale).toFixed(1)}px`,
            overflow: 'hidden',
            filter: 'blur(12px)',
            opacity: 1 - t,
            transform: `translate(${(t * card.x * 0.6).toFixed(1)}px, ${(t * card.y * 0.6).toFixed(1)}px)`,
        };
    }

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

    if (isCompact.value) {
        const mobileSize =
            MOBILE_TITLE.fontSize * frame.value.scale * (1 - 0.3126 * phase1);

        return {
            fontSize: `${mobileSize.toFixed(2)}px`,
            lineHeight: `${mobileSize.toFixed(2)}px`,
            letterSpacing: `${(-0.04 * mobileSize).toFixed(3)}px`,
            maxWidth: `${(MOBILE_TITLE.width * frame.value.scale).toFixed(1)}px`,
            margin: '0 auto',
            transform: `translateY(${(28 * phase1 - 553 * phase2).toFixed(1)}px)`,
            opacity: 1 - ease(rangeProgress(p, 0.13, 0.2)),
        };
    }

    const scale = typeScale.value;
    const fontSize = (120 - 37.507 * phase1) * scale;
    // Op mobiel loopt de titel over twee regels; dan is een normale regelafstand nodig.
    const lineHeight = isCompact.value
        ? fontSize * 0.95
        : (70 - 21.879 * phase1) * scale;

    return {
        fontSize: `${fontSize.toFixed(2)}px`,
        lineHeight: `${lineHeight.toFixed(2)}px`,
        letterSpacing: `${((-4.8 + 1.5003 * phase1) * scale).toFixed(4)}px`,
        padding: '0 16px',
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

const titleTop = computed(() =>
    isCompact.value
        ? `${frame.value.y(MOBILE_TITLE.top).toFixed(1)}px`
        : sy(387),
);

const bodyTextStyle = computed<CSSProperties>(() => {
    const t = isBodyTextLocked.value ? 1 : bodyTextScaleT.value;

    if (isCompact.value) {
        const scale = frame.value.scale;
        const grow = lerp(1, 26 / MOBILE_BODY.fontSize, t);

        return {
            fontWeight: '400',
            fontSize: `${(MOBILE_BODY.fontSize * scale * grow).toFixed(2)}px`,
            lineHeight: `${(MOBILE_BODY.lineHeight * scale * grow).toFixed(2)}px`,
            letterSpacing: `${(MOBILE_BODY.letterSpacing * scale * grow).toFixed(3)}px`,
            color: 'white',
        };
    }

    const scale = typeScale.value;

    return {
        fontWeight: '400',
        fontSize: `${(lerp(29.407, HERO_TEXT_LOCK_FONT_SIZE, t) * scale).toFixed(2)}px`,
        lineHeight: `${(lerp(36.759, HERO_TEXT_LOCK_LINE_HEIGHT, t) * scale).toFixed(2)}px`,
        letterSpacing: `${(lerp(-0.8822, HERO_TEXT_LOCK_LETTER_SPACING, t) * scale).toFixed(4)}px`,
        color: 'white',
        transform: 'translateY(-0px)',
    };
});

const bodyTextWidth = computed(() => {
    if (isCompact.value) {
        const t = isBodyTextLocked.value ? 1 : bodyTextScaleT.value;
        const start = MOBILE_BODY.width * frame.value.scale;

        return `${lerp(start, props.viewportWidth * 0.9, t).toFixed(1)}px`;
    }

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
    const baseTop = isCompact.value
        ? frame.value.y(MOBILE_BODY.top)
        : (HERO_TEXT_START_TOP / FIGMA_DESKTOP_HEIGHT) * props.viewportHeight;
    const t = rangeProgress(props.heroProgress, 0.085, 0.99);
    // Gemeten hoogte: op smalle schermen loopt de tekst over veel meer regels.
    const endTop = -(bodyTextHeight.value + 40);

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
    const gradient = isCompact.value
        ? mobileBodyTextMask()
        : 'linear-gradient(180deg, transparent 0%, black 30%, black 55%, transparent 95%)';

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

// Mobiel (node 1:37): de tekst vervaagt binnen zijn eigen kader van wit (11,9%)
// naar transparant (65,9%). Omgerekend naar viewportposities, zodat de
// tekst bij het omhoog scrollen netjes binnen het beeld verschijnt.
function mobileBodyTextMask(): string {
    const { scale, y } = frame.value;
    const textTop = y(MOBILE_BODY.top);
    const solidUntil = textTop + MOBILE_BODY.boxHeight * 0.1188 * scale;
    const goneAt = textTop + MOBILE_BODY.boxHeight * 0.65865 * scale;

    return `linear-gradient(180deg, transparent 0%, black 30%, black ${solidUntil.toFixed(0)}px, transparent ${goneAt.toFixed(0)}px)`;
}

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

const scrollButtonStyle = computed<CSSProperties>(() => {
    const base: CSSProperties = {
        position: 'fixed',
        transform: 'translateX(-50%)',
        opacity: scrollIndicatorOpacity.value,
        background: 'none',
        border: 'none',
        padding: 0,
        zIndex: 3,
    };

    if (!isCompact.value) {
        return { ...base, left: sx(819.667), bottom: '8px' };
    }

    const { scale, y } = frame.value;
    const ringScale = (MOBILE_SCROLL.ringSize * scale) / SCROLL_SVG.ring;
    const ringCenter = y(FIGMA_MOBILE_HEIGHT - MOBILE_SCROLL.centerFromBottom);

    return {
        ...base,
        left: '50%',
        top: `${(ringCenter - SCROLL_SVG.centerY * ringScale).toFixed(1)}px`,
    };
});

const scrollSvgSize = computed(() => {
    if (!isCompact.value) {
        return { width: 66, height: 80 };
    }

    const ringScale =
        (MOBILE_SCROLL.ringSize * frame.value.scale) / SCROLL_SVG.ring;

    return {
        width: Math.round(SCROLL_SVG.width * ringScale),
        height: Math.round(SCROLL_SVG.height * ringScale),
    };
});

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
            <CmsImage
                sizes="(min-width: 768px) 30vw, 50vw"
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
            :style="{ top: titleTop }"
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
                <p
                    ref="bodyTextRef"
                    class="text-center font-avenir"
                    :style="bodyTextStyle"
                >
                    {{ bodyText }}
                </p>
            </div>
        </div>

        <!-- Scroll indicator: ring vult zich naarmate heroProgress vordert; klik → services -->
        <button
            class="cursor-pointer"
            :style="scrollButtonStyle"
            aria-label="Scroll naar services"
            @click="goToServices"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                :width="scrollSvgSize.width"
                :height="scrollSvgSize.height"
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
