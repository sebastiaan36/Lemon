<script setup lang="ts">
/**
 * Home V2 (interne review 28-11): cases vóór services en een rustiger
 * services-blok met één doorlopende video. De gewone homepage blijft ongewijzigd.
 */
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import HomeCasesCarousel from '@/components/home/HomeCasesCarousel.vue';
import HomeHero from '@/components/home/HomeHero.vue';
import HomeHeroBackdrop from '@/components/home/HomeHeroBackdrop.vue';
import HomeServicesStory from '@/components/home/HomeServicesStory.vue';
import HomeTeam from '@/components/home/HomeTeam.vue';
import HomeTestimonial from '@/components/home/HomeTestimonial.vue';
import PagePills from '@/components/PagePills.vue';
import SiteFooter from '@/components/SiteFooter.vue';
import SiteHeader from '@/components/SiteHeader.vue';
import { useScrollLayout } from '@/composables/useScrollLayout';
import {
    FIGMA_DESKTOP_HEIGHT,
    FIGMA_DESKTOP_WIDTH,
    rangeProgress,
    scaleY,
} from '@/lib/scrollAnimation';
import type { HomeCase, HomeService } from '@/types';

const props = withDefaults(
    defineProps<{
        seoTitle?: string | null;
        metaDescription?: string | null;
        heroTitle?: string;
        heroBodyText?: string;
        heroBgImage?: string | null;
        heroFloatingImages?: (string | null)[];
        servicesIntroText?: string;
        services?: HomeService[];
        servicesVideo?: { url: string; type: 'image' | 'video' } | null;
        cases?: HomeCase[];
        testimonialQuote?: string;
        testimonialAuthor?: string;
        testimonialAuthorAvatar?: string | null;
        testimonialLogo?: string | null;
        clientLogos?: string[];
        teamDescription?: string;
        teamButtonText?: string;
        teamButtonHref?: string;
        teamPhotos?: string[];
    }>(),
    {
        seoTitle: null,
        metaDescription: null,
        heroTitle: 'Credible Creativity',
        heroBodyText:
            "People love brands that live up to what they say and do. Now more than ever. We help your brand uncover its true essence and turn it into a credible, compelling story. One that creates consistently engaging experiences, wows minds, and wins hearts. From A to Z. From customers to employees. That is how you deliver on your brand promise. It's what we love to do, and what we call Credible Creativity.",
        heroBgImage: null,
        heroFloatingImages: () => [null, null, null, null],
        servicesIntroText:
            'Credible Creativity delivers on your brand promise through three connected services that engage everyone from customer to employee.',
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
                    'Social',
                ],
                mediaUrl: null,
                mediaType: 'image',
            },
            {
                name: 'Experience',
                description:
                    'Create memorable, connected brand experiences that drive engagement and loyalty from A to Z, customer to employee.',
                tags: ['Customer Journey', 'Digital', 'Events', 'Environments'],
                mediaUrl: null,
                mediaType: 'image',
            },
            {
                name: 'Employee',
                description:
                    "Align your internal culture with your external promise. Create energy and trust inside your company and shape how it's seen from the outside.",
                tags: ['Internal Comms', 'Culture', 'Engagement', 'Training'],
                mediaUrl: null,
                mediaType: 'image',
            },
        ],
        servicesVideo: null,
        cases: () => [],
        testimonialQuote:
            '"Our people live the brand, passengers feel it instantly, journeys become memorable and your airline becomes unmistakable."',
        testimonialAuthor: 'Michelle',
        testimonialAuthorAvatar:
            '/figma-assets/8a2fda0a-bf49-4b4c-b9a8-e66c433eed09.png',
        testimonialLogo: '/figma-assets/delta-logo.png',
        clientLogos: () => [
            '/figma-assets/72b82c06-224e-447c-a6e3-63cf81af6fbf.png',
            '/figma-assets/e6fdd583-cf9b-4016-89eb-8a00b937a435.png',
            '/figma-assets/51c72f3c-4a09-491a-a4cd-629926c6994e.png',
            '/figma-assets/25863eb3-6397-486a-a0c1-d8fe79dfe8dd.png',
            '/figma-assets/d11446f1-3384-40e5-82f1-ef19ebdc26e4.png',
        ],
        teamDescription:
            'Our reception desk is a tea bar – and occasionally just a bar. We host workshops sessions and always have bar stools reserved for our clients and the talented, inspiring people from our Lemon agency community.',
        teamButtonText: 'About us',
        teamButtonHref: '#about',
        teamPhotos: () => [],
    },
);

// Fallback-assets voor het testimonial-blok: tonen het Figma-ontwerp zolang het CMS leeg is.
const FALLBACK_TESTIMONIAL_LOGO = '/figma-assets/delta-logo.png';
const FALLBACK_TESTIMONIAL_AVATAR =
    '/figma-assets/8a2fda0a-bf49-4b4c-b9a8-e66c433eed09.png';
const FALLBACK_CLIENT_LOGOS = [
    '/figma-assets/72b82c06-224e-447c-a6e3-63cf81af6fbf.png',
    '/figma-assets/e6fdd583-cf9b-4016-89eb-8a00b937a435.png',
    '/figma-assets/51c72f3c-4a09-491a-a4cd-629926c6994e.png',
    '/figma-assets/25863eb3-6397-486a-a0c1-d8fe79dfe8dd.png',
    '/figma-assets/d11446f1-3384-40e5-82f1-ef19ebdc26e4.png',
];

// ─── Section refs ─────────────────────────────────────────────────────────────
const heroRef = ref<HTMLElement | null>(null);
const casesRef = ref<HTMLElement | null>(null);
const aboutRef = ref<HTMLElement | null>(null);
const servicesRef = ref<HTMLElement | null>(null);
const testimonialRef = ref<HTMLElement | null>(null);
const teamRef = ref<HTMLElement | null>(null);

const { scrollY, viewportWidth, viewportHeight, boxes, sectionProgress } =
    useScrollLayout(
        {
            hero: heroRef,
            cases: casesRef,
            about: aboutRef,
            services: servicesRef,
            testimonial: testimonialRef,
            team: teamRef,
        },
        { width: FIGMA_DESKTOP_WIDTH, height: FIGMA_DESKTOP_HEIGHT },
    );

const heroProgress = sectionProgress('hero');

function sy(value: number): string {
    return scaleY(value, viewportHeight.value);
}

// ─── Services: binnenkomen (sectie schuift het scherm in) en sticky verhaal ────
const servicesEnterProgress = computed(() => {
    const box = boxes.value.services;

    if (!box) {
        return 0;
    }

    return rangeProgress(
        scrollY.value,
        box.top - viewportHeight.value,
        box.top,
    );
});
/** Scrollafstand per dienst (in vh) in het sticky services-blok. */
const SERVICE_SCROLL_VH = 60;
const servicesStoryProgress = sectionProgress('services');

// ─── Header ───────────────────────────────────────────────────────────────────
const HEADER_PROBE_OFFSET = 40;
const HEADER_AREA_HEIGHT = 110;

function headerInside(
    key: 'cases' | 'about' | 'services' | 'testimonial' | 'team',
): boolean {
    const box = boxes.value[key];
    const line = scrollY.value + HEADER_PROBE_OFFSET;

    return !!box && line >= box.top && line < box.top + box.height;
}

// Geel achter de header: de services-intro, de kaartfase van services en team.
const navOnYellow = computed(
    () =>
        headerInside('about') ||
        headerInside('team') ||
        (headerInside('services') && servicesEnterProgress.value < 0.9) ||
        (!!boxes.value.team &&
            scrollY.value >= boxes.value.team.top + boxes.value.team.height),
);
const headerOverServices = computed(() => {
    const box = boxes.value.services;

    return (
        !!box &&
        servicesEnterProgress.value >= 0.9 &&
        scrollY.value + HEADER_AREA_HEIGHT < box.top + box.height
    );
});

// De vaste hero-video verdwijnt zodra de cases hem helemaal bedekken.
const heroBackdropHidden = computed(() => {
    const box = boxes.value.cases;

    return !!box && scrollY.value >= box.top;
});

// ─── Overige blokken ──────────────────────────────────────────────────────────
const resolvedTestimonialLogo = computed(
    () => props.testimonialLogo || FALLBACK_TESTIMONIAL_LOGO,
);
const resolvedTestimonialAvatar = computed(
    () => props.testimonialAuthorAvatar || FALLBACK_TESTIMONIAL_AVATAR,
);
const resolvedClientLogos = computed(() =>
    props.clientLogos && props.clientLogos.length > 0
        ? props.clientLogos
        : FALLBACK_CLIENT_LOGOS,
);
const teamPassProgress = computed(() => {
    const box = boxes.value.team;

    if (!box) {
        return 0;
    }

    return rangeProgress(
        scrollY.value,
        box.top - viewportHeight.value,
        box.top + box.height,
    );
});
</script>

<template>
    <Head :title="seoTitle ?? 'Lemon Scented Tea — Credible Creativity'" />

    <SiteHeader
        :nav-on-yellow="navOnYellow"
        :no-scrolled-backdrop="headerOverServices"
        :glass-cta="headerOverServices"
    />

    <HomeHeroBackdrop
        :bg-image="heroBgImage"
        :hero-progress="heroProgress"
        :hidden="heroBackdropHidden"
        :viewport-width="viewportWidth"
        :viewport-height="viewportHeight"
    />

    <!-- HERO — identiek aan de gewone homepage -->
    <section
        id="home"
        ref="heroRef"
        data-pill-section="hero"
        class="bg-[#0c0c0c]"
        style="height: 550vh"
    >
        <HomeHero
            :title="heroTitle"
            :body-text="heroBodyText"
            :floating-images="heroFloatingImages"
            :hero-progress="heroProgress"
            :viewport-width="viewportWidth"
            :viewport-height="viewportHeight"
        />
    </section>

    <!-- CASES — schuift met afgeronde bovenhoeken over de hero-video -->
    <section
        id="cases"
        ref="casesRef"
        data-pill-section="cases"
        class="relative z-[8] h-svh overflow-hidden rounded-t-[24px] bg-[#0c0c0c] md:h-screen md:rounded-t-[30px]"
    >
        <HomeCasesCarousel :cases="cases" :viewport-width="viewportWidth" />
    </section>

    <!-- SERVICES-INTRO — geel, sluit naadloos aan op de services-kaart -->
    <section
        id="about"
        ref="aboutRef"
        data-pill-section="about"
        class="relative z-[8] -mt-[24px] rounded-t-[24px] bg-[#ffc700] md:-mt-[30px] md:rounded-t-[30px]"
        :style="{
            paddingTop: `max(${sy(194)}, 120px)`,
            paddingBottom: sy(120),
        }"
    >
        <p
            class="mx-auto text-center font-avenir text-[#101010]"
            style="
                width: min(832px, calc(100vw - 48px));
                font-size: clamp(26px, 3.4vw, 50px);
                font-weight: 400;
                line-height: 1.2;
                letter-spacing: -0.03em;
            "
        >
            {{ servicesIntroText }}
        </p>
    </section>

    <!-- SERVICES — sticky, één doorlopende video; 60vh scroll per dienst -->
    <section
        id="services"
        ref="servicesRef"
        data-pill-section="services"
        class="relative z-[8] bg-[#ffc700]"
        :style="{ height: `${100 + services.length * SERVICE_SCROLL_VH}vh` }"
    >
        <HomeServicesStory
            :services="services"
            :video="servicesVideo"
            :enter-progress="servicesEnterProgress"
            :story-progress="servicesStoryProgress"
            :viewport-width="viewportWidth"
        />
    </section>

    <!-- TESTIMONIAL — schuift met afgeronde hoeken over de video omhoog -->
    <section
        ref="testimonialRef"
        data-pill-section="testimonial"
        class="relative z-[9] -mt-[30px] min-h-[50vh] rounded-t-[24px] bg-[#0c0c0c] md:rounded-t-[30px]"
    >
        <HomeTestimonial
            :quote="testimonialQuote"
            :author="testimonialAuthor"
            :author-avatar="resolvedTestimonialAvatar"
            :logo="resolvedTestimonialLogo"
            :client-logos="resolvedClientLogos"
            :viewport-width="viewportWidth"
        />
    </section>

    <!-- TEAM -->
    <section
        id="team"
        ref="teamRef"
        data-pill-section="team"
        style="position: relative; z-index: 10; background: #ffc700"
    >
        <HomeTeam
            :description="teamDescription"
            :button-text="teamButtonText"
            :button-href="teamButtonHref"
            :photos="teamPhotos"
            :pass-progress="teamPassProgress"
            :viewport-width="viewportWidth"
            :viewport-height="viewportHeight"
        />
    </section>

    <PagePills page="home-v2" />

    <SiteFooter />
</template>

<style>
html,
body {
    overflow-x: clip;
}
</style>
