<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
import CasesCarousel from '@/components/CasesCarousel.vue';
import CmsImage from '@/components/CmsImage.vue';
import FloatingPill from '@/components/FloatingPill.vue';
import HlsVideo from '@/components/HlsVideo.vue';
import SiteFooter from '@/components/SiteFooter.vue';
import SiteHeader from '@/components/SiteHeader.vue';
import { useDragScroll } from '@/composables/useDragScroll';
import { SITE_NAV_ITEMS } from '@/lib/siteNavigation';

const CASES_ARROW_URL =
    '/figma-assets/d80e54ef-48d6-43a4-8629-0801cfe41603.svg';

type VisualItem = {
    image: string | null;
};

type GalleryItem = {
    url: string;
    type: 'image' | 'video';
    width: number | null;
    height: number | null;
};

type ResultStat = {
    prefix?: string | null;
    value: string;
    suffix?: string | null;
    label: string;
};

type RelatedCase = {
    name: string;
    slug: string;
    photo: string | null;
    video: string | null;
};

const props = defineProps<{
    seoTitle?: string | null;
    metaDescription?: string | null;
    caseStudy: {
        name: string;
        slug: string;
        clientName?: string | null;
        accentColor?: string | null;
        styleVariant?: number | null;
        heroTitle?: string | null;
        heroTitleLines?: string[];
        heroSubtitle?: string | null;
        heroMedia?: string | null;
        heroDuration?: string | null;
        introLogo?: string | null;
        touchpoints?: string[];
        overviewTitle?: string | null;
        overviewBody?: string | null;
        galleryItems?: GalleryItem[];
        highlightTitle?: string | null;
        highlightBody?: string | null;
        highlightMedia?: string | null;
        highlightButtonText?: string | null;
        campaignTitle?: string | null;
        campaignBody?: string | null;
        campaignMedia?: string | null;
        storyTitle?: string | null;
        storyBody?: string | null;
        storyMedia?: string | null;
        preCalloutTitle?: string | null;
        preCalloutBody?: string | null;
        preCalloutGalleryItems?: GalleryItem[];
        storyImages?: VisualItem[];
        calloutTitle?: string | null;
        secondaryStoryMedia?: string | null;
        secondaryStoryBody?: string | null;
        secondaryStoryButtonText?: string | null;
        resultsHeading?: string | null;
        resultsStats?: ResultStat[];
        optionalPanelTitle?: string | null;
        optionalPanelBody?: string | null;
        optionalPanelButtonText?: string | null;
    };
    moreCases?: RelatedCase[];
}>();

function isVideo(url: string | null | undefined): boolean {
    return !!url && /\.(mp4|webm|ogg|mov|m3u8)(\?.*)?$/i.test(url);
}

// Vaste kleurstellingen per opmaak-optie (gekozen in Filament).
// pageBg/pageText sturen de pagina-achtergrond en bodytekst; kleurstelling 3 is een donker thema.
type StylePalette = {
    accent: string;
    nav: string;
    highlightText: string;
    pageBg: string;
    pageText: string;
    divider: string;
};

const stylePalettes: Record<number, StylePalette> = {
    1: {
        accent: '#0A7949',
        // Figma: navigatie (logo-knop, menu) zwart, niet in de accentkleur.
        nav: '#0c0c0c',
        highlightText: '#ffffff',
        pageBg: '#fcfcfc',
        pageText: '#101010',
        divider: 'rgba(0,0,0,0.1)',
    },
    2: {
        accent: '#F6C000',
        nav: '#0c0c0c',
        highlightText: '#101010',
        pageBg: '#fcfcfc',
        pageText: '#101010',
        divider: 'rgba(0,0,0,0.1)',
    },
    3: {
        accent: '#F6C000',
        nav: '#ffffff',
        highlightText: '#101010',
        pageBg: '#000000',
        pageText: '#ffffff',
        divider: 'rgba(255,255,255,0.1)',
    },
};

const palette =
    stylePalettes[props.caseStudy.styleVariant ?? 1] ?? stylePalettes[1];
const accentColor = palette.accent;
const isDarkTheme = (props.caseStudy.styleVariant ?? 1) === 3;
const siteItems = SITE_NAV_ITEMS;

const heroVideoRef = ref<InstanceType<typeof HlsVideo> | null>(null);
const heroPlaying = ref(false);

function playHeroVideo(): void {
    const el = heroVideoRef.value?.videoEl;

    if (!el) {
        return;
    }

    void el.play();
    heroPlaying.value = true;
}

const isLargeScreen = ref(false);
const viewportWidth = ref(0);
const galleryItemSizes = [
    { width: 448, height: 492 },
    { width: 448, height: 526 },
    { width: 448, height: 428 },
];

function updateScreenSize() {
    if (typeof window === 'undefined') {
        return;
    }

    viewportWidth.value = window.innerWidth;
    isLargeScreen.value = window.innerWidth >= 1024;
}

onMounted(() => {
    updateScreenSize();
    window.addEventListener('resize', updateScreenSize);
});

onUnmounted(() => {
    if (typeof window === 'undefined') {
        return;
    }

    window.removeEventListener('resize', updateScreenSize);
});

const galleryRef = ref<HTMLElement | null>(null);
const galleryIndex = ref(0);
const galleryCount = (props.caseStudy.galleryItems || []).length;
const heroTitleLines = computed(() => {
    const lines = (props.caseStudy.heroTitleLines || []).filter(
        (line) => line.trim().length > 0,
    );

    return lines.length
        ? lines
        : [props.caseStudy.heroTitle || props.caseStudy.name];
});
const preCalloutIndex = ref(0);
const preCalloutGalleryItems = computed(
    () => props.caseStudy.preCalloutGalleryItems || [],
);
const preCalloutGalleryCount = computed(
    () => preCalloutGalleryItems.value.length,
);
// Desktop schuift de rij op met de werkelijke positie van de actieve kaart,
// omdat de kaarten een vaste hoogte en daardoor elk een eigen breedte hebben.
const preCalloutOffset = ref(0);
const preCalloutGalleryStyle = computed(() => ({
    transform: `translateX(-${preCalloutOffset.value}px)`,
}));

function updatePreCalloutOffset(): void {
    const items = preCalloutTrackRef.value?.querySelectorAll<HTMLElement>(
        '[data-pre-callout-item]',
    );
    const active = items?.[preCalloutIndex.value];

    preCalloutOffset.value =
        active && items?.[0] ? active.offsetLeft - items[0].offsetLeft : 0;
}

/** Breedte/hoogte van een galerijbeeld, zodat de kaart het hele beeld toont. */
function preCalloutAspect(item: GalleryItem): string {
    return item.width && item.height
        ? `${item.width} / ${item.height}`
        : '295 / 453';
}
const hasPreCalloutBlock = computed(
    () =>
        !!props.caseStudy.preCalloutTitle ||
        !!props.caseStudy.preCalloutBody ||
        preCalloutGalleryCount.value > 0,
);

const preCalloutTrackRef = ref<HTMLElement | null>(null);

watch([preCalloutIndex, isLargeScreen, viewportWidth], () =>
    nextTick(updatePreCalloutOffset),
);

useDragScroll(galleryRef);
useDragScroll(preCalloutTrackRef);

// Mobiel: de carousel wordt geswipet; de teller volgt de kaart die het dichtst bij het midden staat.
function onPreCalloutScroll(): void {
    const track = preCalloutTrackRef.value;

    if (isLargeScreen.value || !track) {
        return;
    }

    const items = Array.from(
        track.querySelectorAll<HTMLElement>('[data-pre-callout-item]'),
    );
    const center = track.scrollLeft + track.clientWidth / 2;
    let closest = 0;
    let smallestDistance = Infinity;

    items.forEach((item, index) => {
        const distance = Math.abs(
            item.offsetLeft + item.offsetWidth / 2 - center,
        );

        if (distance < smallestDistance) {
            smallestDistance = distance;
            closest = index;
        }
    });

    preCalloutIndex.value = closest;
}

function scrollPreCalloutGallery(direction: 1 | -1) {
    if (preCalloutGalleryCount.value === 0) {
        return;
    }

    preCalloutIndex.value = Math.max(
        0,
        Math.min(
            preCalloutGalleryCount.value - 1,
            preCalloutIndex.value + direction,
        ),
    );
}

function galleryItemStyle(index: number) {
    if (!isLargeScreen.value) {
        // Figma M: kaarten van 354×389–418 op een 540 breed scherm.
        return {
            width: '65.6vw',
            height: index % 2 === 0 ? '72vw' : '77.3vw',
        };
    }

    const size = galleryItemSizes[index % galleryItemSizes.length];

    return {
        width: `${size.width}px`,
        height: `${size.height}px`,
    };
}

function gallerySpacerWidth(): number {
    const container = galleryRef.value;

    if (!container) {
        return 0;
    }

    const spacer = container.querySelector<HTMLElement>(
        '[data-gallery-spacer]',
    );

    return spacer?.offsetWidth ?? 0;
}

function scrollGallery(direction: 1 | -1) {
    const container = galleryRef.value;

    if (!container) {
        return;
    }

    const items = Array.from(
        container.querySelectorAll<HTMLElement>('[data-gallery-item]'),
    );

    if (items.length === 0) {
        return;
    }

    const next = Math.max(
        0,
        Math.min(items.length - 1, galleryIndex.value + direction),
    );

    if (next === galleryIndex.value) {
        return;
    }

    galleryIndex.value = next;
    const target = items[next];
    const offset = gallerySpacerWidth();
    container.scrollTo({
        left: target.offsetLeft - container.offsetLeft - offset,
        behavior: 'smooth',
    });
}

function onGalleryScroll() {
    const container = galleryRef.value;

    if (!container) {
        return;
    }

    const items = Array.from(
        container.querySelectorAll<HTMLElement>('[data-gallery-item]'),
    );
    const offset = gallerySpacerWidth();
    const scrollLeft = container.scrollLeft + container.offsetLeft + offset;
    let closest = 0;
    let minDelta = Number.POSITIVE_INFINITY;
    items.forEach((el, i) => {
        const delta = Math.abs(el.offsetLeft - scrollLeft);

        if (delta < minDelta) {
            minDelta = delta;
            closest = i;
        }
    });
    galleryIndex.value = closest;
}

function pad2(n: number): string {
    return n.toString().padStart(2, '0');
}
</script>

<template>
    <Head :title="seoTitle ?? caseStudy.name" />

    <div :style="{ backgroundColor: palette.pageBg, color: palette.pageText }">
        <SiteHeader
            :nav-color="palette.nav"
            contact-href="/contact"
            :menu-items="siteItems"
            no-top-gradient
            cta-style="outline"
            avoid-matching-cta-background
        />

        <main>
            <!-- ============ HERO ============ -->
            <section
                data-section="hero"
                class="px-[33px] pt-[140px] pb-[24px] lg:px-[59px]"
            >
                <p
                    class="mb-4 text-center font-avenir text-[18px] leading-[1.1] tracking-[-0.18px] lg:text-[30px] lg:tracking-[-0.3px]"
                >
                    {{ caseStudy.heroSubtitle || caseStudy.clientName }}
                </p>
                <h1
                    class="mx-auto mb-[13.7vw] max-w-[63vw] text-center font-avenir text-[15.74vw] leading-[0.882] tracking-[-0.03em] lg:mb-[86px] lg:text-[137px] lg:leading-[0.9] lg:tracking-[-4.11px]"
                    :class="
                        heroTitleLines.length > 1
                            ? 'lg:max-w-[1041px]'
                            : 'lg:max-w-[700px]'
                    "
                    :style="{
                        color: accentColor,
                        fontWeight: '900',
                        fontStyle: 'oblique',
                    }"
                >
                    <span
                        v-for="line in heroTitleLines"
                        :key="line"
                        class="block"
                    >
                        {{ line }}
                    </span>
                </h1>

                <div
                    class="relative mx-auto max-w-[1529px] overflow-hidden rounded-[20px] bg-neutral-200"
                >
                    <template
                        v-if="
                            caseStudy.heroMedia && isVideo(caseStudy.heroMedia)
                        "
                    >
                        <HlsVideo
                            ref="heroVideoRef"
                            :src="caseStudy.heroMedia"
                            loop
                            playsinline
                            :controls="heroPlaying"
                            class="aspect-[483/550] w-full object-cover lg:aspect-[1529/901]"
                        />
                        <button
                            v-if="!heroPlaying"
                            type="button"
                            aria-label="Play video"
                            class="absolute inset-0 flex items-center justify-center"
                            @click="playHeroVideo"
                        >
                            <span
                                class="flex h-[52px] w-[52px] items-center justify-center rounded-full bg-lst-yellow transition-transform hover:scale-105"
                            >
                                <svg
                                    width="16"
                                    height="18"
                                    viewBox="0 0 16 18"
                                    fill="none"
                                    xmlns="http://www.w3.org/2000/svg"
                                >
                                    <path
                                        d="M15 7.27a2 2 0 0 1 0 3.46L3 17.66A2 2 0 0 1 0 15.93V2.07A2 2 0 0 1 3 .34l12 6.93Z"
                                        fill="#000"
                                    />
                                </svg>
                            </span>
                        </button>
                    </template>
                    <CmsImage
                        priority
                        v-else-if="caseStudy.heroMedia"
                        :src="caseStudy.heroMedia"
                        :alt="caseStudy.name"
                        class="aspect-[483/550] w-full object-cover lg:aspect-[1529/901]"
                    />
                    <div
                        v-else
                        class="aspect-[483/550] w-full bg-neutral-200 lg:aspect-[1529/901]"
                    />

                    <span
                        v-if="caseStudy.heroDuration && !heroPlaying"
                        class="pointer-events-none absolute right-6 bottom-6 font-avenir text-[15px] tracking-[-0.47px] text-white"
                    >
                        {{ caseStudy.heroDuration }}
                    </span>
                </div>
            </section>

            <!-- ============ META (client + touchpoints) ============ -->
            <section
                data-section="meta"
                class="px-[33px] pb-[24px] lg:px-[59px]"
            >
                <!-- Mobile: logo links + verticale stack van Client/Touchpoints rechts -->
                <div class="flex items-start gap-8 lg:hidden">
                    <CmsImage
                        sizes="240px"
                        v-if="caseStudy.introLogo"
                        :src="caseStudy.introLogo"
                        :alt="`Logo ${caseStudy.clientName || caseStudy.name}`"
                        class="h-[34px] w-auto shrink-0 object-contain"
                    />
                    <div class="flex flex-1 flex-col gap-4">
                        <div>
                            <p
                                class="font-avenir text-[11px] leading-[18px]"
                                style="font-weight: 300"
                            >
                                Client
                            </p>
                            <p
                                class="font-avenir text-[15px] leading-[20px] tracking-[-0.01em]"
                            >
                                {{ caseStudy.clientName }}
                            </p>
                        </div>
                        <div>
                            <p
                                class="font-avenir text-[11px] leading-[18px]"
                                style="font-weight: 300"
                            >
                                Touchpoints
                            </p>
                            <p
                                class="font-avenir text-[15px] leading-[20px] tracking-[-0.01em]"
                            >
                                {{ (caseStudy.touchpoints || []).join(', ') }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Desktop: 3-koloms grid (logo+client links, lege spacer, touchpoints rechts) -->
                <div
                    class="hidden lg:grid lg:grid-cols-[400px_1fr_281px] lg:items-center lg:gap-10"
                >
                    <div class="flex items-center gap-10">
                        <CmsImage
                            sizes="240px"
                            v-if="caseStudy.introLogo"
                            :src="caseStudy.introLogo"
                            :alt="`Logo ${caseStudy.clientName || caseStudy.name}`"
                            class="h-[44px] w-auto shrink-0 object-contain"
                        />
                        <div class="min-w-0">
                            <p
                                class="font-avenir text-[12px] leading-[22px]"
                                style="font-weight: 300"
                            >
                                Client
                            </p>
                            <p
                                class="font-avenir text-[18px] leading-[22px] tracking-[-0.54px] whitespace-nowrap"
                            >
                                {{ caseStudy.clientName }}
                            </p>
                        </div>
                    </div>
                    <div />
                    <div class="text-right">
                        <p
                            class="font-avenir text-[12px] leading-[22px]"
                            style="font-weight: 300"
                        >
                            Touchpoints
                        </p>
                        <p
                            class="font-avenir text-[18px] leading-[22px] tracking-[-0.54px]"
                        >
                            {{ (caseStudy.touchpoints || []).join(', ') }}
                        </p>
                    </div>
                </div>
            </section>

            <!-- ============ OVERVIEW (intro quote + body) ============ -->
            <section
                data-section="overview"
                :style="{ borderColor: palette.divider }"
                class="border-t px-[33px] pt-[60px] pb-[80px] lg:px-[5%] lg:pt-[118px] lg:pb-[200px]"
            >
                <div
                    class="flex flex-col items-center gap-10 text-center lg:grid lg:grid-cols-[minmax(0,555fr)_minmax(0,638fr)] lg:items-start lg:gap-10 lg:text-left"
                >
                    <h2
                        class="max-w-[76.5vw] font-avenir text-[8.52vw] leading-[1.087] tracking-[-0.03em] lg:max-w-[555px] lg:text-[60px] lg:leading-[70px] lg:tracking-[-1.8px]"
                        :style="{
                            color: accentColor,
                        }"
                    >
                        {{ caseStudy.overviewTitle }}
                    </h2>
                    <p
                        class="max-w-[407px] font-avenir text-[18px] leading-[28px] tracking-[-0.54px] lg:mt-[265px] lg:max-w-[638px] lg:text-[28px] lg:leading-[38px] lg:tracking-[-0.84px]"
                    >
                        {{ caseStudy.overviewBody }}
                    </p>
                </div>
            </section>

            <!-- ============ GALLERY (horizontal slider met afbeeldingen + videos in eigen aspect ratio) ============ -->
            <section
                v-if="(caseStudy.galleryItems || []).length"
                data-section="gallery"
                class="py-[60px] lg:py-[90px]"
            >
                <div class="relative">
                    <div
                        ref="galleryRef"
                        class="flex snap-x snap-mandatory scroll-pl-[33px] overflow-x-auto pb-4 lg:scroll-pl-[5%]"
                        style="scrollbar-width: none; -ms-overflow-style: none"
                        @scroll.passive="onGalleryScroll"
                    >
                        <div
                            data-gallery-spacer
                            class="w-[33px] shrink-0 lg:w-[5%]"
                            aria-hidden="true"
                        />

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
                                <CmsImage
                                    sizes="(min-width: 1024px) 45vw, 66vw"
                                    v-else
                                    :src="item.url"
                                    :alt="`${caseStudy.name} — beeld ${index + 1}`"
                                    :width="item.width ?? undefined"
                                    :height="item.height ?? undefined"
                                    class="block h-full w-full object-cover"
                                />
                            </div>
                        </div>

                        <div
                            class="w-[33px] shrink-0 lg:w-[5%]"
                            aria-hidden="true"
                        />
                    </div>
                </div>

                <div
                    class="mt-8 flex items-center justify-end px-[33px] lg:mt-10 lg:justify-between lg:px-[5%]"
                >
                    <p
                        class="flex items-center gap-3 font-avenir text-[16px] tracking-[-0.48px]"
                        :style="{
                            color: accentColor,
                        }"
                    >
                        <span>{{ pad2(galleryIndex + 1) }}</span>
                        <span
                            class="inline-block h-px w-6"
                            :style="{ backgroundColor: accentColor }"
                        />
                        <span>{{ pad2(galleryCount) }}</span>
                    </p>

                    <div class="hidden items-center gap-4 lg:flex">
                        <button
                            type="button"
                            class="flex h-[42px] w-[42px] items-center justify-center rounded-full border transition disabled:opacity-30 lg:h-[46px] lg:w-[46px]"
                            :style="{
                                borderColor: accentColor,
                                color: accentColor,
                            }"
                            :disabled="galleryIndex === 0"
                            aria-label="Vorige"
                            @click="scrollGallery(-1)"
                        >
                            <svg
                                width="18"
                                height="18"
                                viewBox="0 0 20 20"
                                fill="none"
                            >
                                <path
                                    d="M16 10H4M4 10l5-5M4 10l5 5"
                                    stroke="currentColor"
                                    stroke-width="1.6"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </button>
                        <button
                            type="button"
                            class="flex h-[42px] w-[42px] items-center justify-center rounded-full border transition disabled:opacity-30 lg:h-[46px] lg:w-[46px]"
                            :style="{
                                borderColor: accentColor,
                                color: accentColor,
                            }"
                            :disabled="galleryIndex >= galleryCount - 1"
                            aria-label="Volgende"
                            @click="scrollGallery(1)"
                        >
                            <svg
                                width="18"
                                height="18"
                                viewBox="0 0 20 20"
                                fill="none"
                            >
                                <path
                                    d="M4 10h12M16 10l-5-5M16 10l-5 5"
                                    stroke="currentColor"
                                    stroke-width="1.6"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </button>
                    </div>
                </div>
            </section>

            <!-- ============ HIGHLIGHT (green block, "Nature has to shout to be heard") ============ -->
            <section
                v-if="caseStudy.highlightTitle || caseStudy.highlightBody"
                data-section="highlight"
            >
                <div
                    class="px-[33px] py-[23vw] lg:px-[59px] lg:py-[120px]"
                    :style="{ backgroundColor: accentColor }"
                >
                    <h2
                        class="mx-auto max-w-[70vw] text-center font-avenir text-[12.96vw] leading-[0.93] tracking-[-0.03em] lg:max-w-[800px] lg:text-[140px] lg:leading-[130px] lg:tracking-[-4.2px]"
                        :style="{
                            color: palette.highlightText,
                            fontWeight: '900',
                            fontStyle: 'oblique',
                        }"
                    >
                        {{ caseStudy.highlightTitle }}
                    </h2>
                    <p
                        class="mx-auto mt-8 max-w-[83vw] text-center font-avenir text-[max(16px,4.07vw)] leading-[1.45] tracking-[-0.03em] lg:mt-10 lg:max-w-[765px] lg:text-[22px] lg:leading-[32px] lg:tracking-[-0.66px]"
                        :style="{
                            color: palette.highlightText,
                        }"
                    >
                        {{ caseStudy.highlightBody }}
                    </p>
                    <div
                        v-if="caseStudy.highlightButtonText"
                        class="mt-8 flex justify-center lg:mt-10"
                    >
                        <a
                            href="#contact"
                            class="rounded-[30px] border px-8 py-3 font-avenir text-[18px] tracking-[-0.54px] lg:text-[20px] lg:tracking-[-0.6px]"
                            :style="{
                                color: palette.highlightText,
                                borderColor: palette.highlightText,
                            }"
                        >
                            {{ caseStudy.highlightButtonText }}
                        </a>
                    </div>
                </div>
            </section>

            <!-- ============ CAMPAIGN (full-width image with overlay text) ============ -->
            <section
                v-if="caseStudy.campaignMedia"
                data-section="campaign"
                class="relative"
            >
                <div
                    class="relative h-[600px] overflow-hidden bg-black lg:h-[994px]"
                >
                    <CmsImage
                        v-if="!isVideo(caseStudy.campaignMedia)"
                        :src="caseStudy.campaignMedia"
                        :alt="`${caseStudy.name} — campagne`"
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
                    <!-- Mobiel: donker onderin naar transparant, voor leesbare tekst over het beeld -->
                    <div
                        class="absolute inset-0 bg-gradient-to-t from-black/85 from-0% via-black/55 via-35% to-transparent to-70% lg:hidden"
                    />
                    <div
                        class="absolute inset-0 hidden bg-gradient-to-r from-transparent via-transparent to-black/35 lg:block"
                    />
                    <div
                        class="absolute inset-x-[33px] bottom-[40px] mx-auto max-w-[437px] text-center text-white lg:inset-x-auto lg:right-[211px] lg:bottom-[278px] lg:mx-0 lg:w-[437px] lg:max-w-none lg:text-left"
                    >
                        <h3
                            class="font-avenir text-[28px] leading-[34px] tracking-[-0.84px] lg:text-[32px] lg:leading-[38px] lg:tracking-[-0.96px]"
                        >
                            {{ caseStudy.campaignTitle }}
                        </h3>
                        <p
                            class="mt-4 font-avenir text-[16px] leading-[24px] tracking-[-0.48px] lg:mt-6 lg:text-[18px] lg:leading-[28px] lg:tracking-[-0.54px]"
                        >
                            {{ caseStudy.campaignBody }}
                        </p>
                    </div>
                </div>
            </section>

            <!-- ============ STORY (full-bleed image + text overlay rechts, campagne-style) ============ -->
            <section
                v-if="
                    caseStudy.storyMedia ||
                    caseStudy.storyTitle ||
                    caseStudy.storyBody
                "
                data-section="story"
                class="relative"
            >
                <div
                    class="relative h-[148.5vw] overflow-hidden bg-black lg:h-[994px]"
                >
                    <HlsVideo
                        v-if="
                            caseStudy.storyMedia &&
                            isVideo(caseStudy.storyMedia)
                        "
                        :src="caseStudy.storyMedia"
                        autoplay
                        loop
                        muted
                        playsinline
                        class="h-full w-full object-cover object-[10%_50%] lg:object-center"
                    />
                    <CmsImage
                        v-else-if="caseStudy.storyMedia"
                        :src="caseStudy.storyMedia"
                        :alt="caseStudy.name"
                        class="h-full w-full object-cover object-[10%_50%] lg:object-center"
                    />

                    <!-- Mobiel: donker onderin naar transparant, voor leesbare tekst over het beeld -->
                    <div
                        class="absolute inset-0 bg-gradient-to-t from-black/85 from-0% via-black/55 via-35% to-transparent to-70% lg:hidden"
                    />
                    <div
                        class="absolute inset-0 hidden bg-gradient-to-r from-transparent via-transparent to-black/35 lg:block"
                    />

                    <div
                        class="absolute inset-x-[33px] bottom-[40px] mx-auto max-w-[415px] text-center text-white lg:inset-x-auto lg:top-1/2 lg:bottom-auto lg:left-[58%] lg:mx-0 lg:w-[415px] lg:max-w-none lg:-translate-y-1/2 lg:text-left"
                    >
                        <h2
                            v-if="caseStudy.storyTitle"
                            class="font-avenir text-[max(24px,5.93vw)] leading-[1.19] tracking-[-0.03em] lg:text-[32px] lg:leading-[38px] lg:tracking-[-0.96px]"
                        >
                            {{ caseStudy.storyTitle }}
                        </h2>
                        <p
                            v-if="caseStudy.storyBody"
                            class="mt-4 font-avenir text-[max(15px,3.33vw)] leading-[1.56] tracking-[-0.03em] lg:mt-6 lg:text-[18px] lg:leading-[28px] lg:tracking-[-0.54px]"
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
                class="grid gap-10 px-[33px] py-[80px] lg:grid-cols-[minmax(280px,374px)_minmax(0,1fr)] lg:gap-[60px] lg:pt-[180px] lg:pr-0 lg:pb-[120px] lg:pl-[8.7vw]"
            >
                <div class="text-center lg:pt-2 lg:text-left">
                    <h2
                        v-if="caseStudy.preCalloutTitle"
                        class="mx-auto max-w-[340px] font-avenir text-[32px] leading-[1.15] tracking-[-0.03em] whitespace-pre-line lg:mx-0 lg:max-w-none lg:text-[48px] lg:leading-[1.12] lg:tracking-[-1.44px]"
                        :style="{
                            color: accentColor,
                        }"
                    >
                        {{ caseStudy.preCalloutTitle }}
                    </h2>
                    <p
                        v-if="caseStudy.preCalloutBody"
                        class="mx-auto mt-5 max-w-[420px] font-avenir text-[16px] leading-[1.6] tracking-[-0.3px] whitespace-pre-line lg:mx-0 lg:mt-7 lg:max-w-none lg:text-[18px] lg:leading-[1.65]"
                        :style="{
                            color: palette.pageText,
                        }"
                    >
                        {{ caseStudy.preCalloutBody }}
                    </p>
                </div>

                <div
                    v-if="preCalloutGalleryCount"
                    class="-mx-[33px] min-w-0 lg:mx-0"
                >
                    <!-- Mobiel: swipen (Figma M toont geen pijlen, wel een sleep-icoon). Desktop: pijlen. -->
                    <div
                        ref="preCalloutTrackRef"
                        class="relative overflow-x-auto [scrollbar-width:none] lg:overflow-hidden [&::-webkit-scrollbar]:hidden"
                        :class="isLargeScreen ? '' : 'snap-x snap-mandatory'"
                        @scroll.passive="onPreCalloutScroll"
                    >
                        <div
                            class="flex gap-[4.3vw] px-[6vw] transition-transform duration-500 ease-out lg:gap-6 lg:px-0"
                            :style="
                                isLargeScreen
                                    ? preCalloutGalleryStyle
                                    : undefined
                            "
                        >
                            <div
                                v-for="(item, index) in preCalloutGalleryItems"
                                :key="`${item.url}-${index}`"
                                data-pre-callout-item
                                class="h-[84vw] w-auto max-w-[88vw] shrink-0 snap-center overflow-hidden rounded-[8px] bg-neutral-200 lg:h-[min(860px,52vw)] lg:max-w-none lg:rounded-[12px]"
                                :style="{ aspectRatio: preCalloutAspect(item) }"
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
                                <CmsImage
                                    sizes="(min-width: 1024px) 60vw, 88vw"
                                    v-else
                                    :src="item.url"
                                    :alt="`${caseStudy.name} — beeld ${index + 1}`"
                                    class="block h-full w-full object-cover"
                                />
                            </div>
                        </div>
                    </div>

                    <div
                        class="mt-7 flex items-center justify-end px-[33px] lg:justify-between lg:px-3 lg:pr-[4.9vw]"
                    >
                        <p
                            class="font-avenir text-[15px] tracking-[-0.15px]"
                            :style="{
                                color: accentColor,
                            }"
                        >
                            {{ pad2(preCalloutIndex + 1) }} —
                            {{ pad2(preCalloutGalleryCount) }}
                        </p>

                        <div class="hidden items-center gap-5 lg:flex">
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
                                :disabled="
                                    preCalloutIndex >=
                                    preCalloutGalleryCount - 1
                                "
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
                    class="text-center font-avenir text-[clamp(56px,17vw,120px)] leading-[0.95] tracking-[-0.03em] whitespace-pre-line lg:text-left lg:text-[200px] lg:leading-[190px] lg:tracking-[-6px]"
                    :style="{
                        color: accentColor,
                        fontWeight: '900',
                        fontStyle: 'oblique',
                    }"
                >
                    {{ caseStudy.calloutTitle }}
                </h2>
            </section>

            <!-- ============ SECONDARY STORY (grote afbeelding links + body en CTA-pill rechts) ============ -->
            <section
                v-if="
                    caseStudy.secondaryStoryMedia ||
                    caseStudy.secondaryStoryBody
                "
                data-section="secondary-story"
                class="px-[33px] pb-[80px] lg:pr-[10%] lg:pb-[120px] lg:pl-[59px]"
            >
                <div
                    class="flex flex-col gap-8 lg:grid lg:grid-cols-[minmax(0,973px)_minmax(320px,1fr)] lg:items-start lg:gap-24"
                >
                    <div class="overflow-hidden rounded-[20px] bg-neutral-200">
                        <HlsVideo
                            v-if="
                                caseStudy.secondaryStoryMedia &&
                                isVideo(caseStudy.secondaryStoryMedia)
                            "
                            :src="caseStudy.secondaryStoryMedia"
                            autoplay
                            loop
                            muted
                            playsinline
                            class="aspect-[486/352] w-full object-cover lg:aspect-[973/544]"
                        />
                        <CmsImage
                            sizes="(min-width: 1024px) 64vw, 100vw"
                            v-else-if="caseStudy.secondaryStoryMedia"
                            :src="caseStudy.secondaryStoryMedia"
                            :alt="caseStudy.name"
                            class="aspect-[486/352] w-full object-cover lg:aspect-[973/544]"
                        />
                    </div>

                    <div class="flex h-full flex-col">
                        <p
                            v-if="caseStudy.secondaryStoryBody"
                            class="text-center font-avenir text-[18px] leading-[28px] tracking-[-0.54px] whitespace-pre-line lg:text-left"
                            :style="{
                                color: palette.pageText,
                            }"
                        >
                            {{ caseStudy.secondaryStoryBody }}
                        </p>
                        <a
                            v-if="caseStudy.secondaryStoryButtonText"
                            href="#contact"
                            class="mt-8 flex h-[73px] w-full max-w-[387px] items-center gap-4 self-center rounded-[73px] px-4 lg:mt-10 lg:self-start"
                            style="
                                background: rgba(0, 0, 0, 0.7);
                                border: 0.6px solid rgba(255, 255, 255, 0.05);
                                backdrop-filter: blur(6px);
                            "
                        >
                            <span
                                class="flex-1 font-avenir text-white"
                                style="font-size: 18px; letter-spacing: -0.18px"
                            >
                                {{ caseStudy.secondaryStoryButtonText }}
                            </span>
                            <div
                                class="flex h-[43px] w-[57px] flex-shrink-0 items-center justify-center rounded-[69px] bg-[#ffc700]"
                            >
                                <svg
                                    width="16"
                                    height="16"
                                    viewBox="0 0 16 16"
                                    fill="none"
                                    style="transform: rotate(-135deg)"
                                >
                                    <path
                                        d="M3 8H13M13 8L8 3M13 8L8 13"
                                        stroke="black"
                                        stroke-width="1.6"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            </div>
                        </a>
                    </div>
                </div>
            </section>

            <!-- ============ RESULTS (titel met pijl-icoon links boven, stats in 3 cellen met verticale dividers) ============ -->
            <section
                v-if="(caseStudy.resultsStats || []).length"
                data-section="results"
                class="border-b"
                :style="{ borderColor: palette.divider }"
            >
                <div
                    class="border-b px-[33px] py-6 lg:py-[32px] lg:pr-[5%] lg:pl-[7.3vw]"
                    :style="{ borderColor: palette.divider }"
                >
                    <div class="flex items-center gap-4 lg:gap-[34px]">
                        <svg
                            width="28"
                            height="28"
                            viewBox="0 0 24 24"
                            fill="none"
                            class="shrink-0 lg:h-9 lg:w-9"
                            :style="{ color: accentColor }"
                            aria-hidden="true"
                        >
                            <path
                                d="M7 7l10 10M17 17H10M17 17V10"
                                stroke="currentColor"
                                stroke-width="1.6"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                        <h2
                            class="font-avenir text-[max(26px,6.67vw)] leading-[1.1] tracking-[-0.03em] lg:text-[50px] lg:leading-[60px] lg:tracking-[-1.5px]"
                            :style="{
                                color: accentColor,
                            }"
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
                        class="flex flex-col items-center px-8 py-[7vw] text-center lg:pt-[50px] lg:pb-[85px]"
                    >
                        <div class="flex items-start">
                            <p
                                v-if="stat.prefix"
                                class="mt-8 -mr-2 font-avenir text-[32px] leading-[32px] tracking-[-0.96px] lg:mt-12 lg:-mr-3 lg:text-[40px] lg:leading-[40px] lg:tracking-[-1.2px]"
                                :style="{
                                    color: accentColor,
                                    fontWeight: '900',
                                    fontStyle: 'oblique',
                                }"
                            >
                                {{ stat.prefix }}
                            </p>
                            <p
                                class="font-avenir text-[22.2vw] leading-[1.167] tracking-[-0.03em] lg:text-[160px] lg:leading-[180px] lg:tracking-[-4.8px]"
                                :style="{
                                    color: accentColor,
                                    fontWeight: '900',
                                    fontStyle: 'oblique',
                                }"
                            >
                                {{ stat.value }}
                            </p>
                            <!-- Figma: kleine eenheid (bv. "k") rechts van de waarde, verticaal gecentreerd -->
                            <p
                                v-if="stat.suffix"
                                class="font-avenir text-[6.94vw] leading-[1.167] tracking-[-0.03em] lg:text-[50px] lg:leading-[180px] lg:tracking-[-1.5px]"
                                :style="{
                                    color: accentColor,
                                    fontWeight: '900',
                                    fontStyle: 'oblique',
                                }"
                            >
                                {{ stat.suffix }}
                            </p>
                        </div>
                        <p
                            class="mt-2 font-avenir text-[max(17px,4.44vw)] leading-[1.3] tracking-[-0.03em] lg:mt-0 lg:text-[24px] lg:leading-[60px]"
                            :style="{
                                color: accentColor,
                            }"
                        >
                            {{ stat.label }}
                        </p>
                    </div>
                </div>
            </section>

            <!-- ============ OPTIONAL PANEL (extra centered CTA panel) ============ -->
            <section
                v-if="
                    caseStudy.optionalPanelTitle ||
                    caseStudy.optionalPanelBody ||
                    caseStudy.optionalPanelButtonText
                "
                data-section="optional-panel"
                class="px-[33px] pt-[40px] pb-[80px] lg:px-[59px] lg:pb-[120px]"
            >
                <div class="mx-auto max-w-[765px] text-center">
                    <h2
                        v-if="caseStudy.optionalPanelTitle"
                        class="font-avenir text-[36px] leading-[44px] tracking-[-1.08px] lg:text-[50px] lg:leading-[60px] lg:tracking-[-1.5px]"
                        :style="{
                            color: accentColor,
                        }"
                    >
                        {{ caseStudy.optionalPanelTitle }}
                    </h2>
                    <p
                        v-if="caseStudy.optionalPanelBody"
                        class="mt-6 font-avenir text-[18px] leading-[28px] tracking-[-0.54px] lg:text-[22px] lg:leading-[32px] lg:tracking-[-0.66px]"
                        :style="{
                            color: palette.pageText,
                        }"
                    >
                        {{ caseStudy.optionalPanelBody }}
                    </p>
                    <div
                        v-if="caseStudy.optionalPanelButtonText"
                        class="mt-8 flex justify-center lg:mt-10"
                    >
                        <a
                            href="#contact"
                            class="rounded-[30px] border px-8 py-3 font-avenir text-[18px] tracking-[-0.54px] lg:text-[20px] lg:tracking-[-0.6px]"
                            :style="{
                                borderColor: accentColor,
                                color: accentColor,
                            }"
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
                <div
                    class="mb-10 flex items-start justify-end gap-3 px-[33px] lg:mb-14 lg:px-[59px]"
                >
                    <img
                        loading="lazy"
                        :src="CASES_ARROW_URL"
                        alt=""
                        class="mt-2 h-[44px] w-[44px] lg:mt-3 lg:h-[52px] lg:w-[52px]"
                    />
                    <h2
                        class="font-avenir text-[33px] leading-[30px] tracking-[-0.99px] text-white lg:text-[40px] lg:leading-[36px] lg:tracking-[-1.21px]"
                        style="font-weight: 900; font-style: oblique"
                    >
                        Explore<br />more cases
                    </h2>
                </div>

                <!-- Center-peek infinite carousel: actieve case gecentreerd, buren links/rechts net buiten beeld -->
                <div class="relative">
                    <CasesCarousel
                        :cases="moreCases"
                        centered
                        infinite
                        :show-counter="false"
                        :show-controls-on-mobile="false"
                        :controls-padding-right="
                            isLargeScreen ? '59px' : '33px'
                        "
                        :aspect-ratio="isLargeScreen ? '16 / 9' : '238 / 307'"
                        :gap="isLargeScreen ? '40px' : '4vw'"
                        :card-widths="
                            isLargeScreen
                                ? ['42vw', '32vw', '37vw']
                                : ['44vw', '40vw', '46vw']
                        "
                    />
                </div>
            </section>
        </main>

        <!-- Figma: zwevende "Tell us about your project"-pill rechtsonder -->
        <FloatingPill
            label="Tell us about your project"
            href="/contact"
            :avatars="['/figma-assets/gijs-avatar.png']"
        />

        <SiteFooter id="contact" :footer-items="siteItems" />
    </div>
</template>

<style scoped>
[data-section='gallery'] > div::-webkit-scrollbar {
    display: none;
}
</style>
