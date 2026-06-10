<script setup lang="ts">
import HlsVideo from '@/components/HlsVideo.vue'
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from 'vue'

type CaseItem = {
    name: string
    slug: string
    video: string | null
    photo: string | null
    touchpoints?: string[]
}

const props = withDefaults(
    defineProps<{
        cases: CaseItem[]
        loop?: boolean
        showCounter?: boolean
        controlsPaddingRight?: string
        heights?: string[]
        cardWidth?: string
        cardWidths?: string[]
        centered?: boolean
        infinite?: boolean
        aspectRatio?: string
        gap?: string
    }>(),
    {
        loop: false,
        showCounter: true,
        controlsPaddingRight: '24px',
        heights: () => [],
        cardWidth: 'min(700px, 85vw)',
        cardWidths: () => [],
        centered: false,
        infinite: false,
        aspectRatio: '16 / 10',
        gap: '20px',
    },
)

// Infinite scroll renders three copies of the list; we keep the scroll position
// parked in the middle copy and jump by one copy-width when an edge is reached,
// so there is always a case bleeding in on both sides.
const displayCases = computed<CaseItem[]>(() =>
    props.infinite
        ? [...props.cases, ...props.cases, ...props.cases]
        : props.cases,
)

// In centered (non-infinite) mode the edge spacers must be half a viewport minus
// half a card wide so the first and last cards can reach the centre. Infinite
// mode never reaches a real edge, so it needs no spacers.
const spacerStyle = computed<Record<string, string>>(() =>
    props.centered && !props.infinite
        ? {
              width: `calc((100% - ${props.cardWidth}) / 2)`,
              flexShrink: '0',
          }
        : { flexShrink: '0' },
)

function cardStyle(index: number): Record<string, string> {
    const width = props.cardWidths.length
        ? props.cardWidths[index % props.cardWidths.length]
        : props.cardWidth
    const base = {
        scrollSnapAlign: props.centered ? 'center' : 'start',
        scrollSnapStop: 'always',
        width,
    }
    if (props.heights.length) {
        return { ...base, height: props.heights[index % props.heights.length] }
    }
    return { ...base, aspectRatio: props.aspectRatio }
}

const scrollEl = ref<HTMLElement | null>(null)
const currentIndex = ref(0)

// Width of one copy of the list (n cards + gaps), measured after layout.
let copyWidth = 0

function pad2(n: number): string {
    return String(n).padStart(2, '0')
}

// Distance the scroll must move to bring `card` to its resting anchor:
// the container's left edge (default) or its centre (centered mode).
function alignOffset(card: HTMLElement): number {
    if (!scrollEl.value) return 0
    const cardRect = card.getBoundingClientRect()
    const containerRect = scrollEl.value.getBoundingClientRect()
    if (props.centered) {
        return (
            cardRect.left +
            cardRect.width / 2 -
            (containerRect.left + containerRect.width / 2)
        )
    }
    return cardRect.left - containerRect.left
}

let snapTimer: ReturnType<typeof setTimeout> | null = null
let progTimer: ReturnType<typeof setTimeout> | null = null
let isProgrammaticScroll = false

function scrollToIndex(index: number, smooth = false): void {
    if (!scrollEl.value) return
    const cards = scrollEl.value.querySelectorAll<HTMLElement>('[data-card]')
    const card = cards[index]
    if (!card) return
    if (smooth) {
        isProgrammaticScroll = true
        scrollEl.value.scrollTo({
            left: scrollEl.value.scrollLeft + alignOffset(card),
            behavior: 'smooth',
        })
        if (progTimer) clearTimeout(progTimer)
        progTimer = setTimeout(() => {
            isProgrammaticScroll = false
        }, 450)
    } else {
        scrollEl.value.scrollLeft += alignOffset(card)
    }
    currentIndex.value = index
}

function prev(count: number): void {
    if (props.infinite) {
        scrollToIndex(currentIndex.value - 1, true)
    } else if (currentIndex.value > 0) {
        scrollToIndex(currentIndex.value - 1, true)
    } else if (props.loop) {
        scrollToIndex(count - 1, true)
    }
}

function next(count: number): void {
    if (props.infinite) {
        scrollToIndex(currentIndex.value + 1, true)
    } else if (currentIndex.value < count - 1) {
        scrollToIndex(currentIndex.value + 1, true)
    } else if (props.loop) {
        scrollToIndex(0, true)
    }
}

// Keep the scroll position parked inside the middle copy by jumping a whole
// copy-width whenever it drifts past the half-copy guard band. The jump is
// invisible because the content one copy over is identical.
function repositionInfinite(): void {
    if (!props.infinite || copyWidth <= 0 || !scrollEl.value) return
    const sl = scrollEl.value.scrollLeft
    if (sl < copyWidth * 0.5) {
        scrollEl.value.scrollLeft = sl + copyWidth
    } else if (sl > copyWidth * 1.5) {
        scrollEl.value.scrollLeft = sl - copyWidth
    }
}

function onScroll(): void {
    if (!scrollEl.value) return
    repositionInfinite()

    const cards = scrollEl.value.querySelectorAll<HTMLElement>('[data-card]')
    let closest = 0
    let minDist = Infinity
    cards.forEach((card, i) => {
        const dist = Math.abs(alignOffset(card))
        if (dist < minDist) {
            minDist = dist
            closest = i
        }
    })
    currentIndex.value = closest

    // After free scrolling settles, snap the nearest card to its anchor so no
    // card stays half-cut on an edge.
    if (isProgrammaticScroll) return
    if (snapTimer) clearTimeout(snapTimer)
    snapTimer = setTimeout(settleSnap, 90)
}

function settleSnap(): void {
    if (!scrollEl.value) return
    const cards = scrollEl.value.querySelectorAll<HTMLElement>('[data-card]')
    const card = cards[currentIndex.value]
    if (!card) return
    const delta = alignOffset(card)
    if (Math.abs(delta) < 2) return
    isProgrammaticScroll = true
    scrollEl.value.scrollTo({
        left: scrollEl.value.scrollLeft + delta,
        behavior: 'smooth',
    })
    if (progTimer) clearTimeout(progTimer)
    progTimer = setTimeout(() => {
        isProgrammaticScroll = false
    }, 400)
}

// Measure one copy-width and park the scroll on the first card of the middle
// copy, centred. Re-run on resize since card widths are viewport-relative.
function setupInfinite(): void {
    if (!props.infinite || !scrollEl.value) return
    const cards = scrollEl.value.querySelectorAll<HTMLElement>('[data-card]')
    const n = props.cases.length
    if (cards.length < n * 2 || n === 0) return
    copyWidth = cards[n].offsetLeft - cards[0].offsetLeft
    isProgrammaticScroll = true
    scrollEl.value.scrollLeft += alignOffset(cards[n])
    currentIndex.value = n
    requestAnimationFrame(() => {
        isProgrammaticScroll = false
    })
}

function onResize(): void {
    if (!props.infinite || !scrollEl.value) return
    const cards = scrollEl.value.querySelectorAll<HTMLElement>('[data-card]')
    const n = props.cases.length
    if (cards.length < n * 2 || n === 0) return
    copyWidth = cards[n].offsetLeft - cards[0].offsetLeft
    const card = cards[currentIndex.value]
    if (!card) return
    isProgrammaticScroll = true
    scrollEl.value.scrollLeft += alignOffset(card)
    requestAnimationFrame(() => {
        isProgrammaticScroll = false
    })
}

// Buttons are only ever disabled in a plain (non-loop, non-infinite) carousel.
const atStart = computed(
    () => !props.loop && !props.infinite && currentIndex.value === 0,
)
const atEnd = computed(
    () =>
        !props.loop &&
        !props.infinite &&
        currentIndex.value === props.cases.length - 1,
)
const counterIndex = computed(() =>
    props.cases.length ? (currentIndex.value % props.cases.length) + 1 : 0,
)

onMounted(() => {
    if (props.infinite) {
        nextTick(setupInfinite)
        window.addEventListener('resize', onResize)
    }
})

onBeforeUnmount(() => {
    window.removeEventListener('resize', onResize)
    if (snapTimer) clearTimeout(snapTimer)
    if (progTimer) clearTimeout(progTimer)
})
</script>

<template>
    <div>
        <!-- Scrollable track -->
        <div
            ref="scrollEl"
            class="flex overflow-x-auto"
            :class="{ 'items-center': heights.length || cardWidths.length }"
            style="scroll-snap-type: x mandatory; scrollbar-width: none; -ms-overflow-style: none;"
            :style="{ gap }"
            @scroll="onScroll"
        >
            <!-- Left spacer: 0 (flush mode) or half-viewport gutter (centered mode) -->
            <div :class="{ 'w-0': !centered }" :style="spacerStyle" />

            <a
                v-for="(item, i) in displayCases"
                :key="i"
                data-card
                :href="`/cases/${item.slug}`"
                class="relative shrink-0 overflow-hidden rounded-[20px]"
                :style="cardStyle(i)"
            >
                <!-- Background photo -->
                <img
                    v-if="item.photo"
                    :src="item.photo"
                    :alt="item.name"
                    class="absolute inset-0 h-full w-full object-cover"
                    draggable="false"
                />
                <div v-else class="absolute inset-0 bg-neutral-800" />

                <!-- Video overlay (autoplay) -->
                <HlsVideo
                    v-if="item.video"
                    :src="item.video"
                    autoplay
                    loop
                    muted
                    playsinline
                    class="absolute inset-0 h-full w-full object-cover"
                />

                <!-- Gradient mask bottom -->
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent" />

                <!-- Case name top-left -->
                <div class="absolute left-5 top-5 z-10">
                    <span
                        style="font-family: 'Avenir', sans-serif; font-weight: 900; font-style: oblique; font-size: 26px; line-height: 1; letter-spacing: -0.8px; color: #ffc700;"
                    >
                        {{ item.name }}
                    </span>
                </div>

                <!-- Touchpoint pills bottom-right -->
                <div
                    v-if="item.touchpoints && item.touchpoints.length"
                    class="absolute bottom-5 right-5 z-10 flex flex-wrap justify-end gap-2"
                >
                    <span
                        v-for="tag in item.touchpoints"
                        :key="tag"
                        class="rounded-full border border-white/60 px-3 py-1 text-xs text-white"
                        style="font-family: 'Avenir', sans-serif; font-size: 12px; letter-spacing: 0.02em;"
                    >
                        {{ tag }}
                    </span>
                </div>
            </a>

            <!-- Right spacer: small (flush mode) or half-viewport gutter (centered mode) -->
            <div :class="{ 'w-4': !centered }" :style="spacerStyle" />
        </div>

        <!-- Controls row -->
        <div
            class="mt-6 flex items-center"
            :class="showCounter ? 'justify-between' : 'justify-end'"
            :style="{ paddingRight: controlsPaddingRight }"
        >
            <!-- Pagination counter -->
            <div v-if="showCounter" class="flex items-center gap-0 text-white">
                <span
                    style="font-family: 'Avenir', sans-serif; font-size: 15px; font-weight: 500; color: #ffc700;"
                >{{ pad2(counterIndex) }}</span>
                <span class="mx-3 block h-px w-8 bg-white/30" />
                <span
                    style="font-family: 'Avenir', sans-serif; font-size: 15px; font-weight: 500; color: rgba(255,255,255,0.35);"
                >{{ pad2(cases.length) }}</span>
            </div>

            <!-- Arrow buttons -->
            <div class="flex gap-3">
                <button
                    :disabled="atStart"
                    class="flex h-[44px] w-[44px] items-center justify-center rounded-full border bg-transparent transition-all duration-200"
                    :class="atStart ? 'border-white/20 cursor-default opacity-25' : 'border-[#F6C000] cursor-pointer'"
                    @click="prev(cases.length)"
                >
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                        <path d="M14.5 9H3.5M3.5 9L8 4.5M3.5 9L8 13.5" :stroke="atStart ? 'white' : '#F6C000'" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
                <button
                    :disabled="atEnd"
                    class="flex h-[44px] w-[44px] items-center justify-center rounded-full border bg-transparent transition-all duration-200"
                    :class="atEnd ? 'border-white/20 cursor-default opacity-25' : 'border-[#F6C000] cursor-pointer'"
                    @click="next(cases.length)"
                >
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                        <path d="M3.5 9H14.5M14.5 9L10 4.5M14.5 9L10 13.5" :stroke="atEnd ? 'white' : '#F6C000'" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</template>
