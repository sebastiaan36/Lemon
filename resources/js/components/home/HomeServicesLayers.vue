<script setup lang="ts">
import { computed } from 'vue';
import type { CSSProperties } from 'vue';
import CmsImage from '@/components/CmsImage.vue';
import HlsVideo from '@/components/HlsVideo.vue';
import {
    FIGMA_DESKTOP_HEIGHT,
    ease,
    lerp,
    rangeProgress,
} from '@/lib/scrollAnimation';
import type { HomeService } from '@/types';

const props = defineProps<{
    services: HomeService[];
    /** Services-voortgang mét scroll-locks (zie withScrollLocks in Welcome.vue). */
    visualProgress: number;
    leadProgress: number;
    casesStarted: boolean;
    /** Onderkant van de intro-tekst t.o.v. de viewport, of null als nog niet gemeten. */
    aboutTextBottom: number | null;
    viewportWidth: number;
    viewportHeight: number;
}>();

// Video: position:fixed (zoals hero), clip-path clipt van gecentreerde kaart → fullscreen
// Fase 0.00–0.12: expansie, 0.12–0.45: Brand, 0.45–0.78: Experience, 0.78–1.00: Employee
const SERVICES_EMPLOYEE_EXIT_START = 0.985;
const SERVICES_EMPLOYEE_EXIT_END = 1;

const exitT = computed(() =>
    ease(
        rangeProgress(
            props.visualProgress,
            SERVICES_EMPLOYEE_EXIT_START,
            SERVICES_EMPLOYEE_EXIT_END,
        ),
    ),
);

const videoStyle = computed<CSSProperties>(() => {
    const sp = props.visualProgress;
    const lp = props.leadProgress;

    if (lp <= 0 && sp <= 0) {
        return { display: 'none' };
    }

    if (sp >= 1 && props.casesStarted) {
        return { display: 'none' };
    }

    const leadInT = ease(rangeProgress(lp, 0, 0.72));
    const leadOpacity = ease(rangeProgress(lp, 0.05, 0.28));
    const fullscreenT = ease(rangeProgress(sp, 0.03, 0.065));
    const textBottomViewport = props.aboutTextBottom ?? 194 + 297;
    const targetTop = textBottomViewport + 110;
    const compact = props.viewportWidth < 768;
    const startWidth = compact
        ? props.viewportWidth - 48
        : props.viewportWidth * 0.8;
    const startLeft = (props.viewportWidth - startWidth) / 2;
    // Mobiel (Figma): liggende kaart onder de introtekst i.p.v. een staande.
    const startHeight = compact
        ? Math.min(startWidth * 0.75, props.viewportHeight * 0.45)
        : Math.min(
              (860.054 / FIGMA_DESKTOP_HEIGHT) * props.viewportHeight,
              props.viewportHeight * 0.58,
          );
    const startTop = props.viewportHeight + 32;
    const settledTop = Math.max(
        targetTop,
        props.viewportHeight - startHeight - 24,
    );
    const enterTop = lerp(startTop, settledTop, leadInT);

    const top = lerp(enterTop, 0, fullscreenT);
    const left = lerp(startLeft, 0, fullscreenT);
    const width = lerp(startWidth, props.viewportWidth, fullscreenT);
    const height = lerp(startHeight, props.viewportHeight, fullscreenT);
    const radius = lerp(20, 0, fullscreenT);
    const exitY = exitT.value * props.viewportHeight;

    return {
        position: 'fixed',
        top: `${(top - exitY).toFixed(1)}px`,
        left: `${left.toFixed(1)}px`,
        width: `${width.toFixed(1)}px`,
        height: `${height.toFixed(1)}px`,
        zIndex: 4,
        borderRadius: `${radius.toFixed(1)}px`,
        overflow: 'hidden',
        pointerEvents: 'none',
        transform: 'translateY(0)',
        transformOrigin: 'center top',
        transition:
            'top 0.15s linear, left 0.15s linear, width 0.15s linear, height 0.15s linear, opacity 0.2s linear, border-radius 0.15s linear',
        opacity: Math.min(
            Math.max(leadOpacity, ease(rangeProgress(sp, 0.02, 0.12))),
            1 - ease(rangeProgress(sp, SERVICES_EMPLOYEE_EXIT_START, 1)),
        ),
    };
});

// Gele achtergrond: apart fixed element op z-index 3 (onder video at 4, boven hero-video at 2)
// Start zodra de services-sectie de viewport inkomt (van onderaf), zodat de overgang
// van de intro-sectie (ook geel) naadloos aansluit — geen donkere flash tussendoor.
const bgStyle = computed<CSSProperties>(() => {
    const sp = props.visualProgress;
    const lp = props.leadProgress;

    if (sp >= 1.0) {
        return { display: 'none' };
    }

    if (lp <= 0 && sp <= 0) {
        return { display: 'none' };
    }

    return {
        position: 'fixed',
        top: `${(-exitT.value * props.viewportHeight).toFixed(1)}px`,
        left: '0',
        width: '100vw',
        height: '100vh',
        background: '#ffc700',
        zIndex: 3,
        pointerEvents: 'none',
        opacity: ease(rangeProgress(Math.max(lp, sp), 0, 0.18)),
    };
});

// Text-overlay: fixed, z-index 6 — boven video (4) en gele bg (3)
// Sticky werkt niet betrouwbaar binnen stacking contexts; fixed garandeert correcte positie.
const textStyle = computed<CSSProperties>(() => {
    const sp = props.visualProgress;

    if (sp <= 0) {
        return { display: 'none' };
    }

    return {
        position: 'fixed',
        top: `${(-exitT.value * props.viewportHeight).toFixed(1)}px`,
        left: '0',
        width: '100vw',
        height: '100vh',
        zIndex: 6,
        pointerEvents: 'none',
        background:
            'linear-gradient(180deg, rgba(0, 0, 0, 0.00) 35.16%, rgba(0, 0, 0, 0.20) 78.29%)',
        opacity: Math.min(
            ease(rangeProgress(sp, 0.1, 0.22)), // fade in zodra video fullscreen is
            1 - ease(rangeProgress(sp, SERVICES_EMPLOYEE_EXIT_START, 1.0)), // fade out bij einde services
        ),
    };
});

const scrollIndicatorOpacity = computed(() =>
    Math.min(
        ease(rangeProgress(props.visualProgress, 0.14, 0.25)),
        1 - ease(rangeProgress(props.visualProgress, 0.72, 0.82)),
    ),
);

// Onder 768px stapelen namen en beschrijving (Figma mobiel); daarboven schalen de namen mee
// zodat ze niet onder de beschrijving rechtsonder doorlopen.
const isCompact = computed(() => props.viewportWidth < 768);
const serviceNameFontSize = computed(() =>
    isCompact.value
        ? Math.min(Math.max(props.viewportWidth * 0.1, 38), 56)
        : Math.min(Math.max(props.viewportWidth * 0.068, 56), 86),
);

const servicesLabelStyle = computed<CSSProperties>(() =>
    isCompact.value
        ? {
              left: '24px',
              top: 'calc(34vh - 28px)',
              fontSize: '16px',
              lineHeight: '28px',
              letterSpacing: '-0.16px',
          }
        : {
              left: '59px',
              top: 'calc(59.44vh - 30px)',
              fontSize: '26px',
              lineHeight: '60px',
              letterSpacing: '-0.78px',
          },
);

function serviceNameStyle(index: number): CSSProperties {
    const size = serviceNameFontSize.value;
    const lineHeight = isCompact.value ? size * 1.05 : size * 1.643;
    const top = isCompact.value
        ? `calc(34vh + ${(index * lineHeight).toFixed(1)}px)`
        : `${59.44 + index * 8.9}vh`;

    return {
        left: isCompact.value ? '20px' : '54px',
        top,
        fontWeight: '900',
        fontStyle: 'oblique',
        fontSize: `${size.toFixed(1)}px`,
        lineHeight: `${lineHeight.toFixed(1)}px`,
        letterSpacing: '-0.03em',
        opacity: activeServiceIndex.value === index ? 1 : 0.25,
    };
}

function serviceDetailStyle(index: number): CSSProperties {
    const active = activeServiceIndex.value === index;

    return {
        ...(isCompact.value
            ? { left: '24px', right: '24px', bottom: '104px' }
            : {
                  right: '59px',
                  bottom: '88px',
                  maxWidth: 'min(559px, 42vw)',
              }),
        opacity: active ? 1 : 0,
        transform: `translateY(${active ? 0 : 20}px)`,
        transition: 'opacity 0.7s ease, transform 0.7s ease',
    };
}

const activeServiceIndex = computed(() => {
    const t = props.visualProgress;

    if (t < 0.45) {
        return 0;
    }

    if (t < 0.65) {
        return 1;
    }

    return 2;
});

function scrollToCases(): void {
    document.getElementById('cases')?.scrollIntoView({ behavior: 'smooth' });
}
</script>

<template>
    <!-- ═══════════════════════════════════════════════
         SERVICES VIDEO — fixed, z-index 4, clip-path van kaart → fullscreen
    ════════════════════════════════════════════════ -->
    <div :style="videoStyle">
        <template
            v-for="(service, i) in services"
            :key="service.name + '-fixed'"
        >
            <div
                class="absolute inset-0"
                :style="{
                    opacity: activeServiceIndex === i ? 1 : 0,
                    transition: 'opacity 0.8s ease',
                }"
            >
                <HlsVideo
                    v-if="service.mediaType === 'video' && service.mediaUrl"
                    :src="service.mediaUrl"
                    autoplay
                    loop
                    muted
                    playsinline
                    class="h-full w-full object-cover"
                />
                <CmsImage
                    v-else-if="
                        service.mediaType === 'image' && service.mediaUrl
                    "
                    sizes="(min-width: 1024px) 50vw, 100vw"
                    :src="service.mediaUrl"
                    :alt="service.name"
                    class="h-full w-full object-cover"
                />
                <div
                    v-else
                    class="flex h-full w-full items-center justify-center"
                    :class="[
                        i === 0
                            ? 'bg-neutral-800'
                            : i === 1
                              ? 'bg-neutral-700'
                              : 'bg-neutral-600',
                    ]"
                >
                    <span class="text-sm text-white/20"
                        >{{ service.name }} media</span
                    >
                </div>
            </div>
        </template>
    </div>

    <!-- ═══════════════════════════════════════════════
         SERVICES GELE ACHTERGROND — fixed, z-index 3
         Zichtbaar in de clip-path gebieden rondom de video-kaart.
         Lager dan de video (4) maar hoger dan de hero-video (2).
    ════════════════════════════════════════════════ -->
    <div :style="bgStyle" />

    <!-- ═══════════════════════════════════════════════
         SERVICES TEXT OVERLAY — fixed, z-index 6, boven video (4) en gele bg (3)
         Altijd zichtbaar op de juiste positie ongeacht scroll — geen sticky nodig.
    ════════════════════════════════════════════════ -->
    <div :style="textStyle">
        <!-- "Services" label — geel, uitgelijnd met servicenamen -->
        <p
            class="absolute font-avenir font-normal text-[#ffc700]"
            :style="servicesLabelStyle"
        >
            Services
        </p>

        <!-- Servicenamen — absoluut gestapeld (Figma: 59.44vh + i×8.9vh) -->
        <p
            v-for="(service, i) in services"
            :key="service.name + '-name'"
            class="absolute font-avenir text-white transition-opacity duration-500"
            :style="serviceNameStyle(i)"
        >
            {{ service.name }}
        </p>

        <!-- Beschrijving + tags — rechtsonder, wisselend per actieve service -->
        <template
            v-for="(service, i) in services"
            :key="service.name + '-right'"
        >
            <div class="absolute" :style="serviceDetailStyle(i)">
                <p
                    class="mb-4 font-avenir text-[16px] leading-[24px] font-normal tracking-[-0.16px] text-white md:mb-5 md:text-[22px] md:leading-[32px] md:tracking-[-0.22px]"
                >
                    {{ service.description }}
                </p>
                <div class="flex flex-wrap gap-2">
                    <span
                        v-for="tag in service.tags"
                        :key="tag"
                        class="rounded-[40px] border border-white px-3 py-1 font-avenir text-[12px] leading-[20px] font-normal tracking-[-0.12px] text-white md:px-4 md:py-1.5 md:text-[14px] md:leading-[24px] md:tracking-[-0.14px]"
                    >
                        {{ tag }}
                    </span>
                </div>
            </div>
        </template>

        <!-- Scroll indicator — zelfde vormgeving als hero -->
        <button
            class="absolute left-1/2 -translate-x-1/2 cursor-pointer"
            style="
                bottom: 8px;
                background: none;
                border: none;
                padding: 0;
                z-index: 3;
            "
            :style="{ opacity: scrollIndicatorOpacity }"
            aria-label="Scroll to cases"
            @click="scrollToCases"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                width="66"
                height="80"
                viewBox="0 0 105 127"
                fill="none"
                aria-hidden="true"
            >
                <g filter="url(#filter_services_scroll)">
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
                <circle
                    opacity="0.2"
                    cx="52.2769"
                    cy="62.5"
                    r="32"
                    stroke="white"
                />
                <circle
                    cx="52.2769"
                    cy="62.5"
                    r="32"
                    stroke="#FFC700"
                    stroke-width="1"
                    fill="none"
                    :stroke-dasharray="201.062"
                    :stroke-dashoffset="201.062 * (1 - visualProgress)"
                    transform="rotate(-90 52.2769 62.5)"
                />
                <defs>
                    <filter
                        id="filter_services_scroll"
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
                            result="effect1_dropShadow_services_scroll"
                        />
                        <feBlend
                            mode="normal"
                            in="SourceGraphic"
                            in2="effect1_dropShadow_services_scroll"
                            result="shape"
                        />
                    </filter>
                </defs>
            </svg>
        </button>
    </div>
</template>
