<script setup lang="ts">
import HlsVideo from '@/components/HlsVideo.vue'
import { computed, ref } from 'vue'

defineProps<{
    cases: Array<{
        name: string
        slug: string
        video: string | null
        photo: string | null
        touchpoints?: string[]
    }>
}>()

const scrollEl = ref<HTMLElement | null>(null)
const currentIndex = ref(0)

function pad2(n: number): string {
    return String(n).padStart(2, '0')
}

function scrollToIndex(index: number): void {
    if (!scrollEl.value) return
    const cards = scrollEl.value.querySelectorAll<HTMLElement>('[data-card]')
    const card = cards[index]
    if (!card) return
    const containerLeft = scrollEl.value.getBoundingClientRect().left
    const cardLeft = card.getBoundingClientRect().left
    scrollEl.value.scrollLeft += cardLeft - containerLeft
    currentIndex.value = index
}

function prev(): void {
    if (currentIndex.value > 0) scrollToIndex(currentIndex.value - 1)
}

function next(count: number): void {
    if (currentIndex.value < count - 1) scrollToIndex(currentIndex.value + 1)
}

function onScroll(count: number): void {
    if (!scrollEl.value) return
    const cards = scrollEl.value.querySelectorAll<HTMLElement>('[data-card]')
    const containerLeft = scrollEl.value.getBoundingClientRect().left
    let closest = 0
    let minDist = Infinity
    cards.forEach((card, i) => {
        const dist = Math.abs(card.getBoundingClientRect().left - containerLeft)
        if (dist < minDist) {
            minDist = dist
            closest = i
        }
    })
    currentIndex.value = closest
}
</script>

<template>
    <div>
        <!-- Scrollable track -->
        <div
            ref="scrollEl"
            class="flex overflow-x-auto"
            style="scroll-snap-type: x mandatory; scrollbar-width: none; -ms-overflow-style: none; gap: 20px;"
            @scroll="onScroll(cases.length)"
        >
            <!-- Left spacer keeps first card flush -->
            <div class="w-0 shrink-0" />

            <a
                v-for="item in cases"
                :key="item.slug"
                data-card
                :href="`/cases/${item.slug}`"
                class="relative shrink-0 overflow-hidden rounded-[20px]"
                style="scroll-snap-align: start; width: min(700px, 85vw); aspect-ratio: 16 / 10;"
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

            <!-- Right spacer -->
            <div class="w-4 shrink-0" />
        </div>

        <!-- Controls row -->
        <div class="mt-6 flex items-center justify-between pr-6">
            <!-- Pagination counter -->
            <div class="flex items-center gap-0 text-white">
                <span
                    style="font-family: 'Avenir', sans-serif; font-size: 15px; font-weight: 500; color: #ffc700;"
                >{{ pad2(currentIndex + 1) }}</span>
                <span class="mx-3 block h-px w-8 bg-white/30" />
                <span
                    style="font-family: 'Avenir', sans-serif; font-size: 15px; font-weight: 500; color: rgba(255,255,255,0.35);"
                >{{ pad2(cases.length) }}</span>
            </div>

            <!-- Arrow buttons -->
            <div class="flex gap-3">
                <button
                    :disabled="currentIndex === 0"
                    class="flex h-[52px] w-[52px] items-center justify-center rounded-full border bg-transparent transition-all duration-200"
                    :class="currentIndex === 0 ? 'border-white/20 cursor-default opacity-25' : 'border-[#F6C000] cursor-pointer'"
                    @click="prev"
                >
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                        <path d="M11 3.5L5.5 9L11 14.5" :stroke="currentIndex === 0 ? 'white' : '#F6C000'" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
                <button
                    :disabled="currentIndex === cases.length - 1"
                    class="flex h-[52px] w-[52px] items-center justify-center rounded-full border bg-transparent transition-all duration-200"
                    :class="currentIndex === cases.length - 1 ? 'border-white/20 cursor-default opacity-25' : 'border-[#F6C000] cursor-pointer'"
                    @click="next(cases.length)"
                >
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                        <path d="M7 3.5L12.5 9L7 14.5" :stroke="currentIndex === cases.length - 1 ? 'white' : '#F6C000'" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</template>
