<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { computed, onMounted, onUnmounted, ref } from 'vue'
import SiteFooter from '@/components/SiteFooter.vue'
import SiteHeader from '@/components/SiteHeader.vue'

type CaseItem = {
    name: string
    slug: string
    photo: string | null
    video: string | null
    autoplayVideo: boolean
    touchpoints: string[]
}

type RowItem = { type: 'case'; data: CaseItem } | { type: 'editorial' }

const props = defineProps<{
    cases: CaseItem[]
    allTouchpoints: string[]
}>()

const activeFilter = ref<string | null>(null)
const dropdownOpen = ref(false)

function selectFilter(value: string | null) {
    activeFilter.value = value
    dropdownOpen.value = false
}

const dropdownLabel = computed(() =>
    activeFilter.value ?? 'Show all',
)

function onClickOutside(e: MouseEvent) {
    const el = document.querySelector('[data-filter-dropdown]')
    if (el && !el.contains(e.target as Node)) dropdownOpen.value = false
}

onMounted(() => document.addEventListener('mousedown', onClickOutside))
onUnmounted(() => document.removeEventListener('mousedown', onClickOutside))

const filteredCases = computed(() =>
    activeFilter.value
        ? props.cases.filter((c) => c.touchpoints.includes(activeFilter.value!))
        : props.cases,
)

// Build flat item list with editorial card injected after 4th case
const items = computed<RowItem[]>(() => {
    const result: RowItem[] = []
    filteredCases.value.forEach((c, i) => {
        if (i === 4) result.push({ type: 'editorial' })
        result.push({ type: 'case', data: c })
    })
    return result
})

// Pair items into rows of 2
const rows = computed<[RowItem, RowItem | null][]>(() => {
    const pairs: [RowItem, RowItem | null][] = []
    for (let i = 0; i < items.value.length; i += 2) {
        pairs.push([items.value[i], items.value[i + 1] ?? null])
    }
    return pairs
})

// Rotation values for organic feel
const rotations = ['-0.7deg', '0.08deg', '0.22deg', '-0.48deg', '0.13deg', '0.1deg', '0.14deg', '-0.54deg']
function cardRotation(seed: number): string {
    return rotations[seed % rotations.length]
}

function isVideo(url: string | null | undefined): boolean {
    return !!url && /\.(mp4|webm|ogg|mov|m3u8)(\?.*)?$/i.test(url)
}

// Both columns are equal (50/50 split at page center).
// The big card fills its column fully; the small card is ~78% wide, floated to the outer edge.
function leftColClass(rowIndex: number): string {
    // even rows: left=big → full width; odd rows: left=small → 78%, left-aligned (outer edge)
    return rowIndex % 2 === 0 ? 'w-[calc(50%-10px)] flex-none' : 'w-[calc(50%-10px)] flex-none flex justify-start'
}
function rightColClass(rowIndex: number): string {
    // even rows: right=small → 78%, right-aligned (outer edge); odd rows: right=big → full width
    return rowIndex % 2 === 0 ? 'w-[calc(50%-10px)] flex-none flex justify-end' : 'w-[calc(50%-10px)] flex-none'
}
function leftCardClass(rowIndex: number): string {
    return rowIndex % 2 === 0 ? 'w-full' : 'w-[78%]'
}
function rightCardClass(rowIndex: number): string {
    return rowIndex % 2 === 0 ? 'w-[78%]' : 'w-full'
}
</script>

<template>
    <Head>
        <title>Cases — Lemon Scented Tea</title>
    </Head>

    <div class="min-h-screen bg-black text-white">
        <SiteHeader hide-top-gradient />

        <!-- ============ HERO TITLE ============ -->
        <section class="pt-[180px] pb-[52px] text-center lg:pt-[220px]">
            <h1
                class="inline text-[80px] font-black italic leading-none tracking-[-3px] lg:text-[130px] lg:tracking-[-4.5px]"
                style="font-family: 'Avenir', system-ui, sans-serif;"
            >
                <span class="text-[#ffc700]">Lemon </span>
                <span class="text-white">work</span>
            </h1>
        </section>

        <!-- ============ FILTER BAR ============ -->
        <section class="flex justify-center px-[59px] pb-[52px]">
            <div class="flex items-center gap-3">
                <span
                    class="text-[16px] tracking-[-0.48px] text-white/50"
                    style="font-family: 'Avenir', system-ui, sans-serif;"
                >
                    Filter
                </span>

                <!-- Dropdown trigger -->
                <div class="relative" data-filter-dropdown @keydown.escape="dropdownOpen = false">
                    <button
                        class="flex h-[50px] min-w-[140px] items-center justify-between gap-3 rounded-[30px] border border-white/30 px-6 text-[16px] tracking-[-0.48px] text-white transition-colors hover:border-white/50"
                        style="font-family: 'Avenir', system-ui, sans-serif;"
                        @click="dropdownOpen = !dropdownOpen"
                    >
                        {{ dropdownLabel }}
                        <svg
                            class="h-[9px] w-[11px] shrink-0 opacity-60 transition-transform duration-200"
                            :class="dropdownOpen ? 'rotate-180' : ''"
                            viewBox="0 0 11 9" fill="none"
                        >
                            <path d="M5.5 9L0 0h11L5.5 9Z" fill="currentColor" />
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
                                class="flex w-full items-center px-5 py-3 text-left text-[15px] tracking-[-0.45px] transition-colors hover:bg-white/5"
                                :class="activeFilter === null ? 'text-white' : 'text-white/60'"
                                style="font-family: 'Avenir', system-ui, sans-serif;"
                                @click="selectFilter(null)"
                            >
                                <span class="mr-3 flex h-[6px] w-[6px] shrink-0 items-center justify-center rounded-full bg-[#ffc700]" :class="activeFilter === null ? 'opacity-100' : 'opacity-0'" />
                                Show all
                            </button>
                            <button
                                v-for="tag in allTouchpoints"
                                :key="tag"
                                class="flex w-full items-center px-5 py-3 text-left text-[15px] tracking-[-0.45px] transition-colors hover:bg-white/5"
                                :class="activeFilter === tag ? 'text-white' : 'text-white/60'"
                                style="font-family: 'Avenir', system-ui, sans-serif;"
                                @click="selectFilter(tag)"
                            >
                                <span class="mr-3 flex h-[6px] w-[6px] shrink-0 items-center justify-center rounded-full bg-[#ffc700]" :class="activeFilter === tag ? 'opacity-100' : 'opacity-0'" />
                                {{ tag }}
                            </button>
                        </div>
                    </Transition>
                </div>
            </div>
        </section>

        <!-- ============ CASES GRID ============ -->
        <section class="flex flex-col gap-32 px-[59px] pb-[100px]">
            <div
                v-for="(row, rowIndex) in rows"
                :key="rowIndex"
                class="flex items-center gap-5"
            >
                <!-- LEFT CARD -->
                <div :class="leftColClass(rowIndex)">
                    <!-- Editorial card -->
                    <template v-if="row[0].type === 'editorial'">
                        <div class="rounded-[16px] bg-[#ffc700] p-10 text-black">
                            <p
                                class="mb-8 text-[22px] leading-[34px] tracking-[-0.66px]"
                                style="font-family: 'Avenir', system-ui, sans-serif;"
                            >
                                We love a great story. It's been the recipe for human engagement since the beginning of time. Stories engage, simplify and spread. We use that power for your brand.
                            </p>
                            <div class="flex flex-wrap gap-3">
                                <a
                                    href="/about"
                                    class="flex h-[52px] items-center rounded-[30px] border border-black px-5 text-[15px] tracking-[-0.45px] transition-colors hover:bg-black hover:text-[#ffc700]"
                                    style="font-family: 'Avenir', system-ui, sans-serif;"
                                >
                                    Credible Creativity
                                </a>
                                <a
                                    href="#contact"
                                    class="flex h-[52px] items-center gap-2 rounded-[30px] bg-black px-4 text-[15px] tracking-[-0.45px] text-white transition-colors hover:bg-black/80"
                                    style="font-family: 'Avenir', system-ui, sans-serif;"
                                >
                                    <span class="flex h-[34px] w-[34px] items-center justify-center overflow-hidden rounded-full bg-[#ffc700]">
                                        <svg class="h-[18px] w-[18px] text-black" viewBox="0 0 20 20" fill="currentColor">
                                            <circle cx="10" cy="7" r="3.5" />
                                            <path d="M3 18c0-3.9 3.1-7 7-7s7 3.1 7 7" />
                                        </svg>
                                    </span>
                                    Get in touch
                                </a>
                            </div>
                        </div>
                    </template>

                    <!-- Case card -->
                    <template v-else>
                        <div
                            :class="leftCardClass(rowIndex)"
                            :style="{ transform: `rotate(${cardRotation(rowIndex * 2)})` }"
                        >
                            <Link
                                :href="`/cases/${row[0].data.slug}`"
                                class="group relative block overflow-hidden rounded-[16px]"
                                @mouseenter="e => { if (!row[0].data.autoplayVideo) (e.currentTarget as HTMLElement).querySelector('video')?.play() }"
                                @mouseleave="e => { if (!row[0].data.autoplayVideo) { const v = (e.currentTarget as HTMLElement).querySelector('video'); if (v) { v.pause(); v.currentTime = 0 } } }"
                            >
                                <div class="relative aspect-[4/3] bg-neutral-900">
                                    <img
                                        v-if="row[0].data.photo && !row[0].data.autoplayVideo"
                                        :src="row[0].data.photo"
                                        :alt="row[0].data.name"
                                        class="absolute inset-0 h-full w-full object-cover transition-transform duration-700 group-hover:scale-[1.03]"
                                    />
                                    <video
                                        v-if="row[0].data.video"
                                        :src="row[0].data.video"
                                        :autoplay="row[0].data.autoplayVideo"
                                        muted
                                        loop
                                        playsinline
                                        :preload="row[0].data.autoplayVideo ? 'auto' : 'none'"
                                        class="absolute inset-0 h-full w-full object-cover transition-transform duration-700 group-hover:scale-[1.03]"
                                        :class="row[0].data.autoplayVideo ? '' : 'opacity-0 group-hover:opacity-100 transition-opacity duration-300'"
                                    />
                                </div>
                                <div class="absolute inset-0 bg-gradient-to-b from-black/35 via-transparent to-transparent pointer-events-none" />
                                <p
                                    class="absolute left-[20px] top-[20px] text-[26px] leading-[28px] tracking-[-0.8px] text-[#ffc700]"
                                    style="font-family: 'Avenir', system-ui, sans-serif; font-weight: 900; font-style: oblique;"
                                >
                                    {{ row[0].data.name }}
                                </p>
                            </Link>
                        </div>
                    </template>
                </div>

                <!-- RIGHT CARD -->
                <div v-if="row[1]" :class="rightColClass(rowIndex)">
                    <!-- Editorial card (right slot, unlikely but safe) -->
                    <template v-if="row[1].type === 'editorial'">
                        <div class="rounded-[16px] bg-[#ffc700] p-10 text-black">
                            <p class="mb-6 text-[22px] leading-[34px] tracking-[-0.66px]" style="font-family: 'Avenir', system-ui, sans-serif;">
                                We love a great story.
                            </p>
                        </div>
                    </template>

                    <!-- Case card -->
                    <template v-else>
                        <div
                            :class="rightCardClass(rowIndex)"
                            :style="{ transform: `rotate(${cardRotation(rowIndex * 2 + 1)})` }"
                        >
                            <Link
                                :href="`/cases/${row[1].data.slug}`"
                                class="group relative block overflow-hidden rounded-[16px]"
                                @mouseenter="e => { if (!row[1].data.autoplayVideo) (e.currentTarget as HTMLElement).querySelector('video')?.play() }"
                                @mouseleave="e => { if (!row[1].data.autoplayVideo) { const v = (e.currentTarget as HTMLElement).querySelector('video'); if (v) { v.pause(); v.currentTime = 0 } } }"
                            >
                                <div class="relative aspect-[4/3] bg-neutral-900">
                                    <img
                                        v-if="row[1].data.photo && !row[1].data.autoplayVideo"
                                        :src="row[1].data.photo"
                                        :alt="row[1].data.name"
                                        class="absolute inset-0 h-full w-full object-cover transition-transform duration-700 group-hover:scale-[1.03]"
                                    />
                                    <video
                                        v-if="row[1].data.video"
                                        :src="row[1].data.video"
                                        :autoplay="row[1].data.autoplayVideo"
                                        muted
                                        loop
                                        playsinline
                                        :preload="row[1].data.autoplayVideo ? 'auto' : 'none'"
                                        class="absolute inset-0 h-full w-full object-cover transition-transform duration-700 group-hover:scale-[1.03]"
                                        :class="row[1].data.autoplayVideo ? '' : 'opacity-0 group-hover:opacity-100 transition-opacity duration-300'"
                                    />
                                </div>
                                <div class="absolute inset-0 bg-gradient-to-b from-black/35 via-transparent to-transparent pointer-events-none" />
                                <p
                                    class="absolute left-[20px] top-[20px] text-[26px] leading-[28px] tracking-[-0.8px] text-[#ffc700]"
                                    style="font-family: 'Avenir', system-ui, sans-serif; font-weight: 900; font-style: oblique;"
                                >
                                    {{ row[1].data.name }}
                                </p>
                            </Link>
                        </div>
                    </template>
                </div>
            </div>
        </section>

        <!-- ============ CTA ============ -->
        <section class="flex justify-center pb-[120px]">
            <a
                href="#contact"
                class="group flex h-[73px] items-center gap-4 rounded-[73px] border border-white/5 bg-black/70 pl-[22px] pr-[22px] backdrop-blur-sm transition-opacity hover:opacity-80"
            >
                <div class="flex h-[43px] w-[44px] items-center justify-center overflow-hidden rounded-full bg-[#ffc700]">
                    <svg class="h-5 w-5 text-black" viewBox="0 0 20 20" fill="currentColor">
                        <circle cx="10" cy="7" r="3.5" />
                        <path d="M3 18c0-3.9 3.1-7 7-7s7 3.1 7 7" />
                    </svg>
                </div>
                <span
                    class="text-[18px] tracking-[-0.18px] text-white"
                    style="font-family: 'Avenir', system-ui, sans-serif;"
                >
                    Tell us about your project
                </span>
                <div class="flex h-[43px] w-[57px] items-center justify-center rounded-[69px] bg-[#ffc700]">
                    <svg class="h-[16px] w-[16px] -rotate-45 text-black" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 8h12M8 2l6 6-6 6" />
                    </svg>
                </div>
            </a>
        </section>

        <SiteFooter id="contact" />
    </div>
</template>
