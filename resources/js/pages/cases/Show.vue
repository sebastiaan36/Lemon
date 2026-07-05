<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { computed, onMounted, onUnmounted, ref } from 'vue'
import CasesCarousel from '@/components/CasesCarousel.vue'
import HlsVideo from '@/components/HlsVideo.vue'
import SiteFooter from '@/components/SiteFooter.vue'
import SiteHeader from '@/components/SiteHeader.vue'

const CASES_ARROW_URL =
    '/figma-assets/d80e54ef-48d6-43a4-8629-0801cfe41603.svg'

type VisualItem = {
    image: string | null
}

type GalleryItem = {
    url: string
    type: 'image' | 'video'
    width: number | null
    height: number | null
}

type ResultStat = {
    prefix?: string | null
    value: string
    label: string
}

type RelatedCase = {
    name: string
    slug: string
    photo: string | null
    video: string | null
}

const props = defineProps<{
    seoTitle?: string | null
    metaDescription?: string | null
    caseStudy: {
        name: string
        slug: string
        clientName?: string | null
        accentColor?: string | null
        styleVariant?: number | null
        heroTitle?: string | null
        heroTitleLines?: string[]
        heroSubtitle?: string | null
        heroMedia?: string | null
        heroDuration?: string | null
        introLogo?: string | null
        touchpoints?: string[]
        overviewTitle?: string | null
        overviewBody?: string | null
        galleryItems?: GalleryItem[]
        highlightTitle?: string | null
        highlightBody?: string | null
        highlightMedia?: string | null
        highlightButtonText?: string | null
        campaignTitle?: string | null
        campaignBody?: string | null
        campaignMedia?: string | null
        storyTitle?: string | null
        storyBody?: string | null
        storyMedia?: string | null
        preCalloutTitle?: string | null
        preCalloutBody?: string | null
        preCalloutGalleryItems?: GalleryItem[]
        storyImages?: VisualItem[]
        calloutTitle?: string | null
        secondaryStoryMedia?: string | null
        secondaryStoryBody?: string | null
        secondaryStoryButtonText?: string | null
        resultsHeading?: string | null
        resultsStats?: ResultStat[]
        optionalPanelTitle?: string | null
        optionalPanelBody?: string | null
        optionalPanelButtonText?: string | null
    }
    moreCases?: RelatedCase[]
}>()

function isVideo(url: string | null | undefined): boolean {
    return !!url && /\.(mp4|webm|ogg|mov|m3u8)(\?.*)?$/i.test(url)
}

// Vaste kleurstellingen per opmaak-optie (gekozen in Filament).
// pageBg/pageText sturen de pagina-achtergrond en bodytekst; kleurstelling 3 is een donker thema.
type StylePalette = {
    accent: string
    nav: string
    highlightText: string
    pageBg: string
    pageText: string
    divider: string
}

const stylePalettes: Record<number, StylePalette> = {
    1: { accent: '#0A7949', nav: '#0A7949', highlightText: '#ffffff', pageBg: '#fcfcfc', pageText: '#101010', divider: 'rgba(0,0,0,0.1)' },
    2: { accent: '#F6C000', nav: '#0c0c0c', highlightText: '#101010', pageBg: '#fcfcfc', pageText: '#101010', divider: 'rgba(0,0,0,0.1)' },
    3: { accent: '#F6C000', nav: '#ffffff', highlightText: '#101010', pageBg: '#000000', pageText: '#ffffff', divider: 'rgba(255,255,255,0.1)' },
}

const palette = stylePalettes[props.caseStudy.styleVariant ?? 1] ?? stylePalettes[1]
const accentColor = palette.accent
const isDarkTheme = (props.caseStudy.styleVariant ?? 1) === 3
const siteItems = [
    { label: 'About', href: '/#about' },
    { label: 'CxByEx', href: '/#services' },
    { label: 'Cases', href: '/cases' },
    { label: 'Industries', href: '/#services' },
    { label: 'Contact', href: '/#contact' },
]

const heroVideoRef = ref<InstanceType<typeof HlsVideo> | null>(null)
const heroPlaying = ref(false)

function playHeroVideo(): void {
    const el = heroVideoRef.value?.videoEl
    if (!el) return
    void el.play()
    heroPlaying.value = true
}

const isLargeScreen = ref(false)
const viewportWidth = ref(0)
const galleryItemSizes = [
    { width: 448, height: 492 },
    { width: 448, height: 526 },
    { width: 448, height: 428 },
]

function updateScreenSize() {
    if (typeof window === 'undefined') return
    viewportWidth.value = window.innerWidth
    isLargeScreen.value = window.innerWidth >= 1024
}

onMounted(() => {
    updateScreenSize()
    window.addEventListener('resize', updateScreenSize)
})

onUnmounted(() => {
    if (typeof window === 'undefined') return
    window.removeEventListener('resize', updateScreenSize)
})

const galleryRef = ref<HTMLElement | null>(null)
const galleryIndex = ref(0)
const galleryCount = (props.caseStudy.galleryItems || []).length
const heroTitleLines = computed(() => {
    const lines = (props.caseStudy.heroTitleLines || []).filter((line) => line.trim().length > 0)

    return lines.length ? lines : [props.caseStudy.heroTitle || props.caseStudy.name]
})
const preCalloutIndex = ref(0)
const preCalloutGalleryItems = computed(() => props.caseStudy.preCalloutGalleryItems || [])
const preCalloutGalleryCount = computed(() => preCalloutGalleryItems.value.length)
const preCalloutGalleryStyle = computed(() => {
    const cardWidth = isLargeScreen.value
        ? 560
        : Math.min(560, (viewportWidth.value || 390) * 0.72)
    const gap = 24
    const offset = preCalloutIndex.value * (cardWidth + gap)

    return { transform: `translateX(-${offset}px)` }
})
const hasPreCalloutBlock = computed(
    () =>
        !!props.caseStudy.preCalloutTitle ||
        !!props.caseStudy.preCalloutBody ||
        preCalloutGalleryCount.value > 0,
)

function scrollPreCalloutGallery(direction: 1 | -1) {
    if (preCalloutGalleryCount.value === 0) return

    preCalloutIndex.value = Math.max(
        0,
        Math.min(preCalloutGalleryCount.value - 1, preCalloutIndex.value + direction),
    )
}

function galleryItemStyle(index: number) {
    if (!isLargeScreen.value) {
        return {
            width: 'min(82vw, 360px)',
            height: '360px',
        }
    }

    const size = galleryItemSizes[index % galleryItemSizes.length]

    return {
        width: `${size.width}px`,
        height: `${size.height}px`,
    }
}

function gallerySpacerWidth(): number {
    const container = galleryRef.value
    if (!container) return 0
    const spacer = container.querySelector<HTMLElement>('[data-gallery-spacer]')
    return spacer?.offsetWidth ?? 0
}

function scrollGallery(direction: 1 | -1) {
    const container = galleryRef.value
    if (!container) return
    const items = Array.from(container.querySelectorAll<HTMLElement>('[data-gallery-item]'))
    if (items.length === 0) return

    const next = Math.max(0, Math.min(items.length - 1, galleryIndex.value + direction))
    if (next === galleryIndex.value) return

    galleryIndex.value = next
    const target = items[next]
    const offset = gallerySpacerWidth()
    container.scrollTo({
        left: target.offsetLeft - container.offsetLeft - offset,
        behavior: 'smooth',
    })
}

function onGalleryScroll() {
    const container = galleryRef.value
    if (!container) return
    const items = Array.from(container.querySelectorAll<HTMLElement>('[data-gallery-item]'))
    const offset = gallerySpacerWidth()
    const scrollLeft = container.scrollLeft + container.offsetLeft + offset
    let closest = 0
    let minDelta = Number.POSITIVE_INFINITY
    items.forEach((el, i) => {
        const delta = Math.abs(el.offsetLeft - scrollLeft)
        if (delta < minDelta) {
            minDelta = delta
            closest = i
        }
    })
    galleryIndex.value = closest
}

function pad2(n: number): string {
    return n.toString().padStart(2, '0')
}
</script>

<template>
    <Head :title="seoTitle ?? caseStudy.name">
        <meta v-if="metaDescription" head-key="description" name="description" :content="metaDescription" />
    </Head>

    <div :style="{ backgroundColor: palette.pageBg, color: palette.pageText }">
        <SiteHeader
            :nav-color="palette.nav"
            contact-href="/#contact"
            :menu-items="siteItems"
            hide-top-gradient
            avoid-matching-cta-background
        />

        <main>
            <!-- ============ HERO ============ -->
            <section data-section="hero" class="px-[33px] pt-[140px] pb-[24px] lg:px-[59px]">
                <p
                    class="mb-4 text-center text-[18px] leading-[1.1] tracking-[-0.18px] lg:text-[30px] lg:tracking-[-0.3px]"
                    style="font-family: 'Avenir', sans-serif;"
                >
                    {{ caseStudy.heroSubtitle || caseStudy.clientName }}
                </p>
                <h1
                    class="mx-auto mb-12 max-w-[340px] text-center text-[64px] leading-[60px] tracking-[-1.92px] lg:max-w-[700px] lg:text-[137px] lg:leading-[0.9] lg:tracking-[-4.11px]"
                    :style="{ color: accentColor, fontFamily: '\'Avenir\', sans-serif', fontWeight: '900', fontStyle: 'oblique' }"
                >
                    <span v-for="line in heroTitleLines" :key="line" class="block">
                        {{ line }}
                    </span>
                </h1>

                <div class="relative mx-auto max-w-[1529px] overflow-hidden rounded-[20px] bg-neutral-200">
                    <template v-if="caseStudy.heroMedia && isVideo(caseStudy.heroMedia)">
                        <HlsVideo
                            ref="heroVideoRef"
                            :src="caseStudy.heroMedia"
                            loop
                            playsinline
                            :controls="heroPlaying"
                            class="aspect-[1529/901] w-full object-cover"
                        />
                        <button
                            v-if="!heroPlaying"
                            type="button"
                            aria-label="Play video"
                            class="absolute inset-0 flex items-center justify-center"
                            @click="playHeroVideo"
                        >
                            <span class="flex h-[52px] w-[52px] items-center justify-center rounded-full bg-lst-yellow transition-transform hover:scale-105">
                                <svg width="16" height="18" viewBox="0 0 16 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M15 7.27a2 2 0 0 1 0 3.46L3 17.66A2 2 0 0 1 0 15.93V2.07A2 2 0 0 1 3 .34l12 6.93Z" fill="#000" />
                                </svg>
                            </span>
                        </button>
                    </template>
                    <img
                        v-else-if="caseStudy.heroMedia"
                        :src="caseStudy.heroMedia"
                        :alt="caseStudy.name"
                        class="aspect-[1529/901] w-full object-cover"
                    />
                    <div v-else class="aspect-[1529/901] w-full bg-neutral-200" />

                    <span
                        v-if="caseStudy.heroDuration && !heroPlaying"
                        class="pointer-events-none absolute right-6 bottom-6 text-[15px] tracking-[-0.47px] text-white"
                        style="font-family: 'Avenir', sans-serif;"
                    >
                        {{ caseStudy.heroDuration }}
                    </span>
                </div>
            </section>

            <!-- ============ META (client + touchpoints) ============ -->
            <section data-section="meta" class="px-[33px] pb-[24px] lg:px-[59px]">
                <!-- Mobile: logo links + verticale stack van Client/Touchpoints rechts -->
                <div class="flex items-start gap-8 lg:hidden">
                    <img
                        v-if="caseStudy.introLogo"
                        :src="caseStudy.introLogo"
                        alt=""
                        class="h-[44px] w-auto shrink-0 object-contain"
                    />
                    <div class="flex flex-1 flex-col gap-6">
                        <div>
                            <p class="text-[12px] leading-[22px]" style="font-family: 'Avenir', sans-serif; font-weight: 300;">
                                Client
                            </p>
                            <p class="text-[18px] leading-[22px] tracking-[-0.54px]" style="font-family: 'Avenir', sans-serif;">
                                {{ caseStudy.clientName }}
                            </p>
                        </div>
                        <div>
                            <p class="text-[12px] leading-[22px]" style="font-family: 'Avenir', sans-serif; font-weight: 300;">
                                Touchpoints
                            </p>
                            <p class="text-[18px] leading-[22px] tracking-[-0.54px]" style="font-family: 'Avenir', sans-serif;">
                                {{ (caseStudy.touchpoints || []).join(', ') }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Desktop: 3-koloms grid (logo+client links, lege spacer, touchpoints rechts) -->
                <div class="hidden lg:grid lg:grid-cols-[400px_1fr_281px] lg:items-center lg:gap-10">
                    <div class="flex items-center gap-10">
                        <img
                            v-if="caseStudy.introLogo"
                            :src="caseStudy.introLogo"
                            alt=""
                            class="h-[44px] w-auto shrink-0 object-contain"
                        />
                        <div class="min-w-0">
                            <p class="text-[12px] leading-[22px]" style="font-family: 'Avenir', sans-serif; font-weight: 300;">
                                Client
                            </p>
                            <p class="whitespace-nowrap text-[18px] leading-[22px] tracking-[-0.54px]" style="font-family: 'Avenir', sans-serif;">
                                {{ caseStudy.clientName }}
                            </p>
                        </div>
                    </div>
                    <div />
                    <div class="text-right">
                        <p class="text-[12px] leading-[22px]" style="font-family: 'Avenir', sans-serif; font-weight: 300;">
                            Touchpoints
                        </p>
                        <p class="text-[18px] leading-[22px] tracking-[-0.54px]" style="font-family: 'Avenir', sans-serif;">
                            {{ (caseStudy.touchpoints || []).join(', ') }}
                        </p>
                    </div>
                </div>
            </section>

            <!-- ============ OVERVIEW (intro quote + body) ============ -->
            <section data-section="overview" class="border-t border-black/15 px-[33px] pt-[60px] pb-[80px] lg:px-[5%] lg:pt-[118px] lg:pb-[200px]">
                <div class="flex flex-col items-center gap-10 text-center lg:grid lg:grid-cols-[minmax(0,555fr)_minmax(0,638fr)] lg:items-start lg:gap-10 lg:text-left">
                    <h2
                        class="max-w-[413px] text-[40px] leading-[44px] tracking-[-1.2px] lg:max-w-[555px] lg:text-[60px] lg:leading-[70px] lg:tracking-[-1.8px]"
                        :style="{ color: accentColor, fontFamily: '\'Avenir\', sans-serif' }"
                    >
                        {{ caseStudy.overviewTitle }}
                    </h2>
                    <p
                        class="max-w-[407px] text-[18px] leading-[28px] tracking-[-0.54px] lg:mt-[265px] lg:max-w-[638px] lg:text-[28px] lg:leading-[38px] lg:tracking-[-0.84px]"
                        style="font-family: 'Avenir', sans-serif;"
                    >
                        {{ caseStudy.overviewBody }}
                    </p>
                </div>
            </section>

            <!-- ============ GALLERY (horizontal slider met afbeeldingen + videos in eigen aspect ratio) ============ -->
            <section v-if="(caseStudy.galleryItems || []).length" data-section="gallery" class="py-[60px] lg:py-[90px]">
                <div
                    ref="galleryRef"
                    class="flex snap-x snap-mandatory overflow-x-auto pb-4 scroll-pl-[33px] lg:scroll-pl-[5%]"
                    style="scrollbar-width: none; -ms-overflow-style: none;"
                    @scroll.passive="onGalleryScroll"
                >
                    <div data-gallery-spacer class="w-[33px] shrink-0 lg:w-[5%]" aria-hidden="true" />

                    <div class="flex shrink-0 gap-4 lg:gap-8">
                        <div
                            v-for="(item, index) in caseStudy.galleryItems"
                            :key="index"
                            data-gallery-item
                            class="shrink-0 snap-start overflow-hidden rounded-[20px] bg-neutral-200"
                            :style="galleryItemStyle(index)"
                        >
                            <HlsVideo
                                v-if="item.type === 'video'"
                                :src="item.url"
                                autoplay
                                loop
                                muted
                                playsinline
                                class="block h-full w-full object-cover"
                            />
                            <img
                                v-else
                                :src="item.url"
                                alt=""
                                :width="item.width ?? undefined"
                                :height="item.height ?? undefined"
                                class="block h-full w-full object-cover"
                            />
                        </div>
                    </div>

                    <div class="w-[33px] shrink-0 lg:w-[5%]" aria-hidden="true" />
                </div>

                <div class="mt-8 flex items-center justify-between px-[33px] lg:mt-10 lg:px-[5%]">
                    <p
                        class="flex items-center gap-3 text-[16px] tracking-[-0.48px]"
                        :style="{ color: accentColor, fontFamily: '\'Avenir\', sans-serif' }"
                    >
                        <span>{{ pad2(galleryIndex + 1) }}</span>
                        <span class="inline-block h-px w-6" :style="{ backgroundColor: accentColor }" />
                        <span>{{ pad2(galleryCount) }}</span>
                    </p>

                    <div class="flex items-center gap-4">
                        <button
                            type="button"
                            class="flex h-[42px] w-[42px] items-center justify-center rounded-full border transition disabled:opacity-30 lg:h-[46px] lg:w-[46px]"
                            :style="{ borderColor: accentColor, color: accentColor }"
                            :disabled="galleryIndex === 0"
                            aria-label="Vorige"
                            @click="scrollGallery(-1)"
                        >
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <path d="M16 10H4M4 10l5-5M4 10l5 5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                        <button
                            type="button"
                            class="flex h-[42px] w-[42px] items-center justify-center rounded-full border transition disabled:opacity-30 lg:h-[46px] lg:w-[46px]"
                            :style="{ borderColor: accentColor, color: accentColor }"
                            :disabled="galleryIndex >= galleryCount - 1"
                            aria-label="Volgende"
                            @click="scrollGallery(1)"
                        >
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <path d="M4 10h12M16 10l-5-5M16 10l-5 5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                    </div>
                </div>
            </section>

            <!-- ============ HIGHLIGHT (green block, "Nature has to shout to be heard") ============ -->
            <section data-section="highlight">
                <div
                    class="px-[33px] py-[80px] lg:px-[59px] lg:py-[120px]"
                    :style="{ backgroundColor: accentColor }"
                >
                    <h2
                        class="mx-auto max-w-[413px] text-center text-[64px] leading-[60px] tracking-[-1.92px] lg:max-w-[800px] lg:text-[140px] lg:leading-[130px] lg:tracking-[-4.2px]"
                        :style="{ color: palette.highlightText, fontFamily: '\'Avenir\', sans-serif', fontWeight: '900', fontStyle: 'oblique' }"
                    >
                        {{ caseStudy.highlightTitle }}
                    </h2>
                    <p
                        class="mx-auto mt-8 max-w-[449px] text-center text-[18px] leading-[28px] tracking-[-0.54px] lg:mt-10 lg:max-w-[765px] lg:text-[22px] lg:leading-[32px] lg:tracking-[-0.66px]"
                        :style="{ color: palette.highlightText, fontFamily: '\'Avenir\', sans-serif' }"
                    >
                        {{ caseStudy.highlightBody }}
                    </p>
                    <div v-if="caseStudy.highlightButtonText" class="mt-8 flex justify-center lg:mt-10">
                        <a
                            href="#contact"
                            class="rounded-[30px] border px-8 py-3 text-[18px] tracking-[-0.54px] lg:text-[20px] lg:tracking-[-0.6px]"
                            :style="{ color: palette.highlightText, borderColor: palette.highlightText, fontFamily: '\'Avenir\', sans-serif' }"
                        >
                            {{ caseStudy.highlightButtonText }}
                        </a>
                    </div>
                </div>
            </section>

            <!-- ============ CAMPAIGN (full-width image with overlay text) ============ -->
            <section v-if="caseStudy.campaignMedia" data-section="campaign" class="relative">
                <div class="relative h-[600px] overflow-hidden bg-black lg:h-[994px]">
                    <img
                        v-if="!isVideo(caseStudy.campaignMedia)"
                        :src="caseStudy.campaignMedia"
                        alt=""
                        class="h-full w-full object-cover"
                    />
                    <HlsVideo
                        v-else
                        :src="caseStudy.campaignMedia"
                        autoplay
                        loop
                        muted
                        playsinline
                        class="h-full w-full object-cover"
                    />
                    <div class="absolute inset-0 bg-gradient-to-b from-transparent via-transparent to-black/60 lg:bg-gradient-to-r lg:from-transparent lg:via-transparent lg:to-black/35" />
                    <div class="absolute inset-x-[33px] bottom-[40px] mx-auto max-w-[437px] text-center text-white lg:inset-x-auto lg:right-[211px] lg:bottom-[278px] lg:mx-0 lg:w-[437px] lg:max-w-none lg:text-left">
                        <h3 class="text-[28px] leading-[34px] tracking-[-0.84px] lg:text-[32px] lg:leading-[38px] lg:tracking-[-0.96px]" style="font-family: 'Avenir', sans-serif;">
                            {{ caseStudy.campaignTitle }}
                        </h3>
                        <p class="mt-4 text-[16px] leading-[24px] tracking-[-0.48px] lg:mt-6 lg:text-[18px] lg:leading-[28px] lg:tracking-[-0.54px]" style="font-family: 'Avenir', sans-serif;">
                            {{ caseStudy.campaignBody }}
                        </p>
                    </div>
                </div>
            </section>

            <!-- ============ STORY (full-bleed image + text overlay rechts, campagne-style) ============ -->
            <section
                v-if="caseStudy.storyMedia || caseStudy.storyTitle || caseStudy.storyBody"
                data-section="story"
                class="relative"
            >
                <div class="relative h-[600px] overflow-hidden bg-black lg:h-[994px]">
                    <HlsVideo
                        v-if="caseStudy.storyMedia && isVideo(caseStudy.storyMedia)"
                        :src="caseStudy.storyMedia"
                        autoplay
                        loop
                        muted
                        playsinline
                        class="h-full w-full object-cover"
                    />
                    <img
                        v-else-if="caseStudy.storyMedia"
                        :src="caseStudy.storyMedia"
                        alt=""
                        class="h-full w-full object-cover"
                    />

                    <div class="absolute inset-0 bg-gradient-to-b from-transparent via-transparent to-black/60 lg:bg-gradient-to-r lg:from-transparent lg:via-transparent lg:to-black/35" />

                    <div class="absolute inset-x-[33px] bottom-[40px] mx-auto max-w-[415px] text-center text-white lg:inset-x-auto lg:top-1/2 lg:left-[58%] lg:bottom-auto lg:mx-0 lg:w-[415px] lg:max-w-none lg:-translate-y-1/2 lg:text-left">
                        <h2
                            v-if="caseStudy.storyTitle"
                            class="text-[28px] leading-[34px] tracking-[-0.84px] lg:text-[32px] lg:leading-[38px] lg:tracking-[-0.96px]"
                            style="font-family: 'Avenir', sans-serif;"
                        >
                            {{ caseStudy.storyTitle }}
                        </h2>
                        <p
                            v-if="caseStudy.storyBody"
                            class="mt-4 text-[16px] leading-[24px] tracking-[-0.48px] lg:mt-6 lg:text-[18px] lg:leading-[28px] lg:tracking-[-0.54px]"
                            style="font-family: 'Avenir', sans-serif;"
                        >
                            {{ caseStudy.storyBody }}
                        </p>
                    </div>
                </div>
            </section>

            <!-- ============ PRE-CALLOUT IMAGE CAROUSEL ============ -->
            <section
                v-if="hasPreCalloutBlock"
                data-section="pre-callout-gallery"
                class="grid gap-10 px-[33px] py-[80px] lg:grid-cols-[minmax(280px,374px)_minmax(0,1fr)] lg:gap-[60px] lg:py-[120px] lg:pr-0 lg:pl-[8.7vw]"
            >
                <div class="lg:pt-2">
                    <h2
                        v-if="caseStudy.preCalloutTitle"
                        class="whitespace-pre-line text-[42px] leading-[1.1] tracking-[-1.26px] lg:text-[48px] lg:leading-[1.12] lg:tracking-[-1.44px]"
                        :style="{ color: accentColor, fontFamily: '\'Avenir\', sans-serif' }"
                    >
                        {{ caseStudy.preCalloutTitle }}
                    </h2>
                    <p
                        v-if="caseStudy.preCalloutBody"
                        class="mt-7 whitespace-pre-line text-[17px] leading-[1.65] tracking-[-0.3px] lg:text-[18px]"
                        :style="{ color: palette.pageText, fontFamily: '\'Avenir\', sans-serif' }"
                    >
                        {{ caseStudy.preCalloutBody }}
                    </p>
                </div>

                <div v-if="preCalloutGalleryCount" class="min-w-0 overflow-hidden">
                    <div
                        class="flex gap-6 transition-transform duration-500 ease-out"
                        :style="preCalloutGalleryStyle"
                    >
                        <div
                            v-for="(item, index) in preCalloutGalleryItems"
                            :key="`${item.url}-${index}`"
                            class="aspect-[560/860] w-[min(560px,72vw)] shrink-0 overflow-hidden rounded-[12px] bg-neutral-200 lg:w-[560px]"
                        >
                            <HlsVideo
                                v-if="item.type === 'video'"
                                :src="item.url"
                                autoplay
                                loop
                                muted
                                playsinline
                                class="block h-full w-full object-cover"
                            />
                            <img
                                v-else
                                :src="item.url"
                                alt=""
                                class="block h-full w-full object-cover"
                                loading="lazy"
                            />
                        </div>
                    </div>

                    <div class="mt-7 flex items-center justify-between px-3">
                        <p
                            class="text-[15px] tracking-[-0.15px]"
                            :style="{ color: accentColor, fontFamily: '\'Avenir\', sans-serif' }"
                        >
                            {{ pad2(preCalloutIndex + 1) }} — {{ pad2(preCalloutGalleryCount) }}
                        </p>

                        <div class="flex items-center gap-5">
                            <button
                                type="button"
                                class="text-[32px] leading-none transition disabled:opacity-25"
                                :style="{ color: accentColor }"
                                :disabled="preCalloutIndex === 0"
                                aria-label="Vorige afbeelding"
                                @click="scrollPreCalloutGallery(-1)"
                            >
                                ←
                            </button>
                            <button
                                type="button"
                                class="text-[32px] leading-none transition disabled:opacity-25"
                                :style="{ color: accentColor }"
                                :disabled="preCalloutIndex >= preCalloutGalleryCount - 1"
                                aria-label="Volgende afbeelding"
                                @click="scrollPreCalloutGallery(1)"
                            >
                                →
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ============ CALLOUT (massive multi-line tussentitel zoals "Go,Go,Go Koala!") ============ -->
            <section
                v-if="caseStudy.calloutTitle"
                data-section="callout"
                class="px-[33px] pt-[80px] pb-[40px] lg:px-[59px] lg:pt-[120px] lg:pb-[80px]"
            >
                <h2
                    class="whitespace-pre-line text-center text-[clamp(44px,12vw,72px)] leading-[1.05] tracking-[-1.5px] lg:text-left lg:text-[200px] lg:leading-[190px] lg:tracking-[-6px]"
                    :style="{ color: accentColor, fontFamily: '\'Avenir\', sans-serif', fontWeight: '900', fontStyle: 'oblique' }"
                >
                    {{ caseStudy.calloutTitle }}
                </h2>
            </section>

            <!-- ============ SECONDARY STORY (grote afbeelding links + body en CTA-pill rechts) ============ -->
            <section
                v-if="caseStudy.secondaryStoryMedia || caseStudy.secondaryStoryBody"
                data-section="secondary-story"
                class="px-[33px] pb-[80px] lg:pl-[59px] lg:pr-[10%] lg:pb-[120px]"
            >
                <div class="flex flex-col gap-8 lg:grid lg:grid-cols-[minmax(0,973px)_minmax(320px,1fr)] lg:items-start lg:gap-24">
                    <div class="overflow-hidden rounded-[20px] bg-neutral-200">
                        <HlsVideo
                            v-if="caseStudy.secondaryStoryMedia && isVideo(caseStudy.secondaryStoryMedia)"
                            :src="caseStudy.secondaryStoryMedia"
                            autoplay
                            loop
                            muted
                            playsinline
                            class="aspect-[973/544] w-full object-cover"
                        />
                        <img
                            v-else-if="caseStudy.secondaryStoryMedia"
                            :src="caseStudy.secondaryStoryMedia"
                            alt=""
                            class="aspect-[973/544] w-full object-cover"
                        />
                    </div>

                    <div class="flex h-full flex-col">
                        <p
                            v-if="caseStudy.secondaryStoryBody"
                            class="whitespace-pre-line text-center text-[18px] leading-[28px] tracking-[-0.54px] lg:text-left"
                            :style="{ color: palette.pageText, fontFamily: '\'Avenir\', sans-serif' }"
                        >
                            {{ caseStudy.secondaryStoryBody }}
                        </p>
                        <a
                            v-if="caseStudy.secondaryStoryButtonText"
                            href="#contact"
                            class="mt-8 flex h-[73px] w-full max-w-[387px] items-center gap-4 self-center rounded-[73px] px-4 lg:mt-10 lg:self-start"
                            style="background: rgba(0,0,0,0.7); border: 0.6px solid rgba(255,255,255,0.05); backdrop-filter: blur(6px);"
                        >
                            <span class="flex-1 text-white" style="font-family: 'Avenir', sans-serif; font-size: 18px; letter-spacing: -0.18px;">
                                {{ caseStudy.secondaryStoryButtonText }}
                            </span>
                            <div class="flex h-[43px] w-[57px] flex-shrink-0 items-center justify-center rounded-[69px] bg-[#ffc700]">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" style="transform: rotate(-135deg)">
                                    <path d="M3 8H13M13 8L8 3M13 8L8 13" stroke="black" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>
                        </a>
                    </div>
                </div>
            </section>

            <!-- ============ RESULTS (titel met pijl-icoon links boven, stats in 3 cellen met verticale dividers) ============ -->
            <section v-if="(caseStudy.resultsStats || []).length" data-section="results" class="border-b" :style="{ borderColor: palette.divider }">
                <div class="border-b px-[33px] py-6 lg:px-[5%] lg:py-7" :style="{ borderColor: palette.divider }">
                    <div class="flex items-center gap-4">
                        <svg
                            width="28"
                            height="28"
                            viewBox="0 0 24 24"
                            fill="none"
                            class="lg:h-9 lg:w-9"
                            :style="{ color: accentColor }"
                            aria-hidden="true"
                        >
                            <path d="M7 7l10 10M17 17H10M17 17V10" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <h2
                            class="text-[24px] leading-[30px] tracking-[-0.72px] lg:text-[32px] lg:leading-[38px] lg:tracking-[-0.96px]"
                            :style="{ color: accentColor, fontFamily: '\'Avenir\', sans-serif' }"
                        >
                            {{ caseStudy.resultsHeading }}
                        </h2>
                    </div>
                </div>

                <div
                    class="flex flex-col divide-y lg:grid lg:grid-cols-3 lg:divide-x lg:divide-y-0"
                    :class="isDarkTheme ? 'divide-white/10' : 'divide-black/10'"
                >
                    <div
                        v-for="(stat, index) in caseStudy.resultsStats"
                        :key="index"
                        class="flex flex-col items-center px-8 py-[60px] text-center lg:py-[80px]"
                    >
                        <div class="flex items-start">
                            <p
                                v-if="stat.prefix"
                                class="mt-8 -mr-2 text-[32px] leading-[32px] tracking-[-0.96px] lg:mt-12 lg:-mr-3 lg:text-[40px] lg:leading-[40px] lg:tracking-[-1.2px]"
                                :style="{ color: accentColor, fontFamily: '\'Avenir\', sans-serif', fontWeight: '900', fontStyle: 'oblique' }"
                            >
                                {{ stat.prefix }}
                            </p>
                            <p
                                class="text-[100px] leading-[100px] tracking-[-3px] lg:text-[140px] lg:leading-[140px] lg:tracking-[-4.2px]"
                                :style="{ color: accentColor, fontFamily: '\'Avenir\', sans-serif', fontWeight: '900', fontStyle: 'oblique' }"
                            >
                                {{ stat.value }}
                            </p>
                        </div>
                        <p
                            class="mt-6 text-[18px] leading-[24px] tracking-[-0.54px] lg:mt-8"
                            :style="{ color: accentColor, fontFamily: '\'Avenir\', sans-serif' }"
                        >
                            {{ stat.label }}
                        </p>
                    </div>
                </div>
            </section>

            <!-- ============ OPTIONAL PANEL (extra centered CTA panel) ============ -->
            <section
                v-if="caseStudy.optionalPanelTitle || caseStudy.optionalPanelBody || caseStudy.optionalPanelButtonText"
                data-section="optional-panel"
                class="px-[33px] pt-[40px] pb-[80px] lg:px-[59px] lg:pb-[120px]"
            >
                <div class="mx-auto max-w-[765px] text-center">
                    <h2
                        v-if="caseStudy.optionalPanelTitle"
                        class="text-[36px] leading-[44px] tracking-[-1.08px] lg:text-[50px] lg:leading-[60px] lg:tracking-[-1.5px]"
                        :style="{ color: accentColor, fontFamily: '\'Avenir\', sans-serif' }"
                    >
                        {{ caseStudy.optionalPanelTitle }}
                    </h2>
                    <p
                        v-if="caseStudy.optionalPanelBody"
                        class="mt-6 text-[18px] leading-[28px] tracking-[-0.54px] lg:text-[22px] lg:leading-[32px] lg:tracking-[-0.66px]"
                        :style="{ color: palette.pageText, fontFamily: '\'Avenir\', sans-serif' }"
                    >
                        {{ caseStudy.optionalPanelBody }}
                    </p>
                    <div v-if="caseStudy.optionalPanelButtonText" class="mt-8 flex justify-center lg:mt-10">
                        <a
                            href="#contact"
                            class="rounded-[30px] border px-8 py-3 text-[18px] tracking-[-0.54px] lg:text-[20px] lg:tracking-[-0.6px]"
                            :style="{ borderColor: accentColor, color: accentColor, fontFamily: '\'Avenir\', sans-serif' }"
                        >
                            {{ caseStudy.optionalPanelButtonText }}
                        </a>
                    </div>
                </div>
            </section>

            <!-- ============ MORE CASES (dark "Explore more cases" carousel) ============ -->
            <section
                v-if="moreCases && moreCases.length"
                data-section="more-cases"
                class="bg-black py-[80px] lg:py-[125px]"
            >
                <!-- Titel rechtsboven — ↙ pijl links van de titel -->
                <div class="mb-10 flex items-start justify-end gap-3 px-[33px] lg:mb-14 lg:px-[59px]">
                    <img :src="CASES_ARROW_URL" alt="" class="mt-2 h-[44px] w-[44px] lg:mt-3 lg:h-[52px] lg:w-[52px]" />
                    <h2
                        class="text-[33px] leading-[30px] tracking-[-0.99px] text-white lg:text-[40px] lg:leading-[36px] lg:tracking-[-1.21px]"
                        style="font-family: 'Avenir', sans-serif; font-weight: 900; font-style: oblique;"
                    >
                        Explore<br />more cases
                    </h2>
                </div>

                <!-- Center-peek infinite carousel: actieve case gecentreerd, buren links/rechts net buiten beeld -->
                <CasesCarousel
                    :cases="moreCases"
                    centered
                    infinite
                    :show-counter="false"
                    :controls-padding-right="isLargeScreen ? '59px' : '33px'"
                    aspect-ratio="16 / 9"
                    :gap="isLargeScreen ? '40px' : '24px'"
                    :card-widths="
                        isLargeScreen
                            ? ['42vw', '32vw', '37vw']
                            : ['82vw', '66vw', '74vw']
                    "
                />
            </section>
        </main>

        <SiteFooter id="contact" :footer-items="siteItems" />
    </div>
</template>

<style scoped>
[data-section='gallery'] > div::-webkit-scrollbar {
    display: none;
}
</style>
