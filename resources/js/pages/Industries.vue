<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { onBeforeUnmount, onMounted, ref } from 'vue';
import CasesCarousel from '@/components/CasesCarousel.vue';
import CmsImage from '@/components/CmsImage.vue';
import PagePills from '@/components/PagePills.vue';
import SiteFooter from '@/components/SiteFooter.vue';
import SiteHeader from '@/components/SiteHeader.vue';
import VideoHero from '@/components/VideoHero.vue';
import { useLightBackgroundBehindHeader } from '@/composables/useLightBackgroundBehindHeader';

type IndustryCase = {
    name: string;
    slug: string;
    video: string | null;
    photo: string | null;
    touchpoints: string[];
};

type ApproachItem = {
    title: string;
    subtitle: string | null;
    body: string | null;
    bullets: string[];
};

type Industry = {
    name: string;
    slug: string;
    headline: string | null;
    whyTitle: string | null;
    whyLead: string | null;
    whyBody: string | null;
    cases: IndustryCase[];
    approachTitle: string | null;
    approachItems: ApproachItem[];
    beliefTitle: string | null;
    beliefBody: string | null;
    beliefHighlight: string | null;
    ctaText: string | null;
    ctaPrimaryText: string | null;
    ctaPrimaryHref: string | null;
    ctaSecondaryText: string | null;
    ctaSecondaryHref: string | null;
    proofTitle: string | null;
    proofLogos: { name: string; logo: string }[];
    impactTitle: string | null;
    impactBody: string | null;
    impactStats: { value: string; label: string }[];
    insightsTitle: string | null;
    insights: { title: string; slug: string; category: string | null }[];
};

const props = defineProps<{
    heroTitle: string | null;
    heroDescription: string | null;
    heroBgImage?: { url: string; type: string } | null;
    navLabel: string | null;
    ctaAvatar: string | null;
    seoTitle: string | null;
    industries: Industry[];
}>();

const DEFAULT_CTA_AVATAR = '/figma-assets/kirsten-avatar.png';

// Menu-icoon en "Get in touch": wit op de videohero en de zwarte branches.
const navOnLightBackground = useLightBackgroundBehindHeader(false);

// ─── Actieve branche in de navigatie-pill ─────────────────────────────────
const activeSlug = ref(props.industries[0]?.slug ?? '');
let sectionObserver: IntersectionObserver | null = null;

onMounted(() => {
    if (typeof IntersectionObserver === 'undefined') {
        return;
    }

    // Een branche is "actief" zodra hij de bovenste helft van het scherm vult.
    sectionObserver = new IntersectionObserver(
        (entries) => {
            const visible = entries
                .filter((entry) => entry.isIntersecting)
                .sort(
                    (a, b) =>
                        a.boundingClientRect.top - b.boundingClientRect.top,
                );

            if (visible[0]) {
                activeSlug.value = (visible[0].target as HTMLElement).id;
            }
        },
        { rootMargin: '-45% 0px -50% 0px' },
    );

    for (const industry of props.industries) {
        const section = document.getElementById(industry.slug);

        if (section) {
            sectionObserver.observe(section);
        }
    }
});

onBeforeUnmount(() => sectionObserver?.disconnect());

// ─── Branche-pill: donker glas over de videohero, wit glasplaatje met zwarte letters
// zodra hij boven de zwarte branches hangt ─
const industryNav = ref<HTMLElement | null>(null);
const navOverBlack = ref(false);
let navFrame = 0;

function measureNav(): void {
    navFrame = 0;
    const hero = document.querySelector('[data-section="hero"]');

    if (!industryNav.value || !hero) {
        return;
    }

    // Pas omschakelen als de hero volledig boven de pill is weggescrold.
    navOverBlack.value =
        hero.getBoundingClientRect().bottom <=
        industryNav.value.getBoundingClientRect().top;
}

function scheduleNavMeasure(): void {
    if (!navFrame) {
        navFrame = requestAnimationFrame(measureNav);
    }
}

onMounted(() => {
    measureNav();
    window.addEventListener('scroll', scheduleNavMeasure, { passive: true });
    window.addEventListener('resize', scheduleNavMeasure, { passive: true });
});

onBeforeUnmount(() => {
    window.removeEventListener('scroll', scheduleNavMeasure);
    window.removeEventListener('resize', scheduleNavMeasure);

    if (navFrame) {
        cancelAnimationFrame(navFrame);
    }
});
</script>

<template>
    <Head>
        <title>{{ seoTitle || 'Industries — Lemon Scented Tea' }}</title>
    </Head>

    <div class="min-h-screen overflow-x-clip bg-black text-white">
        <SiteHeader :nav-on-yellow="navOnLightBackground" />

        <!-- ============ HERO (video op de achtergrond, gedeeld met About) ============ -->
        <VideoHero
            :title="heroTitle"
            :subtitle="heroDescription"
            :media="heroBgImage"
            scroll-hint-class="bottom-[40px] md:bottom-[48px]"
        />

        <!-- ============ BRANCHE-NAVIGATIE (pill over de onderrand van de hero, blijft plakken) ============ -->
        <nav
            v-if="industries.length"
            aria-label="Industries"
            data-header-ignore
            class="pointer-events-none sticky top-[96px] z-[55] -mt-[30px] flex justify-center px-5 md:top-[24px] md:-mt-[36px]"
        >
            <div
                ref="industryNav"
                class="pointer-events-auto flex h-[60px] max-w-full items-center gap-6 overflow-x-auto rounded-[73px] border-[0.6px] px-6 font-avenir text-[16px] whitespace-nowrap backdrop-blur-[6px] transition-colors duration-300 [scrollbar-width:none] md:h-[73px] md:gap-[36px] md:px-10 md:text-[18px] [&::-webkit-scrollbar]:hidden"
                :class="
                    navOverBlack
                        ? 'border-black/5 bg-white/50'
                        : 'border-white/5 bg-black/70'
                "
                style="box-shadow: inset 0 -2.4px 4.9px 0 rgba(0, 0, 0, 0.1)"
            >
                <span
                    v-if="navLabel"
                    class="font-black tracking-[-0.01em] text-[#ffc700] italic"
                >
                    {{ navLabel }}
                </span>
                <a
                    v-for="industry in industries"
                    :key="industry.slug"
                    :href="`#${industry.slug}`"
                    class="relative font-extrabold tracking-[-0.01em] transition-colors duration-300 after:absolute after:inset-x-0 after:-bottom-[7px] after:h-[2px] after:origin-left after:rounded-full after:bg-[#ffc700] after:transition-transform after:duration-300 after:ease-out hover:after:scale-x-100 motion-reduce:after:transition-none"
                    :class="[
                        navOverBlack ? 'text-black' : 'text-white',
                        activeSlug === industry.slug
                            ? 'after:scale-x-100'
                            : 'after:scale-x-0',
                    ]"
                    :aria-current="
                        activeSlug === industry.slug ? 'true' : undefined
                    "
                >
                    {{ industry.name }}
                </a>
            </div>
        </nav>

        <!-- ============ BRANCHES ============ -->
        <section
            data-pill-section="industries"
            v-for="(industry, index) in industries"
            :id="industry.slug"
            :key="industry.slug"
            class="scroll-mt-[120px] px-6 pt-24 pb-12 lg:grid lg:grid-cols-[31.6vw_minmax(0,1fr)] lg:pr-[5.4vw] lg:pb-0 lg:pl-[5.8vw]"
            :class="index === 0 ? 'lg:pt-[12.5vw]' : 'lg:pt-[12.6vw]'"
        >
            <!-- Blijft plakken (ruim onder logo en branche-pill) tot de volgende branche begint.
                 Rechter-padding houdt altijd lucht tot de tekstkolom: op brede schermen paste
                 "Financial services" anders net op één regel, tegen de tekst aan. -->
            <h2
                class="font-avenir text-[clamp(52px,4.88vw,80px)] leading-[0.9] font-black tracking-[-0.03em] text-[#ffc700] italic lg:sticky lg:top-[160px] lg:self-start lg:pt-3 lg:pr-[clamp(32px,3.5vw,72px)]"
            >
                {{ industry.name }}
            </h2>

            <div class="mt-8 min-w-0 lg:mt-0">
                <p
                    v-if="industry.headline"
                    class="max-w-[814px] font-avenir text-[clamp(32px,3.66vw,60px)] leading-[1.167] tracking-[-0.03em]"
                >
                    {{ industry.headline }}
                </p>

                <div
                    v-if="industry.whyTitle || industry.whyBody"
                    class="mt-10 max-w-[853px] font-avenir lg:mt-[50px]"
                >
                    <h3
                        v-if="industry.whyTitle"
                        class="text-[clamp(26px,2.07vw,34px)] leading-[1.12] font-black tracking-[-0.03em] text-[#ffc700] italic"
                    >
                        {{ industry.whyTitle }}
                    </h3>
                    <p
                        v-if="industry.whyLead"
                        class="mt-6 text-[clamp(20px,1.71vw,28px)] leading-[1.357] font-black tracking-[-0.03em] italic"
                    >
                        {{ industry.whyLead }}
                    </p>
                    <p
                        v-if="industry.whyBody"
                        class="text-[clamp(20px,1.71vw,28px)] leading-[1.357] tracking-[-0.03em] whitespace-pre-line"
                    >
                        {{ industry.whyBody }}
                    </p>
                </div>

                <!-- Figma: kaarten van 805×449, lopen rechts uit beeld -->
                <div
                    v-if="industry.cases.length"
                    class="mt-12 -mr-6 lg:mt-[54px] lg:-mr-[5.4vw]"
                >
                    <CasesCarousel
                        :cases="industry.cases"
                        card-width="min(805px, 85vw)"
                        aspect-ratio="805 / 449"
                        gap="clamp(16px, 2.2vw, 36px)"
                        controls-padding-right="max(24px, 5.4vw)"
                        :leading-gap="false"
                    />
                </div>

                <template v-if="industry.approachItems.length">
                    <h3
                        v-if="industry.approachTitle"
                        class="mt-16 font-avenir text-[clamp(26px,2.07vw,34px)] leading-[1.12] font-black tracking-[-0.03em] text-[#ffc700] italic lg:mt-[68px]"
                    >
                        {{ industry.approachTitle }}
                    </h3>
                    <ol class="mt-8 max-w-[936px] space-y-[29px]">
                        <li
                            v-for="(item, i) in industry.approachItems"
                            :key="item.title"
                            class="rounded-[15px] border border-white/30 px-6 py-8 font-avenir md:px-[60px] md:pt-[44px] md:pb-[48px]"
                        >
                            <h4
                                class="text-[clamp(30px,2.93vw,48px)] leading-[1.1] font-black tracking-[-0.03em] italic"
                            >
                                {{ i + 1 }}. {{ item.title }}
                            </h4>
                            <p
                                v-if="item.subtitle"
                                class="mt-2 text-[clamp(20px,1.71vw,28px)] leading-[1.357] tracking-[-0.03em] text-[#ffc700]"
                            >
                                {{ item.subtitle }}
                            </p>
                            <p
                                v-if="item.body"
                                class="mt-1 text-[clamp(17px,1.34vw,22px)] leading-[1.455] tracking-[-0.03em] text-[#b8b8b8]"
                            >
                                {{ item.body }}
                            </p>
                            <ul
                                v-if="item.bullets.length"
                                class="list-disc pl-[33px] text-[clamp(17px,1.34vw,22px)] leading-[1.455] tracking-[-0.03em] text-[#b8b8b8]"
                            >
                                <li
                                    v-for="bullet in item.bullets"
                                    :key="bullet"
                                >
                                    {{ bullet }}
                                </li>
                            </ul>
                        </li>
                    </ol>
                </template>

                <div
                    v-if="industry.beliefBody || industry.beliefHighlight"
                    class="mt-20 max-w-[814px] font-avenir lg:mt-[128px] lg:pl-[10px]"
                >
                    <h3
                        v-if="industry.beliefTitle"
                        class="text-[clamp(26px,2.07vw,34px)] leading-[1.12] font-black tracking-[-0.03em] text-[#ffc700] italic"
                    >
                        {{ industry.beliefTitle }}
                    </h3>
                    <p
                        class="mt-2 text-[clamp(34px,3.66vw,60px)] leading-[1.167] tracking-[-0.03em] whitespace-pre-line italic"
                    >
                        {{ industry.beliefBody }}
                        <strong
                            v-if="industry.beliefHighlight"
                            class="block font-black text-[#ffc700]"
                        >
                            {{ industry.beliefHighlight }}
                        </strong>
                    </p>
                </div>

                <!-- Figma: geel CTA-blok (936×322) -->
                <div
                    v-if="industry.ctaText"
                    class="mt-14 max-w-[936px] rounded-[16.6px] bg-[#ffc700] px-6 py-8 font-avenir text-black md:px-[55px] md:pt-[51px] md:pb-[53px] lg:mt-[85px]"
                >
                    <p
                        class="text-[clamp(18px,1.46vw,24px)] leading-[1.333] tracking-[-0.03em] whitespace-pre-line"
                    >
                        {{ industry.ctaText }}
                    </p>
                    <div
                        v-if="
                            industry.ctaPrimaryHref || industry.ctaSecondaryHref
                        "
                        class="mt-8 flex flex-wrap gap-4"
                    >
                        <Link
                            v-if="
                                industry.ctaPrimaryText &&
                                industry.ctaPrimaryHref
                            "
                            :href="industry.ctaPrimaryHref"
                            class="flex h-[62px] items-center gap-4 rounded-[30px] bg-black pr-11 pl-3 text-[16px] tracking-[-0.03em] text-white transition-opacity hover:opacity-80"
                        >
                            <CmsImage
                                v-if="ctaAvatar"
                                sizes="38px"
                                :src="ctaAvatar"
                                alt=""
                                class="size-[38px] rounded-full object-cover"
                            />
                            <img
                                v-else
                                loading="lazy"
                                :src="DEFAULT_CTA_AVATAR"
                                alt=""
                                width="38"
                                height="40"
                                class="size-[38px] rounded-full object-cover"
                            />
                            {{ industry.ctaPrimaryText }}
                        </Link>
                        <Link
                            v-if="
                                industry.ctaSecondaryText &&
                                industry.ctaSecondaryHref
                            "
                            :href="industry.ctaSecondaryHref"
                            class="flex h-[62px] items-center rounded-[30px] border border-black px-7 text-[16px] tracking-[-0.03em] transition-colors hover:bg-black hover:text-[#ffc700]"
                        >
                            {{ industry.ctaSecondaryText }}
                        </Link>
                    </div>
                </div>

                <!-- Proof: Impact (case + cijfers), Insights (blogs) en klantlogo's -->
                <div
                    v-if="
                        industry.impactStats.length ||
                        industry.insights.length ||
                        industry.proofLogos.length
                    "
                    class="mt-16 max-w-[1100px] font-avenir lg:mt-[118px] lg:pl-[7px]"
                >
                    <h3
                        v-if="industry.proofTitle"
                        class="text-[clamp(26px,2.07vw,34px)] leading-[1.12] font-black tracking-[-0.03em] text-[#ffc700] italic"
                    >
                        {{ industry.proofTitle }}
                    </h3>

                    <div
                        v-if="industry.impactStats.length"
                        class="mt-8 grid gap-10 lg:mt-[45px] lg:grid-cols-[minmax(0,1fr)_minmax(0,1.15fr)] lg:gap-[6vw]"
                    >
                        <div>
                            <p
                                class="text-[14px] tracking-[0.08em] text-white/50 uppercase"
                            >
                                Impact
                            </p>
                            <h4
                                v-if="industry.impactTitle"
                                class="mt-3 text-[clamp(20px,1.6vw,26px)] leading-[1.25] font-extrabold tracking-[-0.03em] text-white"
                            >
                                {{ industry.impactTitle }}
                            </h4>
                            <p
                                v-if="industry.impactBody"
                                class="mt-4 text-[16px] leading-[1.6] tracking-[-0.01em] whitespace-pre-line text-white/70 lg:text-[17px]"
                            >
                                {{ industry.impactBody }}
                            </p>
                            <Link
                                href="/cases"
                                class="mt-8 inline-flex h-[52px] items-center rounded-[30px] border border-white/60 px-7 text-[15px] tracking-[-0.03em] text-white transition-colors hover:border-[#ffc700] hover:bg-[#ffc700] hover:text-black"
                            >
                                View more cases
                            </Link>
                        </div>
                        <dl class="grid grid-cols-2 gap-x-6 gap-y-8 self-start">
                            <div
                                v-for="stat in industry.impactStats"
                                :key="stat.value + stat.label"
                                class="border-t border-white/15 pt-5"
                            >
                                <dt class="sr-only">{{ stat.label }}</dt>
                                <dd
                                    class="text-[clamp(40px,3.3vw,56px)] leading-none font-black tracking-[-0.03em] text-[#ffc700] italic"
                                >
                                    {{ stat.value }}
                                </dd>
                                <dd
                                    class="mt-3 text-[15px] leading-[1.35] tracking-[-0.01em] text-white/70 lg:text-[17px]"
                                    aria-hidden="true"
                                >
                                    {{ stat.label }}
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <div
                        v-if="industry.insights.length"
                        class="mt-16 lg:mt-[96px]"
                    >
                        <div
                            class="flex flex-wrap items-end justify-between gap-4"
                        >
                            <h4
                                class="text-[clamp(22px,1.7vw,28px)] leading-[1.12] font-black tracking-[-0.03em] text-[#ffc700] italic"
                            >
                                {{ industry.insightsTitle || 'Insights' }}
                            </h4>
                            <Link
                                href="/blog"
                                class="text-[15px] tracking-[-0.01em] text-white/70 underline-offset-4 transition-colors hover:text-[#ffc700] hover:underline"
                            >
                                View blog
                            </Link>
                        </div>
                        <ul class="mt-6 border-b border-white/15">
                            <li
                                v-for="post in industry.insights"
                                :key="post.slug"
                            >
                                <Link
                                    :href="`/blog/${post.slug}`"
                                    class="group flex items-center justify-between gap-6 border-t border-white/15 py-5 text-white"
                                >
                                    <span>
                                        <span
                                            v-if="post.category"
                                            class="block text-[13px] tracking-[0.06em] text-white/50 uppercase"
                                        >
                                            {{ post.category }}
                                        </span>
                                        <span
                                            class="mt-1 block text-[clamp(18px,1.4vw,22px)] leading-[1.3] font-extrabold tracking-[-0.02em] transition-colors group-hover:text-[#ffc700]"
                                        >
                                            {{ post.title }}
                                        </span>
                                    </span>
                                    <svg
                                        width="28"
                                        height="28"
                                        viewBox="0 0 28 28"
                                        fill="none"
                                        class="shrink-0 transition-transform duration-300 group-hover:translate-x-1 group-hover:text-[#ffc700]"
                                        aria-hidden="true"
                                    >
                                        <path
                                            d="M4 14h19M16 7l7 7-7 7"
                                            stroke="currentColor"
                                            stroke-width="1.6"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </svg>
                                </Link>
                            </li>
                        </ul>
                    </div>

                    <ul
                        v-if="industry.proofLogos.length"
                        class="mt-16 flex max-w-[886px] flex-wrap items-center justify-between gap-x-10 gap-y-8 lg:mt-[96px]"
                    >
                        <li
                            v-for="logo in industry.proofLogos"
                            :key="logo.logo"
                        >
                            <CmsImage
                                sizes="180px"
                                :src="logo.logo"
                                :alt="logo.name"
                                class="h-auto max-h-[68px] w-auto max-w-[180px] object-contain"
                            />
                        </li>
                    </ul>
                </div>
            </div>
        </section>

        <div class="h-24 lg:h-[160px]" />

        <PagePills page="industries" />

        <SiteFooter id="contact" />
    </div>
</template>
