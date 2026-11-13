<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import CaseCard from '@/components/cases/CaseCard.vue';
import type { CaseCardItem } from '@/components/cases/CaseCard.vue';
import FloatingPill from '@/components/FloatingPill.vue';
import SiteFooter from '@/components/SiteFooter.vue';
import SiteHeader from '@/components/SiteHeader.vue';

type CaseItem = CaseCardItem;

type RowItem = { type: 'case'; data: CaseItem } | { type: 'editorial' };

const props = defineProps<{
    cases: CaseItem[];
    allTouchpoints: string[];
}>();

const activeFilter = ref<string | null>(null);
const dropdownOpen = ref(false);

function selectFilter(value: string | null) {
    activeFilter.value = value;
    dropdownOpen.value = false;
}

const dropdownLabel = computed(() => activeFilter.value ?? 'Show all');

function onClickOutside(e: MouseEvent) {
    const el = document.querySelector('[data-filter-dropdown]');

    if (el && !el.contains(e.target as Node)) {
        dropdownOpen.value = false;
    }
}

onMounted(() => document.addEventListener('mousedown', onClickOutside));
onUnmounted(() => document.removeEventListener('mousedown', onClickOutside));

const filteredCases = computed(() =>
    activeFilter.value
        ? props.cases.filter((c) => c.touchpoints.includes(activeFilter.value!))
        : props.cases,
);

// Figma: het gele "Credible Creativity"-blok staat na de 4e case. Bij 4 of minder
// cases komt het achteraan, zodat het altijd zichtbaar is.
const EDITORIAL_POSITION = 4;
const CONTACT_AVATAR = '/figma-assets/gijs-avatar.png';

const items = computed<RowItem[]>(() => {
    const result: RowItem[] = filteredCases.value.map((c) => ({
        type: 'case' as const,
        data: c,
    }));
    const position = Math.min(EDITORIAL_POSITION, result.length);
    result.splice(position, 0, { type: 'editorial' });

    return result;
});

// Pair items into rows of 2
const rows = computed<[RowItem, RowItem | null][]>(() => {
    const pairs: [RowItem, RowItem | null][] = [];

    for (let i = 0; i < items.value.length; i += 2) {
        pairs.push([items.value[i], items.value[i + 1] ?? null]);
    }

    return pairs;
});

// Rotation values for organic feel
const rotations = [
    '-0.7deg',
    '0.08deg',
    '0.22deg',
    '-0.48deg',
    '0.13deg',
    '0.1deg',
    '0.14deg',
    '-0.54deg',
];
function cardRotation(seed: number): string {
    return rotations[seed % rotations.length];
}

/**
 * Figma (1640 breed): twee gelijke kolommen van 694px met 23px ertussen en 114px
 * zijmarge. Per rij wisselen een grote kaart (volle kolombreedte, 694×457) en een
 * kleine kaart (76% van de kolom, 528×~370, ~62px van de buitenrand) van kant.
 * Alle maten schalen mee met de schermbreedte. Op mobiel één kolom.
 */
const BIG_CARD = 'w-full';
const SMALL_CARD_LEFT = 'w-full md:ml-[9%] md:w-[76%]';
const SMALL_CARD_RIGHT = 'w-full md:mr-[9%] md:w-[76%]';
const BIG_ASPECT = 'aspect-[4/3] md:aspect-[694/457]';
const SMALL_ASPECT = 'aspect-[4/3] md:aspect-[528/370]';

// Figma: rij 1 begint met de kleine kaart links, daarna wisselt het per rij.
// Zo valt het gele blok (na de 4e case) op een kleine plek links, zoals in het ontwerp.
function isBigLeft(rowIndex: number): boolean {
    return rowIndex % 2 === 1;
}

function leftColClass(rowIndex: number): string {
    return isBigLeft(rowIndex)
        ? 'w-full md:w-[calc(50%-0.7vw)] md:flex-none'
        : 'w-full md:w-[calc(50%-0.7vw)] md:flex-none flex justify-start';
}

function rightColClass(rowIndex: number): string {
    return isBigLeft(rowIndex)
        ? 'w-full md:w-[calc(50%-0.7vw)] md:flex-none flex justify-end'
        : 'w-full md:w-[calc(50%-0.7vw)] md:flex-none';
}

function leftCardClass(rowIndex: number): string {
    return isBigLeft(rowIndex) ? BIG_CARD : SMALL_CARD_LEFT;
}

function rightCardClass(rowIndex: number): string {
    return isBigLeft(rowIndex) ? SMALL_CARD_RIGHT : BIG_CARD;
}

function leftAspect(rowIndex: number): string {
    return isBigLeft(rowIndex) ? BIG_ASPECT : SMALL_ASPECT;
}

function rightAspect(rowIndex: number): string {
    return isBigLeft(rowIndex) ? SMALL_ASPECT : BIG_ASPECT;
}
</script>

<template>
    <Head>
        <title>Cases — Lemon Scented Tea</title>
    </Head>

    <div class="min-h-screen overflow-x-clip bg-black text-white">
        <SiteHeader hide-top-gradient invert-logo cta-style="outline" />

        <!-- ============ HERO TITLE ============ -->
        <section
            class="px-5 pt-[130px] pb-8 text-center md:pt-[180px] md:pb-[52px] lg:pt-[220px]"
        >
            <h1
                class="inline font-avenir text-[clamp(52px,14vw,80px)] leading-none font-black tracking-[-0.035em] italic lg:text-[130px]"
            >
                <span class="text-[#ffc700]">Lemon </span>
                <span class="text-white">cases</span>
            </h1>
        </section>

        <!-- ============ FILTER BAR ============ -->
        <section
            class="flex justify-center px-5 pb-10 md:px-[59px] md:pb-[52px]"
        >
            <div class="flex items-center gap-3">
                <span
                    class="font-avenir text-[16px] tracking-[-0.48px] text-white/50"
                >
                    Filter
                </span>

                <!-- Dropdown trigger -->
                <div
                    class="relative"
                    data-filter-dropdown
                    @keydown.escape="dropdownOpen = false"
                >
                    <button
                        class="flex h-[50px] min-w-[140px] items-center justify-between gap-3 rounded-[30px] border border-white/30 px-6 font-avenir text-[16px] tracking-[-0.48px] text-white transition-colors hover:border-white/50"
                        @click="dropdownOpen = !dropdownOpen"
                    >
                        {{ dropdownLabel }}
                        <svg
                            class="h-[9px] w-[11px] shrink-0 opacity-60 transition-transform duration-200"
                            :class="dropdownOpen ? 'rotate-180' : ''"
                            viewBox="0 0 11 9"
                            fill="none"
                        >
                            <path
                                d="M5.5 9L0 0h11L5.5 9Z"
                                fill="currentColor"
                            />
                        </svg>
                    </button>

                    <!-- Dropdown menu -->
                    <Transition
                        enter-active-class="transition duration-150 ease-out"
                        enter-from-class="opacity-0 translate-y-[-6px]"
                        enter-to-class="opacity-100 translate-y-0"
                        leave-active-class="transition duration-100 ease-in"
                        leave-from-class="opacity-100 translate-y-0"
                        leave-to-class="opacity-0 translate-y-[-6px]"
                    >
                        <div
                            v-if="dropdownOpen"
                            class="absolute top-[calc(100%+8px)] left-0 z-50 min-w-full overflow-hidden rounded-[20px] border border-white/10 bg-[#111] py-2 shadow-xl"
                        >
                            <button
                                class="flex w-full items-center px-5 py-3 text-left font-avenir text-[15px] tracking-[-0.45px] transition-colors hover:bg-white/5"
                                :class="
                                    activeFilter === null
                                        ? 'text-white'
                                        : 'text-white/60'
                                "
                                @click="selectFilter(null)"
                            >
                                <span
                                    class="mr-3 flex h-[6px] w-[6px] shrink-0 items-center justify-center rounded-full bg-[#ffc700]"
                                    :class="
                                        activeFilter === null
                                            ? 'opacity-100'
                                            : 'opacity-0'
                                    "
                                />
                                Show all
                            </button>
                            <button
                                v-for="tag in allTouchpoints"
                                :key="tag"
                                class="flex w-full items-center px-5 py-3 text-left font-avenir text-[15px] tracking-[-0.45px] transition-colors hover:bg-white/5"
                                :class="
                                    activeFilter === tag
                                        ? 'text-white'
                                        : 'text-white/60'
                                "
                                @click="selectFilter(tag)"
                            >
                                <span
                                    class="mr-3 flex h-[6px] w-[6px] shrink-0 items-center justify-center rounded-full bg-[#ffc700]"
                                    :class="
                                        activeFilter === tag
                                            ? 'opacity-100'
                                            : 'opacity-0'
                                    "
                                />
                                {{ tag }}
                            </button>
                        </div>
                    </Transition>
                </div>
            </div>
        </section>

        <!-- ============ CASES GRID ============ -->
        <section
            class="flex flex-col gap-6 px-5 pb-[100px] md:gap-[7.3vw] md:px-[7vw] md:pb-[8vw]"
        >
            <div
                v-for="(row, rowIndex) in rows"
                :key="rowIndex"
                class="flex flex-col items-center gap-6 md:flex-row md:gap-[1.4vw]"
            >
                <!-- LEFT CARD -->
                <div :class="leftColClass(rowIndex)">
                    <!-- Editorial card -->
                    <template v-if="row[0].type === 'editorial'">
                        <div
                            class="w-full rounded-[16px] bg-[#ffc700] p-6 text-black md:ml-[9%] md:w-[76%] md:p-[clamp(28px,3.2vw,52px)]"
                            style="transform: rotate(-0.5deg)"
                        >
                            <p
                                class="mb-6 font-avenir text-[18px] leading-[27px] tracking-[-0.03em] md:mb-8 md:text-[22px] md:leading-[34px]"
                            >
                                We love a great story. It's been the recipe for
                                human engagement since the beginning of time.
                                Stories engage, simplify and spread. We use that
                                power for your brand.
                            </p>
                            <div class="flex flex-wrap gap-3">
                                <a
                                    href="/"
                                    class="flex h-[48px] items-center rounded-[30px] border border-black px-5 font-avenir text-[14px] tracking-[-0.01em] transition-colors hover:bg-black hover:text-[#ffc700] md:h-[52px] md:text-[15px]"
                                >
                                    Credible Creativity
                                </a>
                                <a
                                    href="/contact"
                                    class="flex h-[48px] items-center gap-3 rounded-[30px] bg-black pr-6 pl-2 font-avenir text-[14px] tracking-[-0.01em] text-white transition-colors hover:bg-black/80 md:h-[52px] md:text-[15px]"
                                >
                                    <img
                                        :src="CONTACT_AVATAR"
                                        alt=""
                                        class="size-[34px] rounded-full object-cover"
                                    />
                                    Get in touch
                                </a>
                            </div>
                        </div>
                    </template>

                    <!-- Case card -->
                    <CaseCard
                        v-else-if="row[0].type === 'case'"
                        :case-item="row[0].data"
                        :rotation="cardRotation(rowIndex * 2)"
                        :width-class="leftCardClass(rowIndex)"
                        :aspect-class="leftAspect(rowIndex)"
                    />
                </div>

                <!-- RIGHT CARD -->
                <div v-if="row[1]" :class="rightColClass(rowIndex)">
                    <!-- Editorial card (right slot, unlikely but safe) -->
                    <template v-if="row[1].type === 'editorial'">
                        <div
                            class="rounded-[16px] bg-[#ffc700] p-10 text-black"
                        >
                            <p
                                class="mb-6 font-avenir text-[22px] leading-[34px] tracking-[-0.66px]"
                            >
                                We love a great story.
                            </p>
                        </div>
                    </template>

                    <!-- Case card -->
                    <CaseCard
                        v-else-if="row[1].type === 'case'"
                        :case-item="row[1].data"
                        :rotation="cardRotation(rowIndex * 2 + 1)"
                        :width-class="rightCardClass(rowIndex)"
                        :aspect-class="rightAspect(rowIndex)"
                    />
                </div>
            </div>
        </section>

        <!-- Figma: zwevende "Tell us about your project"-pill rechtsonder -->
        <FloatingPill
            label="Tell us about your project"
            href="/contact"
            :avatars="[CONTACT_AVATAR]"
        />

        <SiteFooter id="contact" />
    </div>
</template>
