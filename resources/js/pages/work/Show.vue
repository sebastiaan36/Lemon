<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { computed, onMounted, onUnmounted, ref } from 'vue'
import HlsVideo from '@/components/HlsVideo.vue'
import SiteFooter from '@/components/SiteFooter.vue'
import SiteHeader from '@/components/SiteHeader.vue'

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
        heroTitle?: string | null
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

const accentColor = props.caseStudy.accentColor || '#0A7949'
const siteItems = [
    { label: 'About', href: '/#about' },
    { label: 'CxByEx', href: '/#services' },
    { label: 'Work', href: '/work' },
    { label: 'Industries', href: '/#services' },
    { label: 'Contact', href: '/#contact' },
]

const isLargeScreen = ref(false)
const galleryHeight = computed(() => (isLargeScreen.value ? 600 : 360))

function updateScreenSize() {
    if (typeof window === 'undefined') return
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

    <div class="bg-[#fcfcfc] text-[#101010]">
        <SiteHeader :nav-color="accentColor" contact-href="/#contact" :menu-items="siteItems" />

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
                    {{ caseStudy.heroTitle }}
                </h1>

                <div class="relative mx-auto max-w-[1529px] overflow-hidden rounded-[20px] bg-neutral-200">
                    <HlsVideo
                        v-if="caseStudy.heroMedia && isVideo(caseStudy.heroMedia)"
                        :src="caseStudy.heroMedia"
                        autoplay
                        loop
                        muted
                        playsinline
                        class="aspect-[1529/901] w-full object-cover"
                    />
                    <img
                        v-else-if="caseStudy.heroMedia"
                        :src="caseStudy.heroMedia"
                        :alt="caseStudy.name"
                        class="aspect-[1529/901] w-full object-cover"
                    />
                    <div v-else class="aspect-[1529/901] w-full bg-neutral-200" />

                    <span
                        v-if="caseStudy.heroDuration"
                        class="absolute right-6 bottom-6 text-[15px] tracking-[-0.47px] text-white"
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
                            :style="{
                                height: galleryHeight + 'px',
                                aspectRatio: item.width && item.height ? `${item.width} / ${item.height}` : '16 / 9',
                            }"
                        >
                            <HlsVideo
                                v-if="item.type === 'video'"
                                :src="item.url"
                                autoplay
                                loop
                                muted
                                playsinline
                                class="block h-full w-full"
                            />
                            <img
                                v-else
                                :src="item.url"
                                alt=""
                                :width="item.width ?? undefined"
                                :height="item.height ?? undefined"
                                class="block h-full w-full"
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
                            class="flex h-[52px] w-[52px] items-center justify-center rounded-full border transition disabled:opacity-30"
                            :style="{ borderColor: accentColor, color: accentColor }"
                            :disabled="galleryIndex === 0"
                            aria-label="Vorige"
                            @click="scrollGallery(-1)"
                        >
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <path d="M16 10H4M4 10l5-5M4 10l5 5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                        <button
                            type="button"
                            class="flex h-[52px] w-[52px] items-center justify-center rounded-full border transition disabled:opacity-30"
                            :style="{ borderColor: accentColor, color: accentColor }"
                            :disabled="galleryIndex >= galleryCount - 1"
                            aria-label="Volgende"
                            @click="scrollGallery(1)"
                        >
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
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
                        class="mx-auto max-w-[413px] text-center text-[64px] leading-[60px] tracking-[-1.92px] text-white lg:max-w-[800px] lg:text-[140px] lg:leading-[130px] lg:tracking-[-4.2px]"
                        style="font-family: 'Avenir', sans-serif; font-weight: 900; font-style: oblique;"
                    >
                        {{ caseStudy.highlightTitle }}
                    </h2>
                    <p
                        class="mx-auto mt-8 max-w-[449px] text-center text-[18px] leading-[28px] tracking-[-0.54px] text-white lg:mt-10 lg:max-w-[765px] lg:text-[22px] lg:leading-[32px] lg:tracking-[-0.66px]"
                        style="font-family: 'Avenir', sans-serif;"
                    >
                        {{ caseStudy.highlightBody }}
                    </p>
                    <div v-if="caseStudy.highlightButtonText" class="mt-8 flex justify-center lg:mt-10">
                        <a
                            href="#contact"
                            class="rounded-[30px] border border-white px-8 py-3 text-[18px] tracking-[-0.54px] text-white lg:text-[20px] lg:tracking-[-0.6px]"
                            style="font-family: 'Avenir', sans-serif;"
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

                    <div class="absolute inset-x-[33px] bottom-[40px] mx-auto max-w-[415px] text-center text-white lg:inset-x-auto lg:top-1/2 lg:right-[5%] lg:bottom-auto lg:mx-0 lg:w-[415px] lg:max-w-none lg:-translate-y-1/2 lg:text-left">
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
                            class="whitespace-pre-line text-center text-[18px] leading-[28px] tracking-[-0.54px] text-[#101010] lg:text-left"
                            style="font-family: 'Avenir', sans-serif;"
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
            <section v-if="(caseStudy.resultsStats || []).length" data-section="results" class="border-b border-black/10">
                <div class="border-b border-black/10 px-[33px] py-6 lg:px-[5%] lg:py-7">
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

                <div class="flex flex-col divide-y divide-black/10 lg:grid lg:grid-cols-3 lg:divide-x lg:divide-y-0 lg:divide-black/10">
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
                        class="mt-6 text-[18px] leading-[28px] tracking-[-0.54px] text-[#101010] lg:text-[22px] lg:leading-[32px] lg:tracking-[-0.66px]"
                        style="font-family: 'Avenir', sans-serif;"
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
            <section data-section="more-cases" class="bg-black px-[33px] py-[80px] lg:px-[59px] lg:py-[125px]">
                <div class="mb-10 flex flex-col items-start gap-6 lg:mb-16 lg:flex-row lg:items-start lg:justify-between lg:gap-0">
                    <div class="flex items-center gap-4">
                        <svg width="28" height="28" viewBox="0 0 32 32" fill="none" class="lg:h-8 lg:w-8">
                            <path d="M6 16h20M19 8l8 8-8 8" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <h2
                            class="max-w-[223px] text-[33px] leading-[30px] tracking-[-0.99px] text-white lg:text-[40px] lg:leading-[36px] lg:tracking-[-1.21px]"
                            style="font-family: 'Avenir', sans-serif; font-weight: 900; font-style: oblique;"
                        >
                            Explore more cases
                        </h2>
                    </div>

                    <a
                        href="#contact"
                        class="flex h-[73px] w-full max-w-[387px] items-center gap-4 rounded-[73px] px-4 lg:w-[387px]"
                        style="background: rgba(0,0,0,0.7); border: 0.6px solid rgba(255,255,255,0.05); backdrop-filter: blur(6px);"
                    >
                        <span class="flex-1 text-white" style="font-family: 'Avenir', sans-serif; font-size: 18px; letter-spacing: -0.18px;">
                            Tell us about your project
                        </span>
                        <div class="flex h-[43px] w-[57px] flex-shrink-0 items-center justify-center rounded-[69px] bg-[#ffc700]">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" style="transform: rotate(-135deg)">
                                <path d="M3 8H13M13 8L8 3M13 8L8 13" stroke="black" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>
                    </a>
                </div>

                <div class="-mx-[33px] flex snap-x snap-mandatory gap-4 overflow-x-auto px-[33px] pb-4 lg:mx-0 lg:grid lg:grid-cols-3 lg:gap-8 lg:overflow-visible lg:px-0 lg:pb-0">
                    <a
                        v-for="item in moreCases"
                        :key="item.slug"
                        :href="`/work/${item.slug}`"
                        class="group relative w-[260px] shrink-0 snap-start overflow-hidden rounded-[20px] bg-neutral-800 lg:w-auto"
                    >
                        <img v-if="item.photo" :src="item.photo" :alt="item.name" class="h-[321px] w-full object-cover transition duration-500 group-hover:scale-105" />
                        <HlsVideo
                            v-else-if="item.video"
                            :src="item.video"
                            autoplay
                            loop
                            muted
                            playsinline
                            class="h-[321px] w-full object-cover transition duration-500 group-hover:scale-105"
                        />
                        <div v-else class="h-[321px] w-full bg-neutral-700" />
                        <p
                            class="absolute left-6 top-6 max-w-[180px] text-[20px] leading-[1.05] tracking-[-0.6px] text-[#ffc700] lg:left-9 lg:top-9 lg:max-w-[220px] lg:text-[24px] lg:tracking-[-0.72px]"
                            style="font-family: 'Avenir', sans-serif; font-weight: 900; font-style: oblique;"
                        >
                            {{ item.name }}
                        </p>
                    </a>
                </div>
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
