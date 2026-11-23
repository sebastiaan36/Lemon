<script setup lang="ts">
import { computed, onBeforeUnmount, ref } from 'vue';
import BlogCard from '@/components/blog/BlogCard.vue';
import type { BlogCardItem } from '@/components/blog/BlogCard.vue';

const props = defineProps<{
    blogs: BlogCardItem[];
}>();

const track = ref<HTMLElement | null>(null);
const currentIndex = ref(0);
const atEnd = ref(false);
let scrollFrame: number | null = null;

const atStart = computed(() => currentIndex.value === 0);

function pad2(value: number): string {
    return String(value).padStart(2, '0');
}

function cards(): HTMLElement[] {
    return track.value
        ? Array.from(track.value.querySelectorAll<HTMLElement>('[data-card]'))
        : [];
}

function syncIndex(): void {
    scrollFrame = null;
    const el = track.value;

    if (!el) {
        return;
    }

    const left = el.getBoundingClientRect().left;
    let closest = 0;
    let minDistance = Infinity;

    cards().forEach((card, index) => {
        const distance = Math.abs(card.getBoundingClientRect().left - left);

        if (distance < minDistance) {
            minDistance = distance;
            closest = index;
        }
    });

    atEnd.value = el.scrollLeft + el.clientWidth >= el.scrollWidth - 4;
    currentIndex.value = atEnd.value ? props.blogs.length - 1 : closest;
}

function onScroll(): void {
    if (scrollFrame === null) {
        scrollFrame = requestAnimationFrame(syncIndex);
    }
}

function scrollToCard(index: number): void {
    const el = track.value;
    const card = cards()[index];

    if (!el || !card) {
        return;
    }

    el.scrollTo({
        left:
            el.scrollLeft +
            card.getBoundingClientRect().left -
            el.getBoundingClientRect().left,
        behavior: 'smooth',
    });
}

function previous(): void {
    scrollToCard(Math.max(0, currentIndex.value - 1));
}

function next(): void {
    scrollToCard(Math.min(props.blogs.length - 1, currentIndex.value + 1));
}

onBeforeUnmount(() => {
    if (scrollFrame !== null) {
        cancelAnimationFrame(scrollFrame);
    }
});
</script>

<template>
    <div>
        <!-- Figma: kaarten van 537px, 43px tussenruimte, lopen rechts uit beeld -->
        <div
            ref="track"
            class="flex snap-x snap-mandatory gap-[clamp(16px,2.6vw,43px)] overflow-x-auto scroll-smooth pr-5 pl-5 [scrollbar-width:none] md:scroll-pl-[8.2vw] md:pl-[8.2vw] [&::-webkit-scrollbar]:hidden"
            @scroll="onScroll"
        >
            <div
                v-for="blog in blogs"
                :key="blog.slug"
                data-card
                class="w-[min(80vw,537px)] shrink-0 snap-start md:w-[clamp(320px,32.75vw,537px)]"
            >
                <BlogCard :blog="blog" />
            </div>
        </div>

        <div
            class="mt-8 flex items-center justify-between px-5 md:mt-[50px] md:pr-[4.6vw] md:pl-[8.7vw]"
        >
            <div
                class="flex items-center gap-[7px] font-avenir text-[15.8px] tracking-[-0.47px] text-white"
                aria-hidden="true"
            >
                <span class="w-[22px]">{{ pad2(currentIndex + 1) }}</span>
                <span class="block h-px w-[25px] bg-white" />
                <span class="pl-[7px]">{{ pad2(blogs.length) }}</span>
            </div>

            <div class="flex gap-[12px]">
                <button
                    type="button"
                    aria-label="Previous article"
                    :disabled="atStart"
                    class="flex size-[50px] items-center justify-center rounded-full border-[0.85px] transition-colors md:size-[59px]"
                    :class="
                        atStart
                            ? 'cursor-default border-[#B5BAB7] text-[#B5BAB7]'
                            : 'cursor-pointer border-[#F6C000] text-[#F6C000] hover:bg-[#F6C000]/10'
                    "
                    @click="previous"
                >
                    <svg
                        width="25"
                        height="17"
                        viewBox="0 0 25 17"
                        fill="none"
                        aria-hidden="true"
                    >
                        <path
                            d="M8.1 1.1L0.5 8.7L8.4 16.6M0.9 8.7H24"
                            stroke="currentColor"
                            stroke-width="1.6"
                        />
                    </svg>
                </button>
                <button
                    type="button"
                    aria-label="Next article"
                    :disabled="atEnd"
                    class="flex size-[50px] items-center justify-center rounded-full border-[0.85px] transition-colors md:size-[59px]"
                    :class="
                        atEnd
                            ? 'cursor-default border-[#B5BAB7] text-[#B5BAB7]'
                            : 'cursor-pointer border-[#F6C000] text-[#F6C000] hover:bg-[#F6C000]/10'
                    "
                    @click="next"
                >
                    <svg
                        width="25"
                        height="17"
                        viewBox="0 0 25 17"
                        fill="none"
                        aria-hidden="true"
                    >
                        <path
                            d="M16.4 16.1L24 8.5L16.1 0.6M23.6 8.5H0.5"
                            stroke="currentColor"
                            stroke-width="1.6"
                        />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</template>
