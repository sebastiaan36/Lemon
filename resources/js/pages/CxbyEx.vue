<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, ref, onMounted, onUnmounted } from 'vue';
import CmsImage from '@/components/CmsImage.vue';
import FloatingPill from '@/components/FloatingPill.vue';
import HlsVideo from '@/components/HlsVideo.vue';
import SiteFooter from '@/components/SiteFooter.vue';
import SiteHeader from '@/components/SiteHeader.vue';
import { useLightBackgroundBehindHeader } from '@/composables/useLightBackgroundBehindHeader';

type Step = {
    number: string;
    title: string;
    body: string;
};

type BrandLogo = {
    logo: string;
    name: string;
};

const props = defineProps<{
    seoTitle?: string | null;
    metaDescription?: string | null;
    heroBgImage?: string | null;
    heroSubtitle?: string | null;
    narrativeText?: string | null;
    caseBgImage?: string | null;
    caseBodyText?: string | null;
    caseClientName?: string | null;
    caseTags?: string[];
    bodyCol1?: string | null;
    bodyCol2?: string | null;
    quoteBgImage?: string | null;
    quoteText?: string | null;
    quoteAuthor?: string | null;
    steps?: Step[];
    checklistImage?: string | null;
    checklistButtonText?: string | null;
    checklistHref?: string | null;
    brandsTitleLine1?: string | null;
    brandsTitleLine2?: string | null;
    brandLogos?: BrandLogo[];
}>();

// Figma-fallbacks zolang het CMS (nog) geen eigen beelden heeft.
const GIJS_AVATAR = '/figma-assets/gijs-avatar.png';

// Menu-icoon en logo: zwart op geel, wit op de video en de zwarte secties.
const navOnLightBackground = useLightBackgroundBehindHeader(false);

// ─── Zwevende pills ─────────────────────────────────────────────────────────
// "Tell us" na een stukje scrollen; zodra de stappen-sectie in beeld komt wisselt
// hij met de checklist-pill. Bij de footer verdwijnen beide.
const stepsRef = ref<HTMLElement | null>(null);
const pastHero = ref(false);
const inChecklistZone = ref(false);
const footerVisible = ref(false);

function updatePills(): void {
    pastHero.value = window.scrollY > 160;
    const steps = stepsRef.value;
    inChecklistZone.value =
        !!steps && steps.getBoundingClientRect().top < window.innerHeight * 0.6;
    const footer = document.getElementById('contact');
    footerVisible.value =
        !!footer && footer.getBoundingClientRect().top < window.innerHeight;
}

const tellUsVisibility = computed(() =>
    pastHero.value &&
    !footerVisible.value &&
    !(inChecklistZone.value && !!props.checklistButtonText)
        ? 1
        : 0,
);
const checklistVisibility = computed(() =>
    inChecklistZone.value && !footerVisible.value ? 1 : 0,
);

onMounted(() => {
    updatePills();
    window.addEventListener('scroll', updatePills, { passive: true });
    window.addEventListener('resize', updatePills, { passive: true });
});

onUnmounted(() => {
    window.removeEventListener('scroll', updatePills);
    window.removeEventListener('resize', updatePills);
});
const FALLBACK_BRAND_LOGOS: BrandLogo[] = [
    { logo: '/figma-assets/logo-vattenfall.svg', name: 'Vattenfall' },
    { logo: '/figma-assets/logo-cortina.svg', name: 'Cortina' },
    { logo: '/figma-assets/logo-loods5.svg', name: 'Loods 5' },
    { logo: '/figma-assets/logo-justeat.svg', name: 'Just Eat' },
];
const resolvedBrandLogos = computed(() =>
    props.brandLogos && props.brandLogos.length > 0
        ? props.brandLogos
        : FALLBACK_BRAND_LOGOS,
);

function scrollDown() {
    document
        .getElementById('cxbyex-content')
        ?.scrollIntoView({ behavior: 'smooth' });
}

function isVideo(url: string | null | undefined) {
    return !!url && /\.(mp4|webm|ogg|mov|m3u8)(\?.*)?$/i.test(url);
}

// ─── Hero scroll-indicator: gele ring vult zich met de scrollvoortgang (zelfde als home) ───
const heroProgress = ref(0);
const scrollIndicatorOpacity = ref(1);

function updateHeroProgress(): void {
    const p = Math.max(0, Math.min(window.scrollY / window.innerHeight, 1));
    heroProgress.value = p;
    // Fade uit over de tweede helft van de hero-scroll.
    scrollIndicatorOpacity.value = Math.max(
        0,
        Math.min(1, 1 - (p - 0.5) / 0.4),
    );
}

// ─── Brand narrative: tekst scrollt 1:1 door een vaste fade-band (zelfde effect als de hero-bodytekst op home/about) ───
const narrativeRef = ref<HTMLElement | null>(null);
const narrativeTextRef = ref<HTMLElement | null>(null);
const narrativeTextHeight = ref(0);
const narrativeTranslate = ref(0);

// Fade aan boven- en onderkant van de band; solide in het midden.
const NARRATIVE_GRADIENT =
    'linear-gradient(180deg, transparent 0%, rgba(0, 0, 0, 0.35) 24%, black 42%, black 58%, rgba(0, 0, 0, 0.35) 76%, transparent 100%)';

function measureNarrative(): void {
    if (narrativeTextRef.value) {
        narrativeTextHeight.value = narrativeTextRef.value.offsetHeight;
    }
}

function updateNarrative(): void {
    const el = narrativeRef.value;

    if (!el) {
        return;
    }

    const rect = el.getBoundingClientRect();
    const scrollRange = rect.height - window.innerHeight;

    if (scrollRange <= 0) {
        narrativeTranslate.value = 0;

        return;
    }

    // rect.top loopt van 0 (net vastgepind) → -scrollRange; tekst beweegt 1:1 omhoog door de band.
    const scrolled = Math.max(0, Math.min(scrollRange, -rect.top));
    narrativeTranslate.value = scrollRange / 2 - scrolled;
}

function onNarrativeScroll(): void {
    updateNarrative();
    updateHeroProgress();
}

function onNarrativeResize(): void {
    measureNarrative();
    updateNarrative();
    updateHeroProgress();
}

onMounted(() => {
    measureNarrative();
    updateNarrative();
    updateHeroProgress();
    window.addEventListener('scroll', onNarrativeScroll, { passive: true });
    window.addEventListener('resize', onNarrativeResize, { passive: true });
});

onUnmounted(() => {
    window.removeEventListener('scroll', onNarrativeScroll);
    window.removeEventListener('resize', onNarrativeResize);
});
</script>

<template>
    <Head>
        <title>{{ seoTitle || 'CXbyEX — Lemon Scented Tea' }}</title>
        <meta
            v-if="metaDescription"
            name="description"
            :content="metaDescription"
        />
    </Head>

    <div class="bg-[#ffc700]">
        <SiteHeader :nav-on-yellow="navOnLightBackground" />

        <!-- ============ HERO ============ -->
        <section
            class="relative flex h-svh flex-col items-center justify-center overflow-hidden md:h-screen"
        >
            <HlsVideo
                v-if="isVideo(heroBgImage)"
                :src="heroBgImage!"
                autoplay
                muted
                loop
                playsinline
                class="absolute inset-0 h-full w-full object-cover"
            />
            <CmsImage
                priority
                v-else-if="heroBgImage"
                :src="heroBgImage"
                alt=""
                class="absolute inset-0 h-full w-full object-cover"
            />
            <div class="absolute inset-0 bg-black/40" />

            <div
                class="relative z-10 flex flex-col items-center px-5 text-center md:px-[59px]"
            >
                <h1
                    class="font-avenir text-[clamp(64px,20vw,100px)] leading-none tracking-[-0.03em] text-[#ffc700] lg:text-[150px]"
                    style="font-weight: 900; font-style: oblique"
                >
                    CXbyEX
                </h1>
                <p
                    v-if="heroSubtitle"
                    class="mt-6 max-w-[840px] font-avenir text-[18px] leading-[1.3] tracking-[-0.02em] text-white md:mt-8 md:text-[22px] lg:text-[29px]"
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
                            <feFlood
                                flood-opacity="0"
                                result="BackgroundImageFix"
                            />
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
                class="sticky top-0 flex h-screen items-center justify-center overflow-hidden px-6 md:px-[59px]"
                :style="{
                    maskImage: NARRATIVE_GRADIENT,
                    WebkitMaskImage: NARRATIVE_GRADIENT,
                }"
            >
                <p
                    ref="narrativeTextRef"
                    class="mx-auto max-w-[971px] text-center font-avenir text-[26px] leading-[36px] tracking-[-0.03em] text-black md:text-[38px] md:leading-[50px] lg:text-[50px] lg:leading-[60px]"
                    :style="{
                        transform: `translateY(${narrativeTranslate}px)`,
                    }"
                >
                    {{ narrativeText }}
                </p>
            </div>
        </section>

        <!-- ============ CASE STUDY CARD ============ -->
        <section
            class="mx-5 overflow-hidden rounded-[24px] md:mx-[59px] md:rounded-[30px] lg:mx-[95px]"
        >
            <div
                class="relative flex min-h-[560px] flex-col justify-between gap-10 bg-black px-6 py-12 md:min-h-[700px] md:gap-[60px] md:px-[60px] md:py-[80px] lg:min-h-[1048px] lg:px-[80px]"
            >
                <HlsVideo
                    v-if="isVideo(caseBgImage)"
                    :src="caseBgImage!"
                    autoplay
                    muted
                    loop
                    playsinline
                    class="absolute inset-0 h-full w-full object-cover"
                />
                <CmsImage
                    v-else-if="caseBgImage"
                    :src="caseBgImage"
                    alt=""
                    class="absolute inset-0 h-full w-full object-cover"
                />
                <div class="absolute inset-0 bg-black/30" />

                <!-- Bodytekst: links, rond het verticale midden van de kaart (zoals Figma) -->
                <p
                    v-if="caseBodyText"
                    class="relative z-10 max-w-[573px] font-avenir text-[20px] leading-[1.4] tracking-[-0.02em] text-white md:text-[26px] lg:mt-auto lg:text-[32px]"
                >
                    {{ caseBodyText }}
                </p>

                <!-- Case example: rechtsonder -->
                <div
                    class="relative z-10 flex flex-col gap-3 lg:mt-auto lg:items-start lg:self-end"
                >
                    <p
                        class="font-avenir text-[18px] leading-[32px] tracking-[-0.54px] text-white"
                    >
                        Case example
                    </p>
                    <p
                        v-if="caseClientName"
                        class="font-avenir text-[24px] leading-[1.1] tracking-[-0.03em] text-[#ffc700] md:text-[27px]"
                        style="font-weight: 900; font-style: oblique"
                    >
                        {{ caseClientName }}
                    </p>
                    <div
                        v-if="caseTags && caseTags.length"
                        class="flex flex-wrap gap-3"
                    >
                        <span
                            v-for="tag in caseTags"
                            :key="tag"
                            class="flex h-[36px] items-center rounded-full border border-white px-4 font-avenir text-[14px] tracking-[-0.14px] text-white"
                        >
                            {{ tag }}
                        </span>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============ TWO COLUMN BODY TEXT ============ -->
        <section
            v-if="bodyCol1 || bodyCol2"
            class="grid gap-8 px-5 py-16 md:gap-[40px] md:px-[59px] md:py-[100px] lg:grid-cols-[minmax(0,561fr)_minmax(0,646fr)] lg:gap-[5.9vw] lg:pt-[10vw] lg:pr-[9.8vw] lg:pb-[5.8vw] lg:pl-[10.7vw]"
        >
            <p
                v-if="bodyCol1"
                class="font-avenir text-[18px] leading-[28px] tracking-[-0.02em] text-black md:text-[20px] md:leading-[32px] lg:text-[24px]"
            >
                {{ bodyCol1 }}
            </p>
            <p
                v-if="bodyCol2"
                class="font-avenir text-[18px] leading-[28px] tracking-[-0.02em] text-black md:text-[20px] md:leading-[32px] lg:text-[24px]"
            >
                {{ bodyCol2 }}
            </p>
        </section>

        <!-- ============ QUOTE SECTION ============ -->
        <section class="relative" style="min-height: 600px">
            <div class="absolute inset-0 overflow-hidden">
                <HlsVideo
                    v-if="isVideo(quoteBgImage)"
                    :src="quoteBgImage!"
                    autoplay
                    muted
                    loop
                    playsinline
                    class="absolute inset-0 h-full w-full object-cover"
                />
                <CmsImage
                    v-else-if="quoteBgImage"
                    :src="quoteBgImage"
                    alt=""
                    class="absolute inset-0 h-full w-full object-cover"
                />
                <div class="absolute inset-0 bg-black/30" />
            </div>

            <div
                class="relative z-10 flex min-h-[600px] flex-col items-center justify-center px-5 py-[100px] text-center md:px-[59px] md:py-[140px] lg:min-h-[1054px]"
            >
                <blockquote
                    v-if="quoteText"
                    class="max-w-[1280px] font-avenir text-[34px] leading-[1.05] tracking-[-0.03em] text-[#ffc700] md:text-[46px] lg:text-[65px]"
                    style="font-weight: 900; font-style: oblique"
                >
                    <span class="text-white">'</span>{{ quoteText
                    }}<span class="text-white">'</span>
                </blockquote>
                <p
                    v-if="quoteAuthor"
                    class="mt-8 font-avenir text-[18px] leading-[32px] tracking-[-0.54px] text-white"
                >
                    {{ quoteAuthor }}
                </p>
            </div>
        </section>

        <!-- ============ FROM STORY TO STRATEGY TO ACTION ============ -->
        <section
            ref="stepsRef"
            class="relative flex flex-col gap-12 px-5 pt-[90px] pb-[120px] md:gap-[80px] md:px-[59px] md:py-[120px] lg:flex-row lg:gap-0 lg:pt-[9vw] lg:pr-[12.9vw] lg:pb-[10vw] lg:pl-[8.5vw]"
        >
            <div
                class="lg:w-[29vw] lg:shrink-0 lg:pr-[2vw] xl:w-[37.4vw] xl:pr-[8vw]"
            >
                <div class="flex items-start gap-5">
                    <h2
                        class="font-avenir text-[44px] leading-[1.0] tracking-[-0.03em] text-black md:text-[56px] lg:text-[68px] lg:leading-[68px]"
                        style="font-weight: 900; font-style: oblique"
                    >
                        From story<br />to strategy<br />to action
                    </h2>

                    <svg
                        class="mt-[10px] h-[63px] w-[67px] shrink-0"
                        viewBox="0 0 67 63"
                        fill="none"
                    >
                        <path
                            d="M42.1833 51.1582L62.2271 31.1145L41.2859 10.1732"
                            stroke="black"
                            stroke-width="6"
                        />
                        <path
                            d="M61.0288 31.1126H2.93882e-05"
                            stroke="black"
                            stroke-width="6"
                        />
                    </svg>
                </div>

                <p
                    class="mt-8 max-w-[425px] font-avenir text-[18px] leading-[28px] tracking-[-0.02em] text-black md:mt-[35px] md:text-[20px] md:leading-[32px] lg:text-[24px]"
                >
                    We partner with leadership teams to transform brand ambition
                    into lived behavior turning culture into a competitive
                    advantage.
                </p>
            </div>

            <div
                v-if="steps && steps.length"
                class="min-w-0 flex-1 lg:max-w-[676px] lg:pt-[20px]"
            >
                <div class="divide-y divide-black/15">
                    <div
                        v-for="step in steps"
                        :key="step.number"
                        class="py-[24px] first:pt-0 lg:pt-[28px] lg:pb-[20px]"
                    >
                        <div class="flex items-start gap-5">
                            <span
                                class="flex h-[39px] w-[61px] shrink-0 items-center justify-center rounded-full bg-black font-avenir text-[18px] tracking-[-0.54px] text-white"
                            >
                                {{ step.number }}
                            </span>
                            <div class="pt-[2px]">
                                <h3
                                    class="font-avenir text-[20px] leading-[28px] tracking-[-0.01em] text-black md:text-[24px] md:leading-[32px] lg:text-[28px]"
                                >
                                    {{ step.title }}
                                </h3>
                                <p
                                    class="mt-1 font-avenir text-[16px] leading-[22px] tracking-[-0.01em] text-[#101010] md:text-[18px] md:leading-[24px] lg:mt-[17px] lg:max-w-[557px] lg:text-[20px]"
                                >
                                    {{ step.body }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============ LEADING BRANDS ============ -->
        <section
            class="bg-black px-5 py-[100px] text-center md:px-[59px] md:py-[140px] lg:pt-[11.6vw] lg:pb-[11.8vw]"
        >
            <h2
                class="mb-12 font-avenir text-[clamp(30px,11vw,80px)] leading-[1.0] tracking-[-0.03em] whitespace-nowrap md:mb-[80px] lg:text-[min(150px,9.15vw)]"
                style="font-weight: 900; font-style: oblique"
            >
                <span class="text-white"
                    >{{ brandsTitleLine1 || 'Leading brands' }}<br
                /></span>
                <span class="text-[#ffc700]">{{
                    brandsTitleLine2 || 'choose Lemon'
                }}</span>
            </h2>

            <!-- Figma: gele klantlogo's in een raster onder de titel -->
            <ul
                class="mx-auto mb-[60px] flex max-w-[1100px] flex-wrap items-center justify-center gap-x-8 gap-y-10 md:mb-[80px] lg:gap-x-[60px] lg:gap-y-[50px]"
            >
                <li
                    v-for="(brand, i) in resolvedBrandLogos"
                    :key="i"
                    class="h-[48px] w-[calc(50%-16px)] max-w-[170px] sm:w-[calc(33.333%-22px)] md:h-[64px] lg:w-[170px]"
                    role="img"
                    :aria-label="brand.name"
                    :style="{
                        backgroundColor: '#ffc700',
                        maskImage: `url(${brand.logo})`,
                        WebkitMaskImage: `url(${brand.logo})`,
                        maskRepeat: 'no-repeat',
                        WebkitMaskRepeat: 'no-repeat',
                        maskPosition: 'center',
                        WebkitMaskPosition: 'center',
                        maskSize: 'contain',
                        WebkitMaskSize: 'contain',
                    }"
                />
            </ul>

            <a
                href="/contact"
                class="inline-flex h-[65px] items-center justify-center rounded-full border border-white px-10 font-avenir text-[22px] tracking-[-0.69px] text-white transition-colors hover:bg-white hover:text-black"
            >
                Get in touch
            </a>
        </section>

        <!-- Zwevende pills (zoals op de andere pagina's): eerst "Tell us about your
             project", vanaf "From story to strategy to action" de checklist. -->
        <FloatingPill
            label="Tell us about your project"
            href="/contact"
            :avatars="[GIJS_AVATAR]"
            :visibility="tellUsVisibility"
            animated
        />
        <FloatingPill
            v-if="checklistButtonText"
            :label="checklistButtonText"
            :href="checklistHref || '#'"
            :visibility="checklistVisibility"
            animated
        >
            <template #leading>
                <span
                    class="relative block h-[60px] w-[60px] shrink-0 md:mr-2 md:ml-[35px] md:h-[73px] md:w-[74px]"
                    aria-hidden="true"
                >
                    <span
                        class="absolute bottom-0 left-0 block h-[90px] w-[74px] origin-bottom-left scale-[0.81] overflow-hidden bg-white shadow-md md:scale-100"
                        aria-hidden="true"
                    >
                        <CmsImage
                            sizes="74px"
                            v-if="checklistImage"
                            :src="checklistImage"
                            alt=""
                            class="absolute inset-0 h-full w-full object-cover"
                        />
                        <template v-else>
                            <img
                                loading="lazy"
                                src="/figma-assets/checklist-bg.jpg"
                                alt=""
                                class="absolute inset-0 h-full w-full object-cover opacity-50"
                            />
                            <span
                                class="absolute inset-x-0 top-[8px] px-1 text-center font-avenir text-[12.3px] leading-[12.3px] tracking-[-0.03em] text-black"
                                style="font-weight: 800"
                            >
                                CX starts with your employees
                            </span>
                            <span
                                class="absolute right-0 bottom-0 left-0 h-[51px] bg-[#ffc700]"
                                style="
                                    mask: url('/figma-assets/checklist-figures.svg')
                                        4px 2px / 62px 48px no-repeat;
                                    -webkit-mask: url('/figma-assets/checklist-figures.svg')
                                        4px 2px / 62px 48px no-repeat;
                                "
                            />
                        </template>
                    </span>
                </span>
            </template>
        </FloatingPill>

        <SiteFooter id="contact" />
    </div>
</template>
