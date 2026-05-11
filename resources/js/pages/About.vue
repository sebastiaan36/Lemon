<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import SiteFooter from '@/components/SiteFooter.vue'
import SiteHeader from '@/components/SiteHeader.vue'
import CasesCarousel from '@/components/CasesCarousel.vue'
import type { CSSProperties } from 'vue'
import { computed, onMounted, onUnmounted, ref } from 'vue'

const PHOTO_ROTATIONS = [-2.5, 1.8, -1.2, 2.2, -1.6]
const PHOTO_OFFSETS  = [0, -32, -16, -48, -8]

const props = withDefaults(
    defineProps<{
        seoTitle?: string | null
        metaDescription?: string | null
        heroTitle?: string
        heroDescription?: string
        introQuoteLine1?: string
        introQuoteLine2?: string
        introBgImage?: { url: string; type: string } | null
        ctaText?: string
        ctaHref?: string
        credibleTitleLine1?: string
        credibleTitleLine2?: string
        credibleBody?: string
        credibleCases?: Array<{ name: string; slug: string; video: string | null; photo: string | null; touchpoints?: string[] }>
        growthTitleLine1?: string
        growthTitleLine2?: string
        growthBody?: string
        growthImage?: { url: string; type: string } | null
        growthClientName?: string | null
        growthTags?: string[]
        services?: Array<{ name: string; description: string; tags: string[] }>
        ancientTitleLine1?: string
        ancientTitleLine2?: string
        ancientBody?: string
        ancientImage?: string | null
        internationalTitleLine1?: string
        internationalTitleLine2?: string
        internationalBody?: string
        teamPhotos?: string[]
        joinTeamText?: string
        joinTeamHref?: string
    }>(),
    {
        seoTitle: null,
        metaDescription: null,
        heroTitle: 'This is Lemon',
        heroDescription:
            "We compare what we do to the ritual of making tea. The boiling water represents society, while the tea bag holds the essence of your brand. We turn that essence into your credible brand narrative, strong enough to infuse people's lives and create a lasting, refreshing blend that helps your brand grow.",
        introQuoteLine1: 'And Lemon?',
        introQuoteLine2: 'We keep it fresh.',
        introBgImage: null as { url: string; type: string } | null,
        ctaText: 'Tell us about your project',
        ctaHref: '/contact',
        credibleTitleLine1: 'Credible',
        credibleTitleLine2: 'Creativity',
        credibleBody:
            'In a world overloaded with brand noise, algorithms, and fake news, people are turning away from brands that fail to live up to what they say and do. Only those that wow minds and win hearts by consistently proving their core essence will stay relevant and future-proof.',
        credibleCases: () => [] as Array<{ name: string; slug: string; video: string | null; photo: string | null; touchpoints?: string[] }>,
        growthTitleLine1: 'Total brand',
        growthTitleLine2: 'growth',
        growthBody:
            "The entire customer journey, employer branding, and 360° advertising brought together in one cohesive strategy. That's how real brand growth happens. Consistent, credible and built to last. We make it our daily mission to guide your brand towards that growth. Totally.",
        growthImage: null as { url: string; type: string } | null,
        growthClientName: null,
        growthTags: () => [],
        services: () => [
            {
                name: 'Brand',
                description:
                    'Tell your credible brand story, stand out in your market, and connect with your customers.',
                tags: ['Positioning', 'Narrative', 'Campaigns', 'Visual identity', 'Activations'],
            },
            {
                name: 'Experience',
                description:
                    'Create memorable, connected brand experiences that drive engagement and loyalty from A to Z, customer to employee.',
                tags: ['Customer Journey', 'Digital', 'Events', 'Environments'],
            },
            {
                name: 'Employee',
                description:
                    'Activate your people as brand ambassadors and create a culture that lives your brand from the inside out.',
                tags: ['Internal Comms', 'Culture', 'Engagement', 'Training'],
            },
        ],
        ancientTitleLine1: "We're keeping",
        ancientTitleLine2: 'ancient actual',
        ancientBody:
            "We love a great story. It's been the recipe for human engagement since the beginning of time. Stories engage, simplify and spread. We use that power for your brand.",
        ancientImage: null,
        internationalTitleLine1: 'International',
        internationalTitleLine2: 'community',
        internationalBody:
            'Lemon Scented Tea is served hot in every time zone. You can find the Lemons in Amsterdam, Atlanta, London, and Munich. And just as the power of storytelling is universal, so are we.\n\nWe operate as one strong core surrounded by an eclectic community of top notch, like-minded professionals from around the world. All united by a single purpose: to create Credible Creativity.',
        teamPhotos: () => [],
        joinTeamText: 'Join our team',
        joinTeamHref: '/careers',
    },
)

// ─── Hero animatie (overgenomen van homepage Welcome.vue) ────────────────
const FIGMA_DESKTOP_WIDTH = 1640
const FIGMA_DESKTOP_HEIGHT = 900
const HERO_TEXT_LOCK_HEIGHT = 249.5
const HERO_TEXT_LOCK_WIDTH = 1169.4
const HERO_TEXT_LOCK_FONT_SIZE = 48.03
const HERO_TEXT_LOCK_LINE_HEIGHT = 60
const HERO_TEXT_LOCK_LETTER_SPACING = -1.44
const HERO_TEXT_START_TOP = 744
const HERO_TEXT_SCALE_START = 0.065
const HERO_TEXT_SCALE_END = 0.095

const heroRef = ref<HTMLElement | null>(null)
const heroProgress = ref(0)
const windowWidth = ref(typeof window !== 'undefined' ? window.innerWidth : FIGMA_DESKTOP_WIDTH)
const windowHeight = ref(typeof window !== 'undefined' ? window.innerHeight : FIGMA_DESKTOP_HEIGHT)

function rp(val: number, from: number, to: number): number {
    return Math.max(0, Math.min((val - from) / (to - from), 1))
}
function ease(t: number): number {
    return t < 0.5 ? 2 * t * t : -1 + (4 - 2 * t) * t
}
function lerp(from: number, to: number, t: number): number {
    return from + (to - from) * t
}
function sx(value: number): string {
    return `${(value / FIGMA_DESKTOP_WIDTH) * windowWidth.value}px`
}
function sy(value: number): string {
    return `${(value / FIGMA_DESKTOP_HEIGHT) * windowHeight.value}px`
}

const p = heroProgress

const titleStyle = computed<CSSProperties>(() => {
    const phase1 = ease(rp(p.value, 0, 0.10))
    const phase2 = ease(rp(p.value, 0.10, 0.20))
    const s = mobileFontScale.value
    return {
        fontSize: `${((120 - 37.507 * phase1) * s).toFixed(2)}px`,
        lineHeight: `${((70 - 21.879 * phase1) * s).toFixed(2)}px`,
        letterSpacing: `${((-4.8 + 1.5003 * phase1) * s).toFixed(4)}px`,
        transform: `translateY(${(28 * phase1 - 553 * phase2).toFixed(1)}px)`,
        opacity: 1 - ease(rp(p.value, 0.13, 0.20)),
    }
})

const bodyTextScaleT = computed(() => ease(rp(p.value, HERO_TEXT_SCALE_START, HERO_TEXT_SCALE_END)))
const bodyTextExitT = computed(() => ease(rp(p.value, 0.995, 1)))
const bodyTextFontSize = computed(() => {
    const s = mobileFontScale.value
    if (p.value >= HERO_TEXT_SCALE_END) return `${(HERO_TEXT_LOCK_FONT_SIZE * s).toFixed(2)}px`
    return `${(lerp(29.407, HERO_TEXT_LOCK_FONT_SIZE, bodyTextScaleT.value) * s).toFixed(2)}px`
})
const bodyTextLineHeight = computed(() => {
    const s = mobileFontScale.value
    if (p.value >= HERO_TEXT_SCALE_END) return `${(HERO_TEXT_LOCK_LINE_HEIGHT * s).toFixed(2)}px`
    return `${(lerp(36.759, HERO_TEXT_LOCK_LINE_HEIGHT, bodyTextScaleT.value) * s).toFixed(2)}px`
})
const bodyTextLetterSpacing = computed(() => {
    const s = mobileFontScale.value
    if (p.value >= HERO_TEXT_SCALE_END) return `${(HERO_TEXT_LOCK_LETTER_SPACING * s).toFixed(4)}px`
    return `${(lerp(-0.8822, HERO_TEXT_LOCK_LETTER_SPACING, bodyTextScaleT.value) * s).toFixed(4)}px`
})
const bodyTextWidth = computed(() => {
    if (p.value >= HERO_TEXT_SCALE_END) return `min(${sx(HERO_TEXT_LOCK_WIDTH)}, 90vw)`
    const phaseAWidth = lerp(701.36, HERO_TEXT_LOCK_WIDTH, bodyTextScaleT.value)
    return `min(${sx(phaseAWidth)}, 90vw)`
})
const bodyTextTop = computed(() => {
    // Viewport-relatieve positie: body start op baseTop en scrollt 1:1 mee met de pagina-scroll,
    // zodat de fixed-positioned container de gradient-mask viewport-relatief kan toepassen
    // (fade aan top/bottom van viewport). scrollY drives natural movement.
    const baseTop = (HERO_TEXT_START_TOP / FIGMA_DESKTOP_HEIGHT) * windowHeight.value
    return `${(baseTop - scrollY.value).toFixed(1)}px`
})
const bodyTextOpacity = computed(() => {
    if (p.value <= 0.995) return 1
    return 1 - bodyTextExitT.value
})
const bodyTextContainerStyle = computed<CSSProperties>(() => {
    const gradient = 'linear-gradient(180deg, transparent 0%, black 30%, black 70%, transparent 100%)'
    return {
        position: 'fixed',
        top: 0,
        left: 0,
        right: 0,
        bottom: 0,
        overflow: 'hidden',
        pointerEvents: 'none',
        opacity: bodyTextOpacity.value,
        maskImage: gradient,
        WebkitMaskImage: gradient,
        zIndex: 3,
    }
})
const bodyTextInnerStyle = computed<CSSProperties>(() => ({
    position: 'absolute',
    left: '50%',
    top: bodyTextTop.value,
    width: bodyTextWidth.value,
    transform: 'translate(-50%, 0)',
}))

// ─── Services scroll-state ───────────────────────────────────────────────
const servicesWrapper = ref<HTMLElement | null>(null)
const servicesSection = ref<HTMLElement | null>(null)
const scrollY = ref(0)
const sectionH = ref(0)

const servicesCount = computed(() => props.services?.length ?? 3)
const isMobile = computed(() => windowWidth.value < 1024)
const mobileFontScale = computed(() => Math.min(1, windowWidth.value / 900))

const wrapperHeight = computed(() =>
    isMobile.value ? 'auto' : `calc(${sectionH.value}px + ${servicesCount.value - 1} * 60vh)`,
)

const stickyTop = computed(() => {
    if (isMobile.value) return '0'
    return sectionH.value > 0 ? `calc(50vh - ${sectionH.value / 2}px)` : 'calc(50vh - 200px)'
})

const internationalGridStyle = computed(() =>
    isMobile.value
        ? { display: 'grid', gridTemplateColumns: '1fr', gap: '40px' }
        : { display: 'grid', gridTemplateColumns: '4fr 2fr', gap: '120px' },
)

const activeServiceIndex = computed(() => {
    if (!servicesWrapper.value || !props.services?.length || sectionH.value === 0) return 0

    const wrapper = servicesWrapper.value
    const stickyTopPx = window.innerHeight / 2 - sectionH.value / 2
    const scrollRange = wrapper.offsetHeight - sectionH.value

    if (scrollRange <= 0) return 0

    // getBoundingClientRect().top + scrollY = stable document offset; using scrollY.value
    // here registers this computed as a reactive scroll dependency.
    const wrapperDocTop = wrapper.getBoundingClientRect().top + window.scrollY
    const scrolled = scrollY.value + stickyTopPx - wrapperDocTop
    const progress = Math.max(0, Math.min(1, scrolled / scrollRange))

    return Math.min(props.services.length - 1, Math.floor(progress * props.services.length))
})

function updateSectionHeight(): void {
    if (servicesSection.value) {
        sectionH.value = servicesSection.value.offsetHeight
    }
}

function updateHeroProgress(): void {
    if (!heroRef.value) return
    const scrolled = window.scrollY - heroRef.value.offsetTop
    // Animatie loopt over de eerste viewport-hoogte van de hero (= scrolling door de hero zelf).
    // Daarna stopt p op 1 en is de hero klaar; intro-quote sluit aan zonder empty-yellow tussenruimte.
    const maxScroll = window.innerHeight
    heroProgress.value = Math.max(0, Math.min(scrolled / maxScroll, 1))
}

function onScroll(): void {
    scrollY.value = window.scrollY
    updateHeroProgress()
}

function onResize(): void {
    windowWidth.value = window.innerWidth
    windowHeight.value = window.innerHeight
    updateSectionHeight()
    updateHeroProgress()
}

onMounted(() => {
    updateSectionHeight()
    scrollY.value = window.scrollY
    updateHeroProgress()
    window.addEventListener('scroll', onScroll, { passive: true })
    window.addEventListener('resize', onResize, { passive: true })
})

onUnmounted(() => {
    window.removeEventListener('scroll', onScroll)
    window.removeEventListener('resize', onResize)
})
</script>

<template>
    <Head :title="seoTitle ?? 'About — Lemon Scented Tea'">
        <meta v-if="metaDescription" head-key="description" name="description" :content="metaDescription" />
    </Head>

    <SiteHeader nav-on-yellow />

    <!-- ============ HERO (gele opening, 140vh — body geschaalt op ~111vh hoogte; extra 30vh ademruimte onder body voordat de intro-quote start) ============ -->
    <section data-section="hero" ref="heroRef" class="relative overflow-hidden bg-[#ffc700]" style="height: 140vh">
        <!-- Titel: positie sy(387) — zelfde Y-positie als homepage hero. Animeert van 120px → 82.493px en scrollt mee uit beeld. -->
        <div class="pointer-events-none absolute left-0 right-0" :style="{ top: sy(387) }">
            <h1
                class="text-center"
                style="
                    font-family: 'Avenir', system-ui, sans-serif;
                    font-weight: 900;
                    font-style: oblique;
                    color: #000;
                "
                :style="titleStyle"
            >
                {{ heroTitle }}
            </h1>
        </div>

        <!-- Bodytekst: absoluut binnen de section met gradient mask, schaalt 29.4px → 48px en scrollt mee uit beeld. -->
        <div :style="bodyTextContainerStyle">
            <div :style="bodyTextInnerStyle">
                <p
                    class="text-center"
                    :style="{
                        fontFamily: '\'Avenir\', system-ui, sans-serif',
                        fontWeight: '400',
                        fontSize: bodyTextFontSize,
                        lineHeight: bodyTextLineHeight,
                        letterSpacing: bodyTextLetterSpacing,
                        color: '#000',
                    }"
                >
                    {{ heroDescription }}
                </p>
            </div>
        </div>
    </section>

    <!-- ============ INTRO QUOTE (groot citaat over achtergrondafbeelding/video) ============ -->
    <section data-section="intro-quote" class="relative min-h-[80vh] overflow-hidden bg-[#0c0c0c]">
        <video
            v-if="introBgImage && introBgImage.type === 'video'"
            :src="introBgImage.url"
            autoplay
            muted
            loop
            playsinline
            class="absolute inset-0 h-full w-full object-cover opacity-60"
        />
        <img
            v-else-if="introBgImage"
            :src="introBgImage.url"
            alt=""
            class="absolute inset-0 h-full w-full object-cover opacity-60"
        />
        <div class="relative flex min-h-[80vh] items-center justify-center px-6 py-24 lg:px-[59px]">
            <div
                class="text-center text-[clamp(60px,9vw,150px)] leading-[0.87] tracking-[-0.03em]"
                style="font-family: 'Avenir', sans-serif; font-weight: 900; font-style: oblique;"
            >
                <p class="text-[#ffc700]">{{ introQuoteLine1 }}</p>
                <p class="text-white">{{ introQuoteLine2 }}</p>
            </div>
        </div>
    </section>

    <!-- ─── Dark content block ────────────────────────────────────────────── -->
    <div class="bg-[#0c0c0c]">

        <!-- ============ CTA ("Tell us about your project"-pill rechts) ============ -->
        <div data-section="cta" class="flex justify-center px-6 py-10 lg:justify-end lg:px-[59px]">
            <a
                :href="ctaHref"
                class="flex items-center gap-4 rounded-[73px] border border-white/10 bg-black/70 px-6 py-4 transition-opacity hover:opacity-80"
            >
                <span
                    class="text-[18px] leading-none tracking-[-0.01em] text-white"
                    style="font-family: 'Avenir', sans-serif; font-weight: 700;"
                >
                    {{ ctaText }}
                </span>
                <span
                    class="flex h-[43px] w-[57px] items-center justify-center rounded-[69px] bg-[#ffc700]"
                >
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                        <path d="M1 15L15 1M15 1H5M15 1V11" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
            </a>
        </div>

        <!-- ============ CREDIBLE CREATIVITY (titel + body links, cases-carousel rechts) ============ -->
        <section data-section="credible" class="px-6 pb-24 pt-8 lg:pl-[59px] lg:pr-0">
            <div class="flex flex-col gap-10 lg:flex-row lg:items-center lg:gap-12">
                <!-- Left: title + body (fixed width) -->
                <div class="w-full shrink-0 lg:w-[380px]">
                    <h2
                        class="mb-8 text-[clamp(32px,3vw,40px)] leading-[1.0] tracking-[-0.03em] text-[#ffc700]"
                        style="font-family: 'Avenir', sans-serif; font-weight: 900; font-style: oblique;"
                    >
                        {{ credibleTitleLine1 }}<br />{{ credibleTitleLine2 }}
                    </h2>
                    <p
                        class="text-[20px] leading-[1.45] tracking-[-0.03em] text-white"
                        style="font-family: 'Avenir', sans-serif;"
                    >
                        {{ credibleBody }}
                    </p>
                </div>

                <!-- Right: case carousel (fills remaining width, bleeds to right edge) -->
                <div class="min-w-0 flex-1">
                    <CasesCarousel
                        v-if="credibleCases && credibleCases.length > 0"
                        :cases="credibleCases"
                    />
                    <div
                        v-else
                        class="flex h-[480px] items-center justify-center rounded-[17px] bg-neutral-800 text-white/20"
                        style="font-family: 'Avenir', sans-serif;"
                    >
                        Voeg cases toe via de backoffice
                    </div>
                </div>
            </div>
        </section>

        <!-- ============ GROWTH (Total brand growth — grote afbeelding links + tekst rechts) ============ -->
        <section data-section="growth" class="px-6 pb-24 lg:px-[59px]">
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-2 lg:gap-16">
                <!-- Left: large image -->
                <div class="relative h-[60vw] overflow-hidden rounded-[20px] lg:h-[1048px] lg:rounded-[30px]">
                    <video
                        v-if="growthImage && growthImage.type === 'video'"
                        :src="growthImage.url"
                        autoplay
                        loop
                        muted
                        playsinline
                        class="h-full w-full object-cover"
                    />
                    <img
                        v-else-if="growthImage"
                        :src="growthImage.url"
                        alt=""
                        class="h-full w-full object-cover"
                    />
                    <div
                        v-else
                        class="flex h-full w-full items-center justify-center bg-neutral-800 text-white/20"
                        style="font-family: 'Avenir', sans-serif;"
                    >
                        Afbeelding toevoegen via backoffice
                    </div>
                    <p
                        v-if="growthClientName"
                        class="absolute left-[59px] top-[66px] text-[27px] leading-none tracking-[-0.03em] text-[#ffc700]"
                        style="font-family: 'Avenir', sans-serif; font-weight: 900; font-style: oblique;"
                    >
                        {{ growthClientName }}
                    </p>

                    <div
                        v-if="growthTags && growthTags.length > 0"
                        class="absolute bottom-8 right-8 flex flex-wrap justify-end gap-2"
                    >
                        <span
                            v-for="tag in growthTags.slice(0, 3)"
                            :key="tag"
                            class="rounded-[40px] border border-white px-4 py-1.5 text-[14px] leading-[24px] tracking-[-0.01em] text-white"
                            style="font-family: 'Avenir', sans-serif;"
                        >
                            {{ tag }}
                        </span>
                    </div>
                </div>

                <!-- Right: title + body -->
                <div class="flex flex-col justify-start pt-8">
                    <h2
                        class="mb-8 text-[clamp(32px,3vw,40px)] leading-[1.0] tracking-[-0.03em] text-[#ffc700]"
                        style="font-family: 'Avenir', sans-serif; font-weight: 900; font-style: oblique;"
                    >
                        {{ growthTitleLine1 }}<br />{{ growthTitleLine2 }}
                    </h2>
                    <p
                        class="max-w-[425px] text-[20px] leading-[1.45] tracking-[-0.03em] text-white"
                        style="font-family: 'Avenir', sans-serif;"
                    >
                        {{ growthBody }}
                    </p>
                </div>
            </div>
        </section>

        <!-- ============ SERVICES (sticky scroll: Brand / Experience / Employee) ============ -->
        <div ref="servicesWrapper" :style="{ height: wrapperHeight }">
            <!-- Mobile: stacked list -->
            <section
                v-if="isMobile"
                data-section="services"
                class="bg-[#0c0c0c] px-6 py-16"
            >
                <p
                    class="mb-8 text-[26px] leading-none tracking-[-0.03em] text-[#ffc700]"
                    style="font-family: 'Avenir', sans-serif;"
                >
                    Services
                </p>
                <div
                    v-for="service in services"
                    :key="service.name"
                    class="mb-12 last:mb-0"
                >
                    <span
                        class="block text-white"
                        style="font-family: 'Avenir', sans-serif; font-weight: 900; font-style: oblique; font-size: clamp(48px, 14vw, 86px); line-height: 1.05; letter-spacing: -0.03em;"
                    >
                        {{ service.name }}
                    </span>
                    <p
                        class="mt-4 text-[18px] leading-[1.55] tracking-[-0.01em] text-white"
                        style="font-family: 'Avenir', sans-serif;"
                    >
                        {{ service.description }}
                    </p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <span
                            v-for="tag in service.tags"
                            :key="tag"
                            class="rounded-[40px] border border-white px-4 py-1.5 text-white"
                            style="font-family: 'Avenir', sans-serif; font-size: 14px; line-height: 24px; letter-spacing: -0.14px;"
                        >
                            {{ tag }}
                        </span>
                    </div>
                </div>
            </section>

            <!-- Desktop: sticky scroll -->
            <section
                v-else
                ref="servicesSection"
                data-section="services"
                class="grid grid-cols-2 bg-[#0c0c0c] px-[59px] py-24"
                :style="{ position: 'sticky', top: stickyTop }"
            >
                <!-- Left: label + service names -->
                <div>
                    <p
                        class="mb-6 text-[26px] leading-none tracking-[-0.03em] text-[#ffc700]"
                        style="font-family: 'Avenir', sans-serif;"
                    >
                        Services
                    </p>
                    <div class="flex flex-col">
                        <div
                            v-for="(service, i) in services"
                            :key="service.name"
                            class="transition-opacity duration-500"
                            :style="{ opacity: activeServiceIndex === i ? 1 : 0.2 }"
                        >
                            <span
                                class="block text-white"
                                style="font-family: 'Avenir', sans-serif; font-weight: 900; font-style: oblique; font-size: 86px; line-height: 1.05; letter-spacing: -0.03em;"
                            >
                                {{ service.name }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Right: active service description + tags -->
                <div class="flex items-center pl-16">
                    <template v-if="services && services[activeServiceIndex]">
                        <div>
                            <p
                                class="mb-6 max-w-[559px] text-[22px] leading-[32px] tracking-[-0.01em] text-white"
                                style="font-family: 'Avenir', sans-serif;"
                            >
                                {{ services[activeServiceIndex].description }}
                            </p>
                            <div class="flex flex-wrap gap-2">
                                <span
                                    v-for="tag in services[activeServiceIndex].tags"
                                    :key="tag"
                                    class="rounded-[40px] border border-white px-4 py-1.5 text-white"
                                    style="font-family: 'Avenir', sans-serif; font-size: 14px; line-height: 24px; letter-spacing: -0.14px;"
                                >
                                    {{ tag }}
                                </span>
                            </div>
                        </div>
                    </template>
                </div>
            </section>
        </div>

        <!-- ============ ANCIENT ("We're keeping ancient actual" — full-bleed image + tekst rechts) ============ -->
        <section data-section="ancient" class="relative min-h-[500px] overflow-hidden lg:min-h-[800px]">
            <!-- Background media -->
            <video
                v-if="ancientImage && ancientImage.match(/\.(mp4|webm|mov)(\?|$)/i)"
                :src="ancientImage"
                autoplay
                muted
                loop
                playsinline
                class="absolute inset-0 h-full w-full object-cover"
            />
            <img
                v-else-if="ancientImage"
                :src="ancientImage"
                alt=""
                class="absolute inset-0 h-full w-full object-cover"
            />
            <div
                v-else
                class="absolute inset-0 bg-neutral-900"
            />

            <!-- Gradient overlay -->
            <div
                class="absolute inset-0"
                style="background: linear-gradient(102deg, rgba(0, 0, 0, 0.00) 32.16%, rgba(0, 0, 0, 0.40) 64.46%);"
            />

            <!-- Content (right side on desktop, centered on mobile) -->
            <div class="relative flex min-h-[400px] items-center justify-center px-6 py-16 lg:min-h-[800px] lg:justify-end lg:pl-[59px] lg:pr-[120px] lg:py-24">
                <div class="w-full text-left lg:w-[40%]">
                    <h2
                        class="mb-8 text-[clamp(28px,2.5vw,40px)] leading-[1.0] tracking-[-0.03em] text-[#ffc700]"
                        style="font-family: 'Avenir', sans-serif; font-weight: 900; font-style: oblique;"
                    >
                        {{ ancientTitleLine1 }}<br />{{ ancientTitleLine2 }}
                    </h2>
                    <p
                        class="max-w-[347px] text-[18px] leading-[1.55] tracking-[-0.03em] text-white"
                        style="font-family: 'Avenir', sans-serif;"
                    >
                        {{ ancientBody }}
                    </p>
                </div>
            </div>
        </section>

    </div>
    <!-- end dark block -->

    <!-- ============ INTERNATIONAL (gele sectie: titel + body + Join-knop, daaronder team-foto's) ============ -->
    <section data-section="international" class="bg-[#ffc700] px-6 pb-0 pt-16 lg:px-[59px] lg:pt-24">
        <div class="items-start pb-16" :style="internationalGridStyle">
            <!-- Left: big title -->
            <h2
                class="text-[clamp(60px,8vw,150px)] leading-[0.87] tracking-[-0.04em] text-black"
                style="font-family: 'Avenir', sans-serif; font-weight: 900; font-style: oblique;"
            >
                {{ internationalTitleLine1 }}<br />{{ internationalTitleLine2 }}<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" style="display:inline-block;vertical-align:middle;width:56px;height:56px;margin-left:8px;position:relative;top:-4px;flex-shrink:0;"><circle cx="12" cy="12" r="10"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/><path d="M2 12h20"/></svg>
            </h2>

            <!-- Right: description + join button -->
            <div class="pt-0 lg:pt-4" style="max-width: 400px;">
                <p
                    v-for="(paragraph, i) in (internationalBody ?? '').split('\n\n')"
                    :key="i"
                    class="mb-6 text-[18px] leading-[1.55] tracking-[-0.01em] text-black"
                    style="font-family: 'Avenir', sans-serif;"
                >
                    {{ paragraph }}
                </p>

                <Link
                    :href="joinTeamHref ?? '/careers'"
                    class="mt-2 inline-flex h-[50px] items-center justify-center rounded-[30px] px-6 text-[16px] tracking-[-0.03em] text-black transition-opacity hover:opacity-60"
                    style="border: 0.5px solid black; font-family: 'Avenir', sans-serif;"
                >
                    {{ joinTeamText }}
                </Link>
            </div>
        </div>

        <!-- ============ TEAM PHOTOS (polaroid-grid onderaan de gele sectie) ============ -->
        <div
            v-if="teamPhotos && teamPhotos.length > 0"
            data-section="team-photos"
            class="flex items-end gap-3 overflow-x-auto pt-12 pb-10 -mx-6 lg:overflow-x-hidden lg:gap-4 lg:pt-14 lg:pb-12 lg:-mx-[59px]"
        >
            <div
                v-for="(photo, i) in teamPhotos"
                :key="i"
                class="flex-1 overflow-hidden rounded-[7px]"
                :style="{
                    transform: `rotate(${PHOTO_ROTATIONS[i % PHOTO_ROTATIONS.length]}deg) translateY(${PHOTO_OFFSETS[i % PHOTO_OFFSETS.length]}px)`,
                    minWidth: isMobile ? '120px' : '0',
                    aspectRatio: '3/4',
                }"
            >
                <video
                    v-if="photo.match(/\.(mp4|webm|mov)(\?|$)/i)"
                    :src="photo"
                    autoplay
                    muted
                    loop
                    playsinline
                    class="h-full w-full object-cover"
                />
                <img
                    v-else
                    :src="photo"
                    alt=""
                    class="h-full w-full object-cover"
                />
            </div>
        </div>
    </section>

    <SiteFooter />
</template>
