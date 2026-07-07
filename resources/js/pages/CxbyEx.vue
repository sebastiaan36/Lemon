<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { ref, onMounted, onUnmounted } from 'vue'
import SiteFooter from '@/components/SiteFooter.vue'
import SiteHeader from '@/components/SiteHeader.vue'

type Step = {
    number: string
    title: string
    body: string
}

type BrandLogo = {
    logo: string
    name: string
}

const props = defineProps<{
    seoTitle?: string | null
    metaDescription?: string | null
    heroBgImage?: string | null
    heroSubtitle?: string | null
    narrativeText?: string | null
    caseBgImage?: string | null
    caseBodyText?: string | null
    caseClientName?: string | null
    caseTags?: string[]
    bodyCol1?: string | null
    bodyCol2?: string | null
    quoteBgImage?: string | null
    quoteText?: string | null
    quoteAuthor?: string | null
    steps?: Step[]
    checklistImage?: string | null
    checklistButtonText?: string | null
    checklistHref?: string | null
    brandsTitleLine1?: string | null
    brandsTitleLine2?: string | null
    brandLogos?: BrandLogo[]
}>()

function scrollDown() {
    document.getElementById('cxbyex-content')?.scrollIntoView({ behavior: 'smooth' })
}

function isVideo(url: string | null | undefined) {
    return !!url && /\.(mp4|webm|ogg|mov|m3u8)(\?.*)?$/i.test(url)
}

// ─── Hero scroll-indicator: gele ring vult zich met de scrollvoortgang (zelfde als home) ───
const heroProgress = ref(0)
const scrollIndicatorOpacity = ref(1)

function updateHeroProgress(): void {
    const p = Math.max(0, Math.min(window.scrollY / window.innerHeight, 1))
    heroProgress.value = p
    // Fade uit over de tweede helft van de hero-scroll.
    scrollIndicatorOpacity.value = Math.max(0, Math.min(1, 1 - (p - 0.5) / 0.4))
}

// ─── Brand narrative: tekst scrollt 1:1 door een vaste fade-band (zelfde effect als de hero-bodytekst op home/about) ───
const narrativeRef = ref<HTMLElement | null>(null)
const narrativeTextRef = ref<HTMLElement | null>(null)
const narrativeTextHeight = ref(0)
const narrativeTranslate = ref(0)

// Fade aan boven- en onderkant van de band; solide in het midden.
const NARRATIVE_GRADIENT =
    'linear-gradient(180deg, transparent 0%, rgba(0, 0, 0, 0.35) 24%, black 42%, black 58%, rgba(0, 0, 0, 0.35) 76%, transparent 100%)'

function measureNarrative(): void {
    if (narrativeTextRef.value) {
        narrativeTextHeight.value = narrativeTextRef.value.offsetHeight
    }
}

function updateNarrative(): void {
    const el = narrativeRef.value
    if (!el) return
    const rect = el.getBoundingClientRect()
    const scrollRange = rect.height - window.innerHeight
    if (scrollRange <= 0) {
        narrativeTranslate.value = 0
        return
    }
    // rect.top loopt van 0 (net vastgepind) → -scrollRange; tekst beweegt 1:1 omhoog door de band.
    const scrolled = Math.max(0, Math.min(scrollRange, -rect.top))
    narrativeTranslate.value = scrollRange / 2 - scrolled
}

function onNarrativeScroll(): void {
    updateNarrative()
    updateHeroProgress()
}

function onNarrativeResize(): void {
    measureNarrative()
    updateNarrative()
    updateHeroProgress()
}

onMounted(() => {
    measureNarrative()
    updateNarrative()
    updateHeroProgress()
    window.addEventListener('scroll', onNarrativeScroll, { passive: true })
    window.addEventListener('resize', onNarrativeResize, { passive: true })
})

onUnmounted(() => {
    window.removeEventListener('scroll', onNarrativeScroll)
    window.removeEventListener('resize', onNarrativeResize)
})
</script>

<template>
    <Head>
        <title>{{ seoTitle || 'CXbyEX — Lemon Scented Tea' }}</title>
        <meta v-if="metaDescription" name="description" :content="metaDescription" />
    </Head>

    <div class="bg-[#ffc700]">
        <SiteHeader />

        <!-- ============ HERO ============ -->
        <section class="relative flex h-screen flex-col items-center justify-center overflow-hidden rounded-b-[30px]">
            <video
                v-if="isVideo(heroBgImage)"
                :src="heroBgImage!"
                autoplay muted loop playsinline
                class="absolute inset-0 h-full w-full object-cover"
            />
            <img v-else-if="heroBgImage" :src="heroBgImage" alt="" class="absolute inset-0 h-full w-full object-cover" />
            <div class="absolute inset-0 bg-black/40" />

            <div class="relative z-10 flex flex-col items-center px-[59px] text-center">
                <h1
                    class="text-[100px] leading-none tracking-[-4.5px] text-[#ffc700] lg:text-[150px]"
                    style="font-family: 'Avenir', system-ui, sans-serif; font-weight: 900; font-style: oblique;"
                >
                    CXbyEX
                </h1>
                <p
                    v-if="heroSubtitle"
                    class="mt-8 max-w-[840px] text-[20px] leading-[1.25] tracking-[-0.88px] text-white lg:text-[29px]"
                    style="font-family: 'Avenir', system-ui, sans-serif;"
                >
                    {{ heroSubtitle }}
                </p>
            </div>

            <!-- Scroll indicator: ring vult zich naarmate je door de hero scrollt (zelfde als home) -->
            <button
                class="absolute bottom-2 left-1/2 -translate-x-1/2 cursor-pointer border-none bg-transparent p-0"
                :style="{ opacity: scrollIndicatorOpacity }"
                @click="scrollDown"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="66"
                    height="80"
                    viewBox="0 0 105 127"
                    fill="none"
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
                            <feFlood flood-opacity="0" result="BackgroundImageFix" />
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
        </section>

        <div id="cxbyex-content" />

        <!-- ============ BRAND NARRATIVE (tekst scrollt door een vaste fade-band) ============ -->
        <section
            v-if="narrativeText"
            ref="narrativeRef"
            class="relative"
            :style="{ height: `calc(100vh + ${narrativeTextHeight}px)` }"
        >
            <div
                class="sticky top-0 flex h-screen items-center justify-center overflow-hidden px-[59px]"
                :style="{ maskImage: NARRATIVE_GRADIENT, WebkitMaskImage: NARRATIVE_GRADIENT }"
            >
                <p
                    ref="narrativeTextRef"
                    class="mx-auto max-w-[971px] text-center text-[38px] leading-[58px] tracking-[-1.5px] text-black lg:text-[50px] lg:leading-[60px]"
                    style="font-family: 'Avenir', system-ui, sans-serif;"
                    :style="{ transform: `translateY(${narrativeTranslate}px)` }"
                >
                    {{ narrativeText }}
                </p>
            </div>
        </section>

        <!-- ============ CASE STUDY CARD ============ -->
        <section class="mx-[59px] overflow-hidden rounded-[30px] lg:mx-[95px]">
            <div class="relative flex min-h-[700px] flex-col justify-between gap-[60px] bg-black px-[60px] py-[80px] lg:min-h-[1048px] lg:px-[80px]">
                <video
                    v-if="isVideo(caseBgImage)"
                    :src="caseBgImage!"
                    autoplay muted loop playsinline
                    class="absolute inset-0 h-full w-full object-cover"
                />
                <img v-else-if="caseBgImage" :src="caseBgImage" alt="" class="absolute inset-0 h-full w-full object-cover" />
                <div class="absolute inset-0 bg-black/30" />

                <!-- Bodytekst: links, rond het verticale midden van de kaart (zoals Figma) -->
                <p
                    v-if="caseBodyText"
                    class="relative z-10 max-w-[573px] text-[26px] leading-[1.35] tracking-[-0.97px] text-white lg:mt-auto lg:text-[32px]"
                    style="font-family: 'Avenir', system-ui, sans-serif;"
                >
                    {{ caseBodyText }}
                </p>

                <!-- Case example: rechtsonder -->
                <div class="relative z-10 flex flex-col gap-3 lg:mt-auto lg:items-start lg:self-end">
                    <p
                        class="text-[18px] leading-[32px] tracking-[-0.54px] text-white"
                        style="font-family: 'Avenir', system-ui, sans-serif;"
                    >
                        Case example
                    </p>
                    <p
                        v-if="caseClientName"
                        class="text-[27px] tracking-[-0.8px] text-[#ffc700]"
                        style="font-family: 'Avenir', system-ui, sans-serif; font-weight: 900; font-style: oblique;"
                    >
                        {{ caseClientName }}
                    </p>
                    <div v-if="caseTags && caseTags.length" class="flex flex-wrap gap-3">
                        <span
                            v-for="tag in caseTags"
                            :key="tag"
                            class="flex h-[36px] items-center rounded-full border border-white px-4 text-[14px] tracking-[-0.14px] text-white"
                            style="font-family: 'Avenir', system-ui, sans-serif;"
                        >
                            {{ tag }}
                        </span>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============ TWO COLUMN BODY TEXT ============ -->
        <section v-if="bodyCol1 || bodyCol2" class="grid gap-[40px] px-[59px] py-[100px] lg:grid-cols-2 lg:gap-[60px]">
            <p
                v-if="bodyCol1"
                class="text-[20px] leading-[32px] tracking-[-0.6px] text-black lg:text-[24px]"
                style="font-family: 'Avenir', system-ui, sans-serif;"
            >
                {{ bodyCol1 }}
            </p>
            <p
                v-if="bodyCol2"
                class="text-[20px] leading-[32px] tracking-[-0.6px] text-black lg:text-[24px]"
                style="font-family: 'Avenir', system-ui, sans-serif;"
            >
                {{ bodyCol2 }}
            </p>
        </section>

        <!-- ============ QUOTE SECTION ============ -->
        <section class="relative" style="min-height: 600px;">
            <div class="absolute inset-0 overflow-hidden">
                <video
                    v-if="isVideo(quoteBgImage)"
                    :src="quoteBgImage!"
                    autoplay muted loop playsinline
                    class="absolute inset-0 h-full w-full object-cover"
                />
                <img v-else-if="quoteBgImage" :src="quoteBgImage" alt="" class="absolute inset-0 h-full w-full object-cover" />
                <div class="absolute inset-0 bg-black/30" />
            </div>

            <div class="relative z-10 flex min-h-[600px] flex-col items-center justify-center px-[59px] py-[140px] text-center lg:min-h-[1054px]">
                <blockquote
                    v-if="quoteText"
                    class="max-w-[1280px] text-[46px] leading-[1.0] tracking-[-1.94px] text-[#ffc700] lg:text-[65px]"
                    style="font-family: 'Avenir', system-ui, sans-serif; font-weight: 900; font-style: oblique;"
                >
                    <span class="text-white">'</span>{{ quoteText }}<span class="text-white">'</span>
                </blockquote>
                <p
                    v-if="quoteAuthor"
                    class="mt-8 text-[18px] leading-[32px] tracking-[-0.54px] text-white"
                    style="font-family: 'Avenir', system-ui, sans-serif;"
                >
                    {{ quoteAuthor }}
                </p>
            </div>

            <!-- CTA: rechtsonder, half over de sectiegrens heen (zoals Figma) -->
            <a
                href="/contact"
                class="absolute bottom-0 right-[59px] z-20 flex h-[73px] translate-y-1/2 items-center gap-3 rounded-full border border-white/10 bg-black/70 pl-6 pr-3 text-[18px] tracking-[-0.18px] text-white backdrop-blur-sm transition-opacity hover:opacity-80"
                style="font-family: 'Avenir', system-ui, sans-serif;"
            >
                Tell us about your project
                <span class="flex h-[43px] w-[57px] shrink-0 items-center justify-center rounded-full bg-[#ffc700]">
                    <svg class="h-4 w-4 -rotate-45 text-black" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 8h10M8 3l5 5-5 5" />
                    </svg>
                </span>
            </a>
        </section>

        <!-- ============ FROM STORY TO STRATEGY TO ACTION ============ -->
        <section class="relative flex flex-col gap-[80px] px-[59px] py-[120px] lg:flex-row lg:gap-[100px]">
            <div class="lg:w-[420px] lg:shrink-0">
                <div class="flex items-start gap-5">
                    <h2
                        class="text-[56px] leading-[1.0] tracking-[-2px] text-black lg:text-[68px] lg:leading-[68px] lg:tracking-[-2.06px]"
                        style="font-family: 'Avenir', system-ui, sans-serif; font-weight: 900; font-style: oblique;"
                    >
                        From story<br />to strategy<br />to action
                    </h2>

                    <svg
                        class="mt-[10px] h-[44px] w-[44px] shrink-0"
                        viewBox="0 0 44 44"
                        fill="none"
                        stroke="black"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <path d="M6 22h30M25 12l11 10-11 10" />
                    </svg>
                </div>

                <p
                    class="mt-[48px] max-w-[425px] text-[20px] leading-[32px] tracking-[-0.6px] text-black lg:text-[24px] lg:tracking-[-0.72px]"
                    style="font-family: 'Avenir', system-ui, sans-serif;"
                >
                    We partner with leadership teams to transform brand ambition into lived behavior turning culture into a competitive advantage.
                </p>
            </div>

            <div v-if="steps && steps.length" class="min-w-0 flex-1">
                <div class="divide-y divide-black/15">
                    <div
                        v-for="step in steps"
                        :key="step.number"
                        class="py-[24px] first:pt-0"
                    >
                        <div class="flex items-start gap-5">
                            <span
                                class="flex h-[39px] w-[61px] shrink-0 items-center justify-center rounded-full bg-black text-[18px] tracking-[-0.54px] text-white"
                                style="font-family: 'Avenir', system-ui, sans-serif;"
                            >
                                {{ step.number }}
                            </span>
                            <div class="pt-[2px]">
                                <h3
                                    class="text-[24px] leading-[32px] tracking-[-0.28px] text-black lg:text-[28px]"
                                    style="font-family: 'Avenir', system-ui, sans-serif;"
                                >
                                    {{ step.title }}
                                </h3>
                                <p
                                    class="mt-1 text-[18px] leading-[24px] tracking-[-0.2px] text-[#101010] lg:text-[20px]"
                                    style="font-family: 'Avenir', system-ui, sans-serif;"
                                >
                                    {{ step.body }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Checklist CTA: rechtsonder, half over de grens met de zwarte sectie (zoals Figma) -->
            <a
                v-if="checklistButtonText"
                :href="checklistHref || '#'"
                class="absolute bottom-0 right-[59px] z-20 flex h-[73px] translate-y-1/2 items-center gap-3 rounded-full border border-white/10 bg-black/70 pr-3 text-[18px] tracking-[-0.18px] text-white backdrop-blur-sm transition-opacity hover:opacity-80"
                :class="checklistImage ? 'pl-[145px]' : 'pl-6'"
                style="font-family: 'Avenir', system-ui, sans-serif;"
            >
                <span
                    v-if="checklistImage"
                    class="absolute bottom-0 left-[51px] block h-[90px] w-[74px] overflow-hidden bg-white shadow-md"
                >
                    <img :src="checklistImage" alt="" class="absolute inset-0 h-full w-full object-cover" />
                </span>
                {{ checklistButtonText }}
                <span class="flex h-[43px] w-[57px] shrink-0 items-center justify-center rounded-full bg-[#ffc700]">
                    <svg class="h-4 w-4 -rotate-45 text-black" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 8h10M8 3l5 5-5 5" />
                    </svg>
                </span>
            </a>
        </section>

        <!-- ============ LEADING BRANDS ============ -->
        <section class="bg-black px-[59px] py-[140px] text-center">
            <h2
                class="mb-[80px] leading-[1.0] tracking-[-4.5px] text-[80px] lg:text-[150px]"
                style="font-family: 'Avenir', system-ui, sans-serif; font-weight: 900; font-style: oblique;"
            >
                <span class="text-white">{{ brandsTitleLine1 || 'Leading brands' }}<br /></span>
                <span class="text-[#ffc700]">{{ brandsTitleLine2 || 'choose Lemon' }}</span>
            </h2>

            <div v-if="brandLogos && brandLogos.length" class="mb-[80px] flex flex-wrap items-center justify-center gap-[60px] lg:gap-[100px]">
                <img
                    v-for="(brand, i) in brandLogos"
                    :key="i"
                    :src="brand.logo"
                    :alt="brand.name"
                    class="h-[50px] w-auto max-w-[180px] object-contain brightness-0 invert"
                />
            </div>

            <a
                href="/contact"
                class="inline-flex h-[65px] items-center justify-center rounded-full border border-white px-10 text-[22px] tracking-[-0.69px] text-white transition-colors hover:bg-white hover:text-black"
                style="font-family: 'Avenir', system-ui, sans-serif;"
            >
                Get in touch
            </a>
        </section>

        <SiteFooter />
    </div>
</template>
