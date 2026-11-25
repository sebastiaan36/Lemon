<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import AboutMediaCard from '@/components/about/AboutMediaCard.vue';
import BrandDimensions from '@/components/about/BrandDimensions.vue';
import type { DimensionItem } from '@/components/about/BrandDimensions.vue';
import CmsImage from '@/components/CmsImage.vue';
import HlsVideo from '@/components/HlsVideo.vue';
import PagePills from '@/components/PagePills.vue';
import SiteFooter from '@/components/SiteFooter.vue';
import SiteHeader from '@/components/SiteHeader.vue';
import VideoHero from '@/components/VideoHero.vue';
import { useLightBackgroundBehindHeader } from '@/composables/useLightBackgroundBehindHeader';
import { isVideoUrl } from '@/lib/scrollAnimation';

type MediaItem = {
    url: string;
    type: string;
};

const PHOTO_ROTATIONS = [-2.5, 1.8, -1.2, 2.2, -1.6];
const PHOTO_OFFSETS = [0, -32, -16, -48, -8];

const props = withDefaults(
    defineProps<{
        seoTitle?: string | null;
        metaDescription?: string | null;
        heroTitle?: string;
        heroDescription?: string;
        heroBgImage?: MediaItem | null;
        credibleTitleLine1?: string;
        credibleTitleLine2?: string;
        credibleBody?: string;
        growthTitleLine1?: string;
        growthTitleLine2?: string;
        growthBody?: string;
        growthImage?: MediaItem | null;
        growthClientName?: string | null;
        growthTags?: string[];
        dimensionsTitle?: string | null;
        dimensionsBody?: string | null;
        dimensionsFormulaTitle?: string | null;
        dimensionsFormulaSubtitle?: string | null;
        dimensionsCenterTitle?: string | null;
        dimensionsCenterSubtitle?: string | null;
        dimensionsItems?: DimensionItem[];
        services?: Array<{ name: string; description: string; tags: string[] }>;
        ancientTitleLine1?: string;
        ancientTitleLine2?: string;
        ancientBody?: string;
        ancientImage?: string | null;
        approachTitle?: string | null;
        approachBody?: string | null;
        narrativeTitle?: string | null;
        narrativeBody?: string | null;
        narrativeButtonText?: string | null;
        narrativeButtonHref?: string | null;
        narrativeImage?: MediaItem | null;
        narrativeClientName?: string | null;
        narrativeTags?: string[];
        internationalTitleLine1?: string;
        internationalTitleLine2?: string;
        internationalBody?: string;
        teamPhotos?: string[];
        joinTeamText?: string;
        joinTeamHref?: string;
    }>(),
    {
        seoTitle: null,
        metaDescription: null,
        heroTitle: 'We are Lemon',
        heroDescription:
            "We compare what we do to the ritual of making tea. The boiling water represents society, while the tea bag holds the essence of your brand. We turn that essence into your credible brand narrative, strong enough to infuse people's lives and create a lasting, refreshing blend that helps your brand grow.",
        heroBgImage: null,
        credibleTitleLine1: 'Credible',
        credibleTitleLine2: 'Creativity',
        credibleBody:
            'In a world overloaded with brand noise, algorithms, and fake news, people are turning away from brands that fail to live up to what they say and do. Only those that wow minds and win hearts by consistently proving their core essence will stay relevant and future-proof.',
        growthTitleLine1: 'Total brand',
        growthTitleLine2: 'growth',
        growthBody:
            "The world's most successful brands don't choose between storytelling and experience. They master both. They capture attention through creativity, build belief through experience, and create products, services and communities people genuinely want to be part of. When every interaction reinforces the same core narrative, brands become credible, distinctive and more likely to grow.\n\nWe call this **Total Brand Growth.**",
        growthImage: null,
        growthClientName: null,
        growthTags: () => [],
        dimensionsTitle: 'The 4 dimensions of Total Brand Growth',
        dimensionsBody:
            'We measure Total Brand Growth across four brand dimensions: Fame, Experience, Proposition and Participation.',
        dimensionsFormulaTitle: 'Lemon’s Credible Creativity Formula',
        dimensionsFormulaSubtitle: 'How brand credibility drives growth',
        dimensionsCenterTitle: 'Brand narrative',
        dimensionsCenterSubtitle: 'Promise & Values',
        dimensionsItems: () => [
            { name: 'Brand', subtitle: 'Advertising, PR & social' },
            { name: 'Employee experience', subtitle: 'Internal teams' },
            { name: 'Proposition', subtitle: 'Core product & NPD' },
            {
                name: 'Customer experience',
                subtitle: 'Key touchpoints & customer care',
            },
        ],
        services: () => [
            {
                name: 'Brand',
                description:
                    'Tell your credible brand story, stand out in your market, and connect with your customers.',
                tags: [
                    'Positioning',
                    'Narrative',
                    'Campaigns',
                    'Visual identity',
                    'Activations',
                ],
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
        approachTitle: 'Our Brand Narrative Approach™',
        approachBody:
            "All great stories share the same structure. From the first campfire tale to the timeless Star Wars saga, every great story has an adversary. No adversary, no story.\n\nAdversarial thinking keeps people hooked. It's human nature to pick a side and root for what matters. When your brand becomes part of that story, they can't look away.",
        narrativeTitle: 'Your Credible Brand narrative',
        narrativeBody:
            'Your Credible Brand Narrative is our mutual creative playground to shape and grow your brand. From advertising to brand experience and employee engagement, everything connects. Creative, coherent, and credible.',
        narrativeButtonText: 'Read even more',
        narrativeButtonHref: '/cases',
        narrativeImage: null,
        narrativeClientName: null,
        narrativeTags: () => [],
        internationalTitleLine1: 'International',
        internationalTitleLine2: 'community',
        internationalBody:
            'Lemon Scented Tea is served hot in every time zone. You can find the Lemons in Amsterdam, Atlanta, London, and Munich. And just as the power of storytelling is universal, so are we.\n\nWe operate as one strong core surrounded by an eclectic community of top notch, like-minded professionals from around the world. All united by a single purpose: to create Credible Creativity.',
        teamPhotos: () => [],
        joinTeamText: 'Join our team',
        joinTeamHref: '/jobs',
    },
);

// Menu-icoon en "Get in touch" zwart op geel, wit op zwart/foto/video (de hero).
const navOnLightBackground = useLightBackgroundBehindHeader(false);

type TextRun = { text: string; strong: boolean };

/**
 * Splitst CMS-tekst in alinea's (lege regel) en markeert **tekst** als nadruk.
 * Geen v-html: alles wordt als tekst gerenderd.
 */
function paragraphs(text: string | null | undefined): TextRun[][] {
    return (text ?? '')
        .split(/\n\s*\n/)
        .map((paragraph) => paragraph.trim())
        .filter(Boolean)
        .map((paragraph) =>
            paragraph
                .split(/(\*\*[^*]+\*\*)/)
                .filter(Boolean)
                .map((part) =>
                    part.startsWith('**') && part.endsWith('**')
                        ? { text: part.slice(2, -2), strong: true }
                        : { text: part, strong: false },
                ),
        );
}

/** "Our Brand Narrative Approach™" → titel + los ™-teken (klein en hoog in Figma). */
const approachTitleParts = computed(() => {
    const title = (props.approachTitle ?? '').trim();

    return title.endsWith('™')
        ? { text: title.slice(0, -1).trimEnd(), trademark: true }
        : { text: title, trademark: false };
});

// ─── Services scroll-state ───────────────────────────────────────────────
const servicesWrapper = ref<HTMLElement | null>(null);
const servicesSection = ref<HTMLElement | null>(null);
const scrollY = ref(0);
const sectionH = ref(0);

const servicesCount = computed(() => props.services?.length ?? 3);
const windowWidth = ref(
    typeof window !== 'undefined' ? window.innerWidth : 1640,
);
const isMobile = computed(() => windowWidth.value < 1024);

const wrapperHeight = computed(() =>
    isMobile.value
        ? 'auto'
        : `calc(${sectionH.value}px + ${servicesCount.value - 1} * 60vh)`,
);

const stickyTop = computed(() => {
    if (isMobile.value) {
        return '0';
    }

    return sectionH.value > 0
        ? `calc(50vh - ${sectionH.value / 2}px)`
        : 'calc(50vh - 200px)';
});

const internationalGridStyle = computed(() =>
    isMobile.value
        ? { display: 'grid', gridTemplateColumns: '1fr', gap: '40px' }
        : { display: 'grid', gridTemplateColumns: '4fr 2fr', gap: '120px' },
);

const activeServiceIndex = computed(() => {
    if (
        !servicesWrapper.value ||
        !props.services?.length ||
        sectionH.value === 0
    ) {
        return 0;
    }

    const wrapper = servicesWrapper.value;
    const stickyTopPx = window.innerHeight / 2 - sectionH.value / 2;
    const scrollRange = wrapper.offsetHeight - sectionH.value;

    if (scrollRange <= 0) {
        return 0;
    }

    // getBoundingClientRect().top + scrollY = stable document offset; using scrollY.value
    // here registers this computed as a reactive scroll dependency.
    const wrapperDocTop = wrapper.getBoundingClientRect().top + window.scrollY;
    const scrolled = scrollY.value + stickyTopPx - wrapperDocTop;
    const progress = Math.max(0, Math.min(1, scrolled / scrollRange));

    return Math.min(
        props.services.length - 1,
        Math.floor(progress * props.services.length),
    );
});

function updateSectionHeight(): void {
    if (servicesSection.value) {
        sectionH.value = servicesSection.value.offsetHeight;
    }
}

function onScroll(): void {
    scrollY.value = window.scrollY;
}

function onResize(): void {
    windowWidth.value = window.innerWidth;
    updateSectionHeight();
}

// ─── Teamfoto's: horizontale carrousel met pijlknoppen (Figma) ─────────────
const teamPhotosTrack = ref<HTMLElement | null>(null);
const teamPhotosCanScrollBack = ref(false);
const teamPhotosCanScrollForward = ref(false);

function updateTeamPhotosScrollState(): void {
    const track = teamPhotosTrack.value;

    if (!track) {
        return;
    }

    teamPhotosCanScrollBack.value = track.scrollLeft > 4;
    teamPhotosCanScrollForward.value =
        track.scrollLeft + track.clientWidth < track.scrollWidth - 4;
}

function scrollTeamPhotos(direction: 1 | -1): void {
    const track = teamPhotosTrack.value;
    const photo = track?.firstElementChild as HTMLElement | null;

    if (!track || !photo) {
        return;
    }

    const gap = parseFloat(getComputedStyle(track).columnGap) || 0;

    track.scrollBy({
        left: direction * (photo.offsetWidth + gap),
        behavior: 'smooth',
    });
}

onMounted(() => {
    updateTeamPhotosScrollState();
    window.addEventListener('resize', updateTeamPhotosScrollState, {
        passive: true,
    });
});

onUnmounted(() => {
    window.removeEventListener('resize', updateTeamPhotosScrollState);
});

onMounted(() => {
    updateSectionHeight();
    scrollY.value = window.scrollY;
    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onResize, { passive: true });
});

onUnmounted(() => {
    window.removeEventListener('scroll', onScroll);
    window.removeEventListener('resize', onResize);
});
</script>

<template>
    <Head :title="seoTitle ?? 'About — Lemon Scented Tea'" />

    <SiteHeader :nav-on-yellow="navOnLightBackground" />

    <!-- ============ HERO (video op de achtergrond, gedeeld met Industries) ============ -->
    <VideoHero
        :title="heroTitle"
        :subtitle="heroDescription"
        :media="heroBgImage"
    />

    <!-- ─── Dark content block ────────────────────────────────────────────── -->
    <div class="bg-black text-white">
        <!-- ============ CREDIBLE CREATIVITY (Figma: titel 100px links, tekst 40px rechts) ============ -->
        <section
            data-section="credible"
            data-pill-section="credible"
            class="grid gap-8 px-6 pt-20 pb-20 lg:grid-cols-[40.2vw_minmax(0,735px)] lg:gap-0 lg:pt-[19.5vw] lg:pr-[6.7vw] lg:pb-[15vw] lg:pl-[8.2vw]"
        >
            <h2
                class="font-avenir text-[clamp(48px,6.1vw,100px)] leading-none font-black tracking-[-0.03em] text-[#ffc700] italic"
            >
                {{ credibleTitleLine1 }}<br />{{ credibleTitleLine2 }}
            </h2>
            <p
                class="font-avenir text-[clamp(22px,2.44vw,40px)] leading-[1.333] tracking-[-0.03em]"
            >
                {{ credibleBody }}
            </p>
        </section>

        <!-- ============ TOTAL BRAND GROWTH (tekst links, beeld rechts) ============ -->
        <section
            data-section="growth"
            data-pill-section="growth"
            class="grid gap-10 px-6 pb-20 lg:grid-cols-[minmax(0,1fr)_49.1vw] lg:gap-[4vw] lg:pr-[4.8vw] lg:pb-[8.5vw] lg:pl-[7.8vw]"
        >
            <div class="lg:max-w-[464px] lg:pt-[9.9vw]">
                <h2
                    class="font-avenir text-[clamp(36px,3.8vw,62px)] leading-none font-black tracking-[-0.03em] text-[#ffc700] italic"
                >
                    {{ growthTitleLine1 }}<br />{{ growthTitleLine2 }}
                </h2>
                <div
                    class="mt-8 space-y-[1.2em] font-avenir text-[clamp(19px,1.71vw,28px)] leading-[1.214] tracking-[-0.03em] lg:mt-[2.6vw]"
                >
                    <p v-for="(runs, i) in paragraphs(growthBody)" :key="i">
                        <template v-for="(run, j) in runs" :key="j">
                            <strong
                                v-if="run.strong"
                                class="font-black italic"
                                >{{ run.text }}</strong
                            ><template v-else>{{ run.text }}</template>
                        </template>
                    </p>
                </div>
            </div>

            <AboutMediaCard
                :media="growthImage"
                :client-name="growthClientName"
                :tags="growthTags"
            />
        </section>
    </div>

    <!-- ============ THE 4 DIMENSIONS (gele sectie met cirkeldiagram) ============ -->
    <section
        data-section="dimensions"
        data-pill-section="dimensions"
        class="grid items-center gap-16 bg-[#ffc700] px-6 py-20 lg:grid-cols-[minmax(0,1fr)_44vw] lg:gap-[6vw] lg:py-[7.6vw] lg:pr-[10.4vw] lg:pl-[8.2vw]"
    >
        <div class="max-w-[500px] text-black">
            <h2
                class="font-avenir text-[clamp(32px,2.93vw,48px)] leading-none font-black tracking-[-0.03em] italic"
            >
                {{ dimensionsTitle }}
            </h2>
            <p
                class="mt-6 font-avenir text-[clamp(18px,1.46vw,24px)] leading-[1.333] tracking-[-0.03em] lg:mt-[1.4vw]"
            >
                {{ dimensionsBody }}
            </p>
        </div>

        <BrandDimensions
            v-if="dimensionsItems && dimensionsItems.length"
            class="mx-auto max-w-[762px]"
            :title="dimensionsFormulaTitle"
            :subtitle="dimensionsFormulaSubtitle"
            :center-title="dimensionsCenterTitle ?? ''"
            :center-subtitle="dimensionsCenterSubtitle"
            :items="dimensionsItems"
        />
    </section>

    <!-- ============ ANCIENT ("We're keeping ancient actual" — full-bleed image + tekst rechts) ============ -->
    <section
        data-section="ancient"
        data-pill-section="ancient"
        class="relative min-h-[500px] overflow-hidden lg:min-h-[800px]"
    >
        <!-- Background media -->
        <HlsVideo
            v-if="isVideoUrl(ancientImage)"
            :src="ancientImage"
            autoplay
            muted
            loop
            playsinline
            class="absolute inset-0 h-full w-full object-cover"
        />
        <CmsImage
            v-else-if="ancientImage"
            :src="ancientImage"
            alt=""
            class="absolute inset-0 h-full w-full object-cover"
        />
        <div v-else class="absolute inset-0 bg-neutral-900" />

        <!-- Gradient overlay -->
        <div
            class="absolute inset-0"
            style="
                background: linear-gradient(
                    102deg,
                    rgba(0, 0, 0, 0) 32.16%,
                    rgba(0, 0, 0, 0.4) 64.46%
                );
            "
        />

        <!-- Content (right side on desktop, centered on mobile) -->
        <div
            class="relative flex min-h-[400px] items-center justify-center px-6 py-16 lg:min-h-[800px] lg:justify-end lg:py-24 lg:pr-[120px] lg:pl-[59px]"
        >
            <div class="w-full text-left lg:w-[40%]">
                <h2
                    class="mb-8 font-avenir text-[clamp(28px,2.5vw,40px)] leading-[1.0] tracking-[-0.03em] text-[#ffc700]"
                    style="font-weight: 900; font-style: oblique"
                >
                    {{ ancientTitleLine1 }}<br />{{ ancientTitleLine2 }}
                </h2>
                <p
                    class="max-w-[347px] font-avenir text-[18px] leading-[1.55] tracking-[-0.03em] text-white"
                >
                    {{ ancientBody }}
                </p>
            </div>
        </div>
    </section>

    <div class="bg-black text-white">
        <!-- ============ OUR BRAND NARRATIVE APPROACH ============ -->
        <section
            data-section="approach"
            data-pill-section="approach"
            class="grid gap-8 px-6 pt-20 pb-16 lg:grid-cols-[42.2vw_minmax(0,693px)] lg:gap-0 lg:pt-[12vw] lg:pr-[6.7vw] lg:pb-[14vw] lg:pl-[6.6vw]"
        >
            <h2
                class="max-w-[643px] font-avenir text-[clamp(48px,6.1vw,100px)] leading-none font-black tracking-[-0.03em] text-[#ffc700] italic"
            >
                {{ approachTitleParts.text
                }}<sup
                    v-if="approachTitleParts.trademark"
                    class="ml-[0.08em] align-[1.35em] text-[0.3em] tracking-normal"
                    >TM</sup
                >
            </h2>
            <div>
                <div
                    class="space-y-[1.2em] font-avenir text-[clamp(19px,1.71vw,28px)] leading-[1.214] tracking-[-0.03em]"
                >
                    <p v-for="(runs, i) in paragraphs(approachBody)" :key="i">
                        {{ runs.map((run) => run.text).join('') }}
                    </p>
                </div>
            </div>
        </section>

        <!-- ============ YOUR CREDIBLE BRAND NARRATIVE (tekst links, beeld rechts) ============ -->
        <section
            data-section="narrative"
            data-pill-section="narrative"
            class="grid gap-10 px-6 pb-20 lg:grid-cols-[minmax(0,1fr)_49.1vw] lg:gap-[4vw] lg:pr-[4.8vw] lg:pb-[10vw] lg:pl-[6.6vw]"
        >
            <div class="lg:max-w-[547px] lg:pt-[11.4vw]">
                <h2
                    class="font-avenir text-[clamp(36px,3.8vw,62px)] leading-none font-black tracking-[-0.03em] text-[#ffc700] italic"
                >
                    {{ narrativeTitle }}
                </h2>
                <div
                    class="mt-8 space-y-[1.2em] font-avenir text-[clamp(19px,1.71vw,28px)] leading-[1.214] tracking-[-0.03em] lg:mt-[2.4vw]"
                >
                    <p v-for="(runs, i) in paragraphs(narrativeBody)" :key="i">
                        {{ runs.map((run) => run.text).join('') }}
                    </p>
                </div>
                <Link
                    v-if="narrativeButtonText && narrativeButtonHref"
                    :href="narrativeButtonHref"
                    class="mt-8 inline-flex h-[50px] items-center rounded-[30px] border-[0.5px] border-white px-8 font-avenir text-[18px] tracking-[-0.03em] transition-colors hover:border-[#ffc700] hover:text-[#ffc700] md:h-[57px] md:text-[20px]"
                >
                    {{ narrativeButtonText }}
                </Link>
            </div>

            <AboutMediaCard
                :media="narrativeImage"
                :client-name="narrativeClientName"
                :tags="narrativeTags"
            />
        </section>

        <!-- ============ SERVICES (sticky scroll: Brand / Experience / Employee) ============ -->
        <div ref="servicesWrapper" :style="{ height: wrapperHeight }">
            <!-- Mobile: stacked list -->
            <section
                v-if="isMobile"
                data-section="services"
                data-pill-section="services"
                class="bg-black px-6 py-16"
            >
                <p
                    class="mb-8 font-avenir text-[26px] leading-none tracking-[-0.03em] text-[#ffc700]"
                >
                    Services
                </p>
                <div
                    v-for="service in services"
                    :key="service.name"
                    class="mb-12 last:mb-0"
                >
                    <span
                        class="block font-avenir text-white"
                        style="
                            font-weight: 900;
                            font-style: oblique;
                            font-size: clamp(48px, 14vw, 86px);
                            line-height: 1.05;
                            letter-spacing: -0.03em;
                        "
                    >
                        {{ service.name }}
                    </span>
                    <p
                        class="mt-4 font-avenir text-[18px] leading-[1.55] tracking-[-0.01em] text-white"
                    >
                        {{ service.description }}
                    </p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <span
                            v-for="tag in service.tags"
                            :key="tag"
                            class="rounded-[40px] border border-white px-4 py-1.5 font-avenir text-white"
                            style="
                                font-size: 14px;
                                line-height: 24px;
                                letter-spacing: -0.14px;
                            "
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
                data-pill-section="services"
                class="grid grid-cols-2 bg-black px-[8.2vw] py-24"
                :style="{ position: 'sticky', top: stickyTop }"
            >
                <!-- Left: label + service names -->
                <div>
                    <p
                        class="mb-6 font-avenir text-[26px] leading-none tracking-[-0.03em] text-[#ffc700]"
                    >
                        Services
                    </p>
                    <div class="flex flex-col">
                        <div
                            v-for="(service, i) in services"
                            :key="service.name"
                            class="transition-opacity duration-500"
                            :style="{
                                opacity: activeServiceIndex === i ? 1 : 0.2,
                            }"
                        >
                            <span
                                class="block font-avenir text-white"
                                style="
                                    font-weight: 900;
                                    font-style: oblique;
                                    font-size: 86px;
                                    line-height: 1.05;
                                    letter-spacing: -0.03em;
                                "
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
                                class="mb-6 max-w-[559px] font-avenir text-[22px] leading-[32px] tracking-[-0.01em] text-white"
                            >
                                {{ services[activeServiceIndex].description }}
                            </p>
                            <div class="flex flex-wrap gap-2">
                                <span
                                    v-for="tag in services[activeServiceIndex]
                                        .tags"
                                    :key="tag"
                                    class="rounded-[40px] border border-white px-4 py-1.5 font-avenir text-white"
                                    style="
                                        font-size: 14px;
                                        line-height: 24px;
                                        letter-spacing: -0.14px;
                                    "
                                >
                                    {{ tag }}
                                </span>
                            </div>
                        </div>
                    </template>
                </div>
            </section>
        </div>
    </div>

    <!-- ============ INTERNATIONAL (gele sectie: titel + body + Join-knop, daaronder team-foto's) ============ -->
    <section
        data-section="international"
        data-pill-section="international"
        class="bg-[#ffc700] px-6 pt-16 pb-0 lg:px-[59px] lg:pt-24"
    >
        <div class="items-start pb-16" :style="internationalGridStyle">
            <!-- Left: big title -->
            <h2
                class="font-avenir text-[clamp(60px,8vw,150px)] leading-[0.87] tracking-[-0.04em] text-black"
                style="font-weight: 900; font-style: oblique"
            >
                {{ internationalTitleLine1 }}<br />{{ internationalTitleLine2
                }}<img
                    loading="lazy"
                    src="/figma-assets/globe.svg"
                    alt=""
                    width="84.4463"
                    height="84.4463"
                    class="ml-[0.1em] inline-block size-[0.56em] align-middle"
                />
            </h2>

            <!-- Right: description + join button -->
            <div class="pt-0 lg:pt-4" style="max-width: 400px">
                <p
                    v-for="(paragraph, i) in (internationalBody ?? '').split(
                        '\n\n',
                    )"
                    :key="i"
                    class="mb-6 font-avenir text-[18px] leading-[1.55] tracking-[-0.01em] text-black"
                >
                    {{ paragraph }}
                </p>

                <Link
                    :href="joinTeamHref ?? '/jobs'"
                    class="mt-2 inline-flex h-[50px] items-center justify-center rounded-[30px] px-6 font-avenir text-[16px] tracking-[-0.03em] text-black transition-opacity hover:opacity-60"
                    style="border: 0.5px solid black"
                >
                    {{ joinTeamText }}
                </Link>
            </div>
        </div>

        <!-- ============ TEAM PHOTOS (polaroid-grid onderaan de gele sectie) ============ -->
        <div
            v-if="teamPhotos && teamPhotos.length > 0"
            ref="teamPhotosTrack"
            data-section="team-photos"
            data-pill-section="team-photos"
            class="-mx-6 flex snap-x snap-mandatory items-end gap-3 overflow-x-auto px-6 pt-12 pb-10 [scrollbar-width:none] lg:-mx-[59px] lg:gap-4 lg:px-[59px] lg:pt-14 lg:pb-12 [&::-webkit-scrollbar]:hidden"
            @scroll.passive="updateTeamPhotosScrollState"
        >
            <div
                v-for="(photo, i) in teamPhotos"
                :key="i"
                class="w-[120px] shrink-0 snap-start overflow-hidden rounded-[7px] lg:w-[18.5vw]"
                :style="{
                    transform: `rotate(${PHOTO_ROTATIONS[i % PHOTO_ROTATIONS.length]}deg) translateY(${PHOTO_OFFSETS[i % PHOTO_OFFSETS.length]}px)`,
                    aspectRatio: '3/4',
                }"
            >
                <HlsVideo
                    v-if="isVideoUrl(photo)"
                    :src="photo"
                    autoplay
                    muted
                    loop
                    playsinline
                    class="h-full w-full object-cover"
                />
                <CmsImage
                    sizes="(min-width: 1024px) 25vw, 45vw"
                    v-else
                    :src="photo"
                    :alt="`Team Lemon — foto ${i + 1}`"
                    class="h-full w-full object-cover"
                />
            </div>
        </div>

        <!-- Figma: ronde pijlknoppen onder de foto's, rechts uitgelijnd -->
        <div
            v-if="teamPhotosCanScrollBack || teamPhotosCanScrollForward"
            class="flex justify-end gap-2 pb-10 lg:pb-12"
        >
            <button
                v-for="direction in [-1, 1] as const"
                :key="direction"
                type="button"
                :aria-label="
                    direction === -1 ? 'Previous photos' : 'Next photos'
                "
                :disabled="
                    direction === -1
                        ? !teamPhotosCanScrollBack
                        : !teamPhotosCanScrollForward
                "
                class="flex size-[44px] items-center justify-center rounded-full border border-black/60 text-black transition-[opacity,background-color] hover:bg-black hover:text-[#ffc700] disabled:pointer-events-none disabled:opacity-30"
                @click="scrollTeamPhotos(direction)"
            >
                <svg
                    width="18"
                    height="18"
                    viewBox="0 0 16 16"
                    fill="none"
                    aria-hidden="true"
                    :class="direction === -1 ? 'rotate-180' : ''"
                >
                    <path
                        d="M2 8H14M14 8L9 3M14 8L9 13"
                        stroke="currentColor"
                        stroke-width="1.3"
                    />
                </svg>
            </button>
        </div>
    </section>

    <PagePills page="about" />

    <SiteFooter id="contact" />
</template>
