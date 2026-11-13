<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import FloatingPill from '@/components/FloatingPill.vue';
import HomeCasesCarousel from '@/components/home/HomeCasesCarousel.vue';
import HomeHero from '@/components/home/HomeHero.vue';
import HomeHeroBackdrop from '@/components/home/HomeHeroBackdrop.vue';
import HomeServicesLayers from '@/components/home/HomeServicesLayers.vue';
import HomeTeam from '@/components/home/HomeTeam.vue';
import HomeTestimonial from '@/components/home/HomeTestimonial.vue';
import SiteFooter from '@/components/SiteFooter.vue';
import SiteHeader from '@/components/SiteHeader.vue';
import { useScrollLayout } from '@/composables/useScrollLayout';
import {
    FIGMA_DESKTOP_HEIGHT,
    FIGMA_DESKTOP_WIDTH,
    ease,
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
        teamAvatars?: string[];
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
        teamAvatars: () => [],
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
// Avatars in de zwevende "Meet the team"- en "Check job opportunities"-pills.
const FALLBACK_TEAM_AVATARS = [
    '/figma-assets/1c7ada78-2639-466d-9c86-b655a62ed8d4.png',
    '/figma-assets/f43a4ed5-5188-4fec-83e4-0c7d3aee6388.png',
    '/figma-assets/8b7bd57d-2be8-461e-8982-ebe28d772ec9.png',
    '/figma-assets/00ba73c5-9c31-4545-912f-957df2c1d581.png',
];
const JOBS_PILL_AVATAR = '/figma-assets/gijs-avatar.png';
const FALLBACK_CLIENT_LOGOS = [
    '/figma-assets/72b82c06-224e-447c-a6e3-63cf81af6fbf.png',
    '/figma-assets/e6fdd583-cf9b-4016-89eb-8a00b937a435.png',
    '/figma-assets/51c72f3c-4a09-491a-a4cd-629926c6994e.png',
    '/figma-assets/25863eb3-6397-486a-a0c1-d8fe79dfe8dd.png',
    '/figma-assets/d11446f1-3384-40e5-82f1-ef19ebdc26e4.png',
];

// ─── Section refs ─────────────────────────────────────────────────────────────
const heroRef = ref<HTMLElement | null>(null);
const aboutRef = ref<HTMLElement | null>(null);
const aboutTextRef = ref<HTMLElement | null>(null);
const servicesRef = ref<HTMLElement | null>(null);
const casesRef = ref<HTMLElement | null>(null);
const testimonialRef = ref<HTMLElement | null>(null);
const teamRef = ref<HTMLElement | null>(null);

// ─── Scroll & layout (offsets gecachet, scroll via rAF) ───────────────────────
const { scrollY, viewportWidth, viewportHeight, boxes, sectionProgress } =
    useScrollLayout(
        {
            hero: heroRef,
            about: aboutRef,
            aboutText: aboutTextRef,
            services: servicesRef,
            cases: casesRef,
            testimonial: testimonialRef,
            team: teamRef,
        },
        { width: FIGMA_DESKTOP_WIDTH, height: FIGMA_DESKTOP_HEIGHT },
    );

// ─── Progress values (0–1) ────────────────────────────────────────────────────
const heroProgress = sectionProgress('hero');
const servicesProgress = sectionProgress('services');
const casesProgress = sectionProgress('cases');
const testimonialProgress = sectionProgress('testimonial');

function sy(value: number): string {
    return scaleY(value, viewportHeight.value);
}

// ─── Nav kleur ────────────────────────────────────────────────────────────────
const navOnYellow = computed(() => {
    if (servicesProgress.value > 0.02 && servicesProgress.value < 0.99) {
        return false;
    }

    if (casesProgress.value > 0.02 && casesProgress.value < 0.99) {
        return false;
    }

    if (testimonialProgress.value > 0.02 && testimonialProgress.value < 0.99) {
        return false;
    }

    return heroProgress.value >= 1;
});

// ─── Services ─────────────────────────────────────────────────────────────────
const servicesLeadProgress = computed(() => {
    const about = boxes.value.about;
    const sectionTop = boxes.value.services?.top ?? Infinity;
    const aboutBottom = about ? about.top + about.height : sectionTop;
    const start = aboutBottom - viewportHeight.value * 1.2;
    const end = sectionTop + viewportHeight.value * 0.08;

    return rangeProgress(scrollY.value, start, end);
});

const SERVICE_SCROLL_LOCK_POINTS = [0.235, 0.555, 0.805] as const;
const SERVICE_SCROLL_LOCK_SPAN = 0.04;

function withScrollLocks(
    progress: number,
    points: readonly number[],
    span: number,
): number {
    const safeProgress = Math.max(0, Math.min(progress, 1));
    const totalHold = Math.min(span * points.length, 0.9);
    const moveScale = 1 / (1 - totalHold);
    let cursor = 0;
    let moved = 0;

    for (const point of points) {
        const start = Math.max(cursor, point - span / 2);
        const end = Math.min(1, start + span);

        if (safeProgress < start) {
            return Math.max(
                0,
                Math.min((moved + (safeProgress - cursor)) * moveScale, 1),
            );
        }

        moved += start - cursor;

        if (safeProgress <= end) {
            return Math.max(0, Math.min(moved * moveScale, 1));
        }

        cursor = end;
    }

    return Math.max(
        0,
        Math.min((moved + (safeProgress - cursor)) * moveScale, 1),
    );
}

const servicesVisualProgress = computed(() =>
    withScrollLocks(
        servicesProgress.value,
        SERVICE_SCROLL_LOCK_POINTS,
        SERVICE_SCROLL_LOCK_SPAN,
    ),
);

// Onderkant van de gele intro-tekst t.o.v. de viewport (positie van de services-kaart).
const aboutTextBottom = computed(() => {
    const box = boxes.value.aboutText;

    return box ? box.top + box.height - scrollY.value : null;
});

// Cases parallax entrance
const casesProgressRaw = computed(() => {
    const box = boxes.value.cases;

    if (!box) {
        return 0;
    }

    const scrollHeight = box.height - viewportHeight.value;

    if (scrollHeight <= 0) {
        return 0;
    }

    return (scrollY.value - box.top) / scrollHeight;
});

const casesStarted = computed(() => casesProgressRaw.value >= 0);
const heroBackdropHidden = computed(
    () => servicesProgress.value >= 1 && casesStarted.value,
);

// Val terug op de Figma-assets zolang het CMS nog geen content heeft.
const resolvedTestimonialLogo = computed(
    () => props.testimonialLogo || FALLBACK_TESTIMONIAL_LOGO,
);
const resolvedTestimonialAvatar = computed(
    () => props.testimonialAuthorAvatar || FALLBACK_TESTIMONIAL_AVATAR,
);
const meetTeamAvatars = computed(() =>
    props.teamAvatars.length > 0
        ? props.teamAvatars.slice(0, 4)
        : FALLBACK_TEAM_AVATARS,
);

// "Meet the team"-pill: komt pas in beeld als de hero uit beeld schuift en de
// gele about-sectie het scherm vult (mobiel en desktop), en blijft staan tot
// de services-video fullscreen gaat.
const meetTeamVisibility = computed(() => {
    const about = boxes.value.about;

    if (!about) {
        return 0;
    }

    const vh = viewportHeight.value;

    return Math.min(
        ease(
            rangeProgress(
                scrollY.value,
                about.top - vh * 0.5,
                about.top - vh * 0.2,
            ),
        ),
        1 - ease(rangeProgress(servicesVisualProgress.value, 0.005, 0.03)),
    );
});

// Voortgang (0–1) van de team-sectie van binnenkomen tot verlaten van de viewport.
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

// "Check job opportunities"-pill: zichtbaar zolang de team-sectie het beeld vult.
const jobsPillVisibility = computed(() => {
    const box = boxes.value.team;

    if (!box) {
        return 0;
    }

    const vh = viewportHeight.value;
    const bottom = box.top + box.height;

    return Math.min(
        ease(
            rangeProgress(
                scrollY.value,
                box.top - vh * 0.6,
                box.top - vh * 0.3,
            ),
        ),
        1 -
            ease(
                rangeProgress(
                    scrollY.value,
                    bottom - vh + 40,
                    bottom - vh + 200,
                ),
            ),
    );
});

const resolvedClientLogos = computed(() =>
    props.clientLogos && props.clientLogos.length > 0
        ? props.clientLogos
        : FALLBACK_CLIENT_LOGOS,
);
</script>

<template>
    <Head :title="seoTitle ?? 'Lemon Scented Tea — Credible Creativity'">
        <meta
            v-if="metaDescription"
            head-key="description"
            name="description"
            :content="metaDescription"
        />
    </Head>

    <SiteHeader :nav-on-yellow="navOnYellow" />

    <HomeHeroBackdrop
        :bg-image="heroBgImage"
        :hero-progress="heroProgress"
        :hidden="heroBackdropHidden"
        :viewport-width="viewportWidth"
        :viewport-height="viewportHeight"
    />

    <HomeServicesLayers
        :services="services"
        :visual-progress="servicesVisualProgress"
        :lead-progress="servicesLeadProgress"
        :cases-started="casesStarted"
        :about-text-bottom="aboutTextBottom"
        :viewport-width="viewportWidth"
        :viewport-height="viewportHeight"
    />

    <!-- ═══════════════════════════════════════════════
         HERO — 550vh sticky
    ════════════════════════════════════════════════ -->
    <section id="home" ref="heroRef" class="bg-[#0c0c0c]" style="height: 550vh">
        <HomeHero
            :title="heroTitle"
            :body-text="heroBodyText"
            :floating-images="heroFloatingImages"
            :hero-progress="heroProgress"
            :viewport-width="viewportWidth"
            :viewport-height="viewportHeight"
        />
    </section>

    <!-- ═══════════════════════════════════════════════
         GELE INTRO SECTIE
    ════════════════════════════════════════════════ -->
    <section
        id="about"
        ref="aboutRef"
        class="bg-[#ffc700]"
        :style="{
            position: 'relative',
            zIndex: 5,
            minHeight: sy(601),
            paddingTop: `max(${sy(194)}, 110px)`,
        }"
    >
        <!-- Figma: 50px/60px op 832px breed; schaalt mee naar ~7vw op mobiel -->
        <p
            ref="aboutTextRef"
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

    <!-- ═══════════════════════════════════════════════
         SERVICES — 650vh scroll tracking
         Alle visuele content (video, gele bg, tekst) is fixed — geen sticky nodig.
    ════════════════════════════════════════════════ -->
    <section id="services" ref="servicesRef" style="height: 650vh" />

    <!-- ═══════════════════════════════════════════════
         CASES
    ════════════════════════════════════════════════ -->
    <section
        id="cases"
        ref="casesRef"
        style="
            height: 100vh;
            margin-top: -80vh;
            position: relative;
            z-index: 8;
            background: #0c0c0c;
        "
    >
        <HomeCasesCarousel :cases="cases" :viewport-width="viewportWidth" />
    </section>

    <!-- ═══════════════════════════════════════════════
         TESTIMONIAL
    ════════════════════════════════════════════════ -->
    <section
        ref="testimonialRef"
        style="
            min-height: 50vh;
            position: relative;
            z-index: 9;
            background: #0c0c0c;
        "
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

    <!-- ═══════════════════════════════════════════════
         TEAM
    ════════════════════════════════════════════════ -->
    <section
        id="team"
        ref="teamRef"
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

    <FloatingPill
        label="Meet the team"
        href="#team"
        :avatars="meetTeamAvatars"
        :visibility="meetTeamVisibility"
        :desktop-bottom="11"
    />
    <FloatingPill
        label="Check job opportunities"
        href="/jobs"
        :avatars="[JOBS_PILL_AVATAR]"
        :visibility="jobsPillVisibility"
        :desktop-bottom="44"
    />

    <!-- ═══════════════════════════════════════════════
         FOOTER
    ════════════════════════════════════════════════ -->
    <SiteFooter />
</template>

<style>
html,
body {
    overflow-x: hidden;
}
</style>
