<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';
import HlsVideo from '@/components/HlsVideo.vue';
import { useAnimationLoop } from '@/composables/useAnimationLoop';
import { wrap } from '@/lib/scrollAnimation';
import type { HomeCase } from '@/types';

const props = defineProps<{
    cases: HomeCase[];
}>();

const CASES_ARROW_URL =
    '/figma-assets/d80e54ef-48d6-43a4-8629-0801cfe41603.svg';

const CARD_SIZES = [
    { width: 559, height: 362 },
    { width: 448, height: 386 },
    { width: 451, height: 304 },
] as const;

const CARD_GAP = 32;
const CAROUSEL_SPEED = 60; // px/s

const rootRef = ref<HTMLElement | null>(null);
const trackRef = ref<HTMLElement | null>(null);

const hoveredCaseIndex = ref<number | null>(null);
const caseVideoRefs = new Map<number, HTMLVideoElement>();

const casesDisplayItems = computed(() => [...props.cases, ...props.cases]);

const casesTrackOffset = computed(() => {
    const totalWidth = props.cases.reduce(
        (sum, _, i) => sum + CARD_SIZES[i % CARD_SIZES.length].width,
        0,
    );

    return totalWidth + props.cases.length * CARD_GAP;
});

// Offset leeft buiten Vue's reactiviteit: de transform wordt per frame direct
// op het track-element gezet, zodat de pagina niet 60×/s opnieuw rendert.
let carouselOffset = 0;
let carouselDragStartX = 0;
let carouselDragStartOffset = 0;
let carouselVelocity = 0;
let carouselLastDragX = 0;
let carouselLastDragTime = 0;
const carouselDragging = ref(false);

function applyTrackTransform(): void {
    if (trackRef.value) {
        trackRef.value.style.transform = `translateX(-${carouselOffset.toFixed(1)}px)`;
    }
}

function tick(dt: number): void {
    const total = casesTrackOffset.value;

    if (total <= 0) {
        return;
    }

    if (!carouselDragging.value && hoveredCaseIndex.value === null) {
        carouselOffset = wrap(carouselOffset + CAROUSEL_SPEED * dt, total);
        applyTrackTransform();
    } else if (!carouselDragging.value && Math.abs(carouselVelocity) > 1) {
        carouselVelocity *= 0.9;
        carouselOffset = wrap(carouselOffset + carouselVelocity * dt, total);
        applyTrackTransform();
    }
}

useAnimationLoop(rootRef, tick);

onMounted(applyTrackTransform);
watch(trackRef, applyTrackTransform);

function onCarouselPointerDown(e: PointerEvent): void {
    carouselDragging.value = true;
    carouselDragStartX = e.clientX;
    carouselDragStartOffset = carouselOffset;
    carouselVelocity = 0;
    carouselLastDragX = e.clientX;
    carouselLastDragTime = performance.now();
    (e.currentTarget as HTMLElement).setPointerCapture(e.pointerId);
}

function onCarouselPointerMove(e: PointerEvent): void {
    if (!carouselDragging.value) {
        return;
    }

    const total = casesTrackOffset.value;

    if (total > 0) {
        carouselOffset = wrap(
            carouselDragStartOffset + (carouselDragStartX - e.clientX),
            total,
        );
        applyTrackTransform();
    }

    const now = performance.now();
    const dt = (now - carouselLastDragTime) / 1000;

    if (dt > 0) {
        carouselVelocity = (carouselLastDragX - e.clientX) / dt;
        carouselLastDragX = e.clientX;
        carouselLastDragTime = now;
    }
}

function onCarouselPointerUp(): void {
    carouselDragging.value = false;
}

function caseIndex(displayIndex: number): number {
    return displayIndex % Math.max(props.cases.length, 1);
}

function caseCardSize(displayIndex: number) {
    return CARD_SIZES[caseIndex(displayIndex) % CARD_SIZES.length];
}

function isCaseHovered(displayIndex: number): boolean {
    return hoveredCaseIndex.value === caseIndex(displayIndex);
}

function setCaseVideoRef(instance: unknown, displayIndex: number): void {
    const video =
        instance !== null &&
        typeof instance === 'object' &&
        'videoEl' in instance &&
        instance.videoEl instanceof HTMLVideoElement
            ? instance.videoEl
            : null;

    if (video) {
        caseVideoRefs.set(displayIndex, video);
    } else {
        caseVideoRefs.delete(displayIndex);
    }
}

function onCaseMouseEnter(displayIndex: number): void {
    const i = caseIndex(displayIndex);
    hoveredCaseIndex.value = i;
    caseVideoRefs.forEach((video, idx) => {
        if (caseIndex(idx) === i) {
            void video.play().catch(() => undefined);
        }
    });
}

function onCaseMouseLeave(displayIndex: number): void {
    const i = caseIndex(displayIndex);

    if (hoveredCaseIndex.value === i) {
        hoveredCaseIndex.value = null;
    }

    caseVideoRefs.forEach((video, idx) => {
        if (caseIndex(idx) === i) {
            video.pause();
            video.currentTime = 0;
        }
    });
}
</script>

<template>
    <div ref="rootRef" class="relative h-full overflow-hidden bg-[#0c0c0c]">
        <!-- "↙ Cases" titel rechtsboven — pijl links van de titel -->
        <div class="absolute top-6 right-[59px] z-10 flex items-center gap-3">
            <img :src="CASES_ARROW_URL" alt="" class="h-[52px] w-[52px]" />
            <h2
                class="font-avenir"
                style="
                    font-weight: 700;
                    font-style: oblique;
                    font-size: 80px;
                    line-height: 71px;
                    letter-spacing: -2.4px;
                    color: white;
                "
            >
                Cases
            </h2>
        </div>

        <!-- Auto-scroll + draggable carousel -->
        <div
            class="absolute overflow-hidden"
            style="
                top: 88px;
                bottom: 112px;
                left: 0;
                right: 0;
                display: flex;
                align-items: center;
            "
            @pointerdown="onCarouselPointerDown"
            @pointermove="onCarouselPointerMove"
            @pointerup="onCarouselPointerUp"
            @pointercancel="onCarouselPointerUp"
        >
            <div
                ref="trackRef"
                class="flex shrink-0 items-center"
                :style="{
                    cursor: carouselDragging ? 'grabbing' : 'grab',
                    willChange: 'transform',
                    userSelect: 'none',
                    gap: CARD_GAP + 'px',
                    paddingLeft: '59px',
                }"
            >
                <a
                    v-for="(caseItem, i) in casesDisplayItems"
                    :key="i"
                    :href="`/cases/${caseItem.slug}`"
                    class="relative shrink-0 cursor-pointer overflow-hidden rounded-[20px] select-none"
                    :style="{
                        width: caseCardSize(i).width + 'px',
                        height: caseCardSize(i).height + 'px',
                        filter:
                            hoveredCaseIndex !== null && !isCaseHovered(i)
                                ? 'blur(17px)'
                                : 'none',
                        transition: 'filter 0.35s ease',
                    }"
                    @mouseenter="!carouselDragging && onCaseMouseEnter(i)"
                    @mouseleave="onCaseMouseLeave(i)"
                >
                    <!-- Laag 1: foto achtergrond -->
                    <img
                        v-if="caseItem.photo"
                        :src="caseItem.photo"
                        :alt="caseItem.name"
                        class="absolute inset-0 h-full w-full object-cover"
                        draggable="false"
                    />
                    <div v-else class="absolute inset-0 bg-neutral-800" />

                    <!-- Laag 2: video (speelt af op hover) -->
                    <HlsVideo
                        v-if="caseItem.video"
                        :ref="(instance) => setCaseVideoRef(instance, i)"
                        :src="caseItem.video"
                        loop
                        muted
                        playsinline
                        class="absolute inset-0 h-full w-full object-cover transition-opacity duration-300"
                        :class="isCaseHovered(i) ? 'opacity-100' : 'opacity-0'"
                    />

                    <!-- Laag 3: overlay — titel + pijl (altijd boven video) -->
                    <div class="absolute inset-0 z-10">
                        <!-- Donkere scrim op hover -->
                        <div
                            class="absolute inset-0 bg-black transition-opacity duration-300"
                            :class="
                                isCaseHovered(i) ? 'opacity-40' : 'opacity-0'
                            "
                        />

                        <!-- Standaard naam: linksboven, klein -->
                        <div
                            class="absolute top-5 left-5 transition-opacity duration-300"
                            :style="{ opacity: isCaseHovered(i) ? 0 : 1 }"
                        >
                            <span
                                class="font-avenir"
                                style="
                                    font-weight: 900;
                                    font-style: oblique;
                                    font-size: 26px;
                                    line-height: 1;
                                    letter-spacing: -0.8px;
                                    color: #ffc700;
                                "
                            >
                                {{ caseItem.name }}
                            </span>
                        </div>

                        <!-- Hover naam: verticaal gecentreerd, groot -->
                        <div
                            class="absolute inset-0 flex items-center justify-center px-8 transition-opacity duration-300"
                            :style="{ opacity: isCaseHovered(i) ? 1 : 0 }"
                        >
                            <span
                                class="font-avenir"
                                style="
                                    font-weight: 900;
                                    font-style: oblique;
                                    font-size: 80px;
                                    line-height: 1;
                                    letter-spacing: -2.4px;
                                    color: #ffc700;
                                    text-align: center;
                                "
                            >
                                {{ caseItem.name }}
                            </span>
                        </div>

                        <!-- Pijl rechtsonder op hover -->
                        <div
                            class="absolute right-5 bottom-5 transition-opacity duration-300"
                            :style="{ opacity: isCaseHovered(i) ? 1 : 0 }"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="58"
                                height="56"
                                viewBox="0 0 58 56"
                                fill="none"
                                style="width: 52px; height: 52px"
                                aria-hidden="true"
                            >
                                <path
                                    d="M21.3457 53.3944L54.8379 52.672L54.0832 17.6802"
                                    stroke="#FFC700"
                                    stroke-width="5"
                                />
                                <path
                                    d="M53.8163 51.6939L1.72841 1.80544"
                                    stroke="#FFC700"
                                    stroke-width="5"
                                />
                            </svg>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Cases footer -->
        <div class="absolute right-0 bottom-0 left-0">
            <div class="mx-[59px] border-t border-white/20" />
            <div
                class="flex min-h-[168px] flex-col items-end justify-start px-[59px] pt-[48px]"
            >
                <p
                    class="font-avenir"
                    style="
                        font-weight: 400;
                        font-size: 20px;
                        line-height: 24px;
                        letter-spacing: -0.2px;
                        color: white;
                        text-align: right;
                    "
                >
                    Discover more credible creativity
                </p>
                <a
                    href="/cases"
                    class="mt-6 flex shrink-0 items-center justify-end gap-3 font-avenir text-white"
                    style="
                        font-size: 13px;
                        line-height: 16px;
                        letter-spacing: -0.13px;
                    "
                >
                    Show all cases
                    <svg
                        width="16"
                        height="16"
                        viewBox="0 0 16 16"
                        fill="none"
                        aria-hidden="true"
                    >
                        <path
                            d="M3 8H13M13 8L8 3M13 8L8 13"
                            stroke="#FFC700"
                            stroke-width="1.6"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                </a>
            </div>
        </div>
    </div>
</template>
