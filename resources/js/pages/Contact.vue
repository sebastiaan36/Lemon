<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import CmsImage from '@/components/CmsImage.vue';
import HlsVideo from '@/components/HlsVideo.vue';
import SiteFooter from '@/components/SiteFooter.vue';
import SiteHeader from '@/components/SiteHeader.vue';
import { telHref } from '@/lib/siteNavigation';

type TeamMember = {
    name: string;
    role: string;
    phone: string | null;
    email: string | null;
    photo: string | null;
};

type Job = {
    title: string;
    slug: string;
};

const props = defineProps<{
    seoTitle?: string | null;
    metaDescription?: string | null;
    heroTitle?: string | null;
    heroBgImage?: string | null;
    heroAddress?: string | null;
    heroPhone?: string | null;
    heroEmail?: string | null;
    introText?: string | null;
    teamMembers?: TeamMember[];
    clientLogosLabel?: string | null;
    clientLogos?: string[];
    joinIntroText?: string | null;
    joinTitleLine1?: string | null;
    joinTitleLine2?: string | null;
    joinJobs?: Job[];
    joinButtonText?: string | null;
}>();

// Iedere medewerker toont telefoon- en mailicoon (Figma); ontbreken eigen
// gegevens, dan vallen ze terug op het algemene nummer en adres.
const FALLBACK_PHONE = '+31 (0) 20 606 3580';
const FALLBACK_EMAIL = 'info@lemonscentedtea.com';

function memberPhone(member: TeamMember): string {
    return member.phone || props.heroPhone || FALLBACK_PHONE;
}

function memberEmail(member: TeamMember): string {
    return member.email || props.heroEmail || FALLBACK_EMAIL;
}

const currentTeamIndex = ref(0);
const teamScrollEl = ref<HTMLElement | null>(null);

const heroInfoItems = computed(() =>
    [props.heroAddress, props.heroPhone, props.heroEmail].filter(Boolean),
);

function scrollBelowHero() {
    document
        .getElementById('below-hero')
        ?.scrollIntoView({ behavior: 'smooth' });
}

function scrollTeamTo(index: number) {
    if (!teamScrollEl.value) {
        return;
    }

    const cards =
        teamScrollEl.value.querySelectorAll<HTMLElement>('[data-team-card]');
    const card = cards[index];

    if (!card) {
        return;
    }

    const containerLeft = teamScrollEl.value.getBoundingClientRect().left;
    const cardLeft = card.getBoundingClientRect().left;
    teamScrollEl.value.scrollLeft += cardLeft - containerLeft;
    currentTeamIndex.value = index;
}

function teamPrev() {
    if (currentTeamIndex.value > 0) {
        scrollTeamTo(currentTeamIndex.value - 1);
    }
}

function teamNext() {
    const max = (props.teamMembers?.length ?? 0) - 1;

    if (currentTeamIndex.value < max) {
        scrollTeamTo(currentTeamIndex.value + 1);
    }
}

function pad2(n: number) {
    return String(n + 1).padStart(2, '0');
}

function isVideo(url: string | null | undefined) {
    return !!url && /\.(mp4|webm|ogg|mov|m3u8)(\?.*)?$/i.test(url);
}
</script>

<template>
    <Head>
        <title>{{ seoTitle || 'Contact — Lemon Scented Tea' }}</title>
        <meta
            v-if="metaDescription"
            name="description"
            :content="metaDescription"
        />
    </Head>

    <div class="bg-[#ffc700]">
        <SiteHeader />

        <!-- ============ HERO ============ -->
        <!-- Mobiel precies de zichtbare viewporthoogte (svh, mét adresbalk); desktop min. 100vh -->
        <section
            class="relative h-svh overflow-hidden md:h-auto md:min-h-screen"
        >
            <!-- background image/video -->
            <div class="absolute inset-0">
                <HlsVideo
                    v-if="isVideo(heroBgImage)"
                    :src="heroBgImage!"
                    autoplay
                    muted
                    loop
                    playsinline
                    class="h-full w-full object-cover"
                />
                <CmsImage
                    priority
                    v-else-if="heroBgImage"
                    :src="heroBgImage"
                    alt=""
                    class="h-full w-full object-cover"
                />
                <div class="absolute inset-0 bg-black/40" />
            </div>

            <!-- Title (centered) -->
            <div
                class="relative flex h-full flex-col items-center justify-center px-5 md:h-auto md:min-h-screen md:px-[59px]"
            >
                <h1
                    class="text-center font-avenir text-[clamp(56px,17vw,90px)] leading-[0.87] tracking-[-0.035em] text-[#ffc700] lg:text-[130px]"
                    style="font-weight: 900; font-style: oblique"
                >
                    {{ heroTitle || 'Get in touch' }}
                </h1>
            </div>

            <!-- Contact info row (bottom) -->
            <div
                v-if="heroInfoItems.length"
                class="absolute right-0 bottom-[110px] left-0 flex flex-col flex-wrap items-center justify-center gap-x-8 gap-y-1 px-5 text-center md:bottom-[120px] md:flex-row md:gap-y-2 md:px-[59px]"
            >
                <span
                    v-if="heroAddress"
                    class="font-avenir text-[18px] tracking-[-0.5px] text-white lg:text-[22px]"
                    >{{ heroAddress }}</span
                >
                <a
                    v-if="heroPhone"
                    :href="telHref(heroPhone)"
                    class="font-avenir text-[18px] tracking-[-0.5px] text-white lg:text-[22px]"
                    style="text-decoration: none"
                    >{{ heroPhone }}</a
                >
                <a
                    v-if="heroEmail"
                    :href="`mailto:${heroEmail}`"
                    class="font-avenir text-[18px] tracking-[-0.5px] text-white lg:text-[22px]"
                    style="text-decoration: none"
                    >{{ heroEmail }}</a
                >
            </div>

            <!-- Scroll indicator -->
            <button
                class="absolute bottom-10 left-1/2 z-10 flex h-[44px] w-[44px] -translate-x-1/2 items-center justify-center rounded-full border border-[#ffc700] transition-colors hover:bg-[#ffc700]/20"
                @click="scrollBelowHero"
            >
                <svg
                    class="h-4 w-4 text-[#ffc700]"
                    viewBox="0 0 16 16"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.5"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <path d="M8 3v10M3 9l5 5 5-5" />
                </svg>
            </button>
        </section>

        <div id="below-hero" />

        <!-- ============ INTRO TEXT ============ -->
        <section
            v-if="introText"
            class="px-5 py-[64px] md:px-[59px] md:py-[80px] lg:py-[120px]"
        >
            <p
                class="mx-auto max-w-[780px] text-center font-avenir text-[22px] leading-[1.45] tracking-[-0.02em] text-black md:text-[28px] md:leading-[1.5] lg:text-[36px] lg:leading-[1.35]"
            >
                {{ introText }}
            </p>
        </section>

        <!-- ============ MEET THE LEMONS ============ -->
        <section
            v-if="teamMembers && teamMembers.length"
            class="flex flex-col items-start gap-8 py-[64px] md:flex-row md:gap-0 lg:py-[120px]"
        >
            <!-- Title column -->
            <div
                class="w-full shrink-0 px-5 pt-2 md:w-[300px] md:px-0 md:pl-[59px] lg:w-[420px]"
            >
                <h2
                    class="font-avenir text-[36px] leading-tight tracking-[-0.03em] text-black md:text-[40px] lg:text-[50px]"
                    style="font-weight: 900; font-style: oblique"
                >
                    Meet the<br />Lemons
                </h2>
            </div>

            <!-- Carousel column -->
            <div class="w-full min-w-0 flex-1">
                <div
                    ref="teamScrollEl"
                    class="flex snap-x snap-mandatory scroll-px-5 gap-4 overflow-x-auto scroll-smooth px-5 pb-4 md:snap-none md:gap-5 md:px-0 md:pr-[59px]"
                    style="scrollbar-width: none; -ms-overflow-style: none"
                >
                    <div
                        v-for="(member, i) in teamMembers"
                        :key="i"
                        data-team-card
                        class="relative shrink-0 snap-start overflow-hidden rounded-[20px] bg-neutral-800"
                        style="width: min(340px, 72vw); aspect-ratio: 2/3"
                    >
                        <CmsImage
                            sizes="(min-width: 472px) 340px, 72vw"
                            v-if="member.photo"
                            :src="member.photo"
                            :alt="member.name"
                            class="h-full w-full object-cover"
                        />
                        <div
                            class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent"
                        />
                        <!-- Name + role bottom-left -->
                        <div class="absolute bottom-0 left-0 p-4 md:p-6">
                            <p
                                class="font-avenir text-[24px] leading-none tracking-[-0.03em] text-[#ffc700] md:text-[28px]"
                                style="font-weight: 900; font-style: oblique"
                            >
                                {{ member.name }}
                            </p>
                            <p
                                class="mt-1 font-avenir text-[14px] font-semibold tracking-[-0.4px] text-white"
                            >
                                {{ member.role }}
                            </p>
                        </div>
                        <!-- Contact icons bottom-right -->
                        <div
                            class="absolute right-0 bottom-0 flex gap-1 p-4 md:gap-2 md:p-6"
                        >
                            <a
                                :href="telHref(memberPhone(member))"
                                :aria-label="`Bel ${member.name}`"
                                class="size-10 md:size-[50px]"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="50"
                                    height="50"
                                    class="h-full w-full"
                                    viewBox="0 0 60 60"
                                    fill="none"
                                >
                                    <circle
                                        opacity="0.23"
                                        cx="29.6826"
                                        cy="29.6826"
                                        r="29.2188"
                                        stroke="white"
                                        stroke-width="0.927582"
                                    />
                                    <path
                                        d="M29.6822 35.2468H29.6915M25.0445 20.4062H34.3199C35.3444 20.4062 36.175 21.2368 36.175 22.2613V37.1019C36.175 38.1264 35.3444 38.957 34.3199 38.957H25.0445C24.02 38.957 23.1895 38.1264 23.1895 37.1019V22.2613C23.1895 21.2368 24.02 20.4062 25.0445 20.4062Z"
                                        stroke="#FFC700"
                                        stroke-width="1.85516"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            </a>
                            <a
                                :href="`mailto:${memberEmail(member)}`"
                                :aria-label="`Mail ${member.name}`"
                                class="size-10 md:size-[50px]"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="50"
                                    height="50"
                                    class="h-full w-full"
                                    viewBox="0 0 60 60"
                                    fill="none"
                                >
                                    <circle
                                        opacity="0.23"
                                        cx="29.6826"
                                        cy="29.6826"
                                        r="29.2188"
                                        stroke="white"
                                        stroke-width="0.927582"
                                    />
                                    <path
                                        d="M38.9584 25.0443L30.6185 30.3563C30.3355 30.5207 30.0141 30.6073 29.6868 30.6073C29.3595 30.6073 29.0381 30.5207 28.7551 30.3563L20.4073 25.0443M22.2623 22.2617H37.1034C38.1274 22.2617 38.9584 23.0923 38.9584 24.1168V35.2472C38.9584 36.2718 38.1274 37.1023 37.1034 37.1023H22.2623C21.2378 37.1023 20.4073 36.2718 20.4073 35.2472V24.1168C20.4073 23.0923 21.2378 22.2617 22.2623 22.2617Z"
                                        stroke="#FFC700"
                                        stroke-width="1.85516"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Navigation -->
                <div
                    class="mt-6 flex items-center justify-between px-5 md:px-0 md:pr-[59px]"
                >
                    <p
                        class="font-avenir text-[14px] tracking-[-0.4px] text-black/50"
                    >
                        {{ pad2(currentTeamIndex) }} —
                        {{ String(teamMembers?.length ?? 1).padStart(2, '0') }}
                    </p>
                    <div class="flex gap-3">
                        <button
                            class="flex h-[44px] w-[44px] items-center justify-center rounded-full border border-black/20 transition-colors hover:border-black/60"
                            :disabled="currentTeamIndex === 0"
                            :class="currentTeamIndex === 0 ? 'opacity-30' : ''"
                            @click="teamPrev"
                        >
                            <svg
                                class="h-4 w-4"
                                viewBox="0 0 16 16"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path d="M10 3L5 8l5 5" />
                            </svg>
                        </button>
                        <button
                            class="flex h-[44px] w-[44px] items-center justify-center rounded-full border border-black/20 transition-colors hover:border-black/60"
                            :disabled="
                                currentTeamIndex >=
                                (teamMembers?.length ?? 1) - 1
                            "
                            :class="
                                currentTeamIndex >=
                                (teamMembers?.length ?? 1) - 1
                                    ? 'opacity-30'
                                    : ''
                            "
                            @click="teamNext"
                        >
                            <svg
                                class="h-4 w-4"
                                viewBox="0 0 16 16"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path d="M6 3l5 5-5 5" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============ CLIENT LOGOS ============ -->
        <section
            v-if="clientLogos && clientLogos.length"
            class="border-t border-black/10 py-[60px]"
        >
            <p
                class="mb-8 flex items-center gap-3 px-5 font-avenir text-[22px] tracking-[-0.02em] text-black md:px-[59px] md:text-[30px]"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="21"
                    height="21"
                    viewBox="0 0 23 23"
                    fill="none"
                    class="shrink-0"
                >
                    <path
                        d="M8.18557 21.7141L21.7143 21.7141L21.7143 7.5796"
                        stroke="black"
                        stroke-width="2.01919"
                    />
                    <path
                        d="M21.3099 21.3098L0.713889 0.713818"
                        stroke="black"
                        stroke-width="2.01919"
                    />
                </svg>
                {{ clientLogosLabel || 'These brands called us before' }}
            </p>
            <!-- Marquee -->
            <div class="overflow-hidden py-2">
                <div class="animate-marquee flex w-max items-center gap-20">
                    <CmsImage
                        sizes="160px"
                        v-for="(logo, i) in [
                            ...clientLogos,
                            ...clientLogos,
                            ...clientLogos,
                            ...clientLogos,
                        ]"
                        :key="i"
                        :src="logo"
                        alt=""
                        class="h-[40px] w-auto max-w-[160px] object-contain opacity-70"
                    />
                </div>
            </div>
        </section>

        <!-- ============ JOIN SECTION ============ -->
        <section
            class="bg-black px-5 py-[90px] md:px-[59px] md:py-[100px] lg:py-[140px]"
        >
            <p
                v-if="joinIntroText"
                class="mb-8 text-center font-avenir text-[18px] tracking-[-0.54px] text-[#ffc700]"
            >
                {{ joinIntroText }}
            </p>

            <h2
                class="mb-10 text-center font-avenir text-[clamp(56px,17vw,80px)] leading-none tracking-[-0.035em] md:mb-12 lg:text-[130px]"
                style="font-weight: 900; font-style: oblique"
            >
                <!-- Expliciete spatie: Vue haalt witruimte aan het eind van de span weg -->
                <span class="text-white">{{ joinTitleLine1 || 'Join' }}</span
                >{{ ' '
                }}<span class="text-[#ffc700]">{{
                    joinTitleLine2 || 'lemon'
                }}</span>
            </h2>

            <!-- Jobs list -->
            <div
                v-if="joinJobs && joinJobs.length"
                class="mb-10 flex flex-col items-center gap-1 md:mb-12"
            >
                <!-- Figma: compacte lijst, eerste vacature uitgelicht in wit -->
                <a
                    v-for="(job, i) in joinJobs"
                    :key="i"
                    :href="`/jobs#${job.slug}`"
                    class="text-center font-avenir text-[19px] leading-[1.3] tracking-[-0.02em] transition-colors hover:text-white md:text-[22px] lg:text-[26px]"
                    :class="i === 0 ? 'text-white' : 'text-white/40'"
                    style="
                        font-weight: 900;
                        font-style: oblique;
                        text-decoration: none;
                    "
                >
                    {{ job.title }}
                </a>
            </div>

            <!-- Button -->
            <div v-if="joinButtonText" class="flex justify-center">
                <a
                    href="/jobs"
                    class="flex h-[65px] items-center justify-center rounded-[35px] border border-white px-10 font-avenir text-[20px] tracking-[-0.6px] text-white transition-colors hover:bg-white hover:text-black"
                >
                    {{ joinButtonText }}
                </a>
            </div>
        </section>

        <SiteFooter id="contact" />
    </div>
</template>

<style scoped>
section div::-webkit-scrollbar {
    display: none;
}

@keyframes marquee {
    from {
        transform: translateX(0);
    }
    to {
        transform: translateX(-25%);
    }
}

.animate-marquee {
    animation: marquee 20s linear infinite;
}
</style>
