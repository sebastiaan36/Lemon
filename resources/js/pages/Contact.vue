<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import SiteFooter from '@/components/SiteFooter.vue'
import SiteHeader from '@/components/SiteHeader.vue'

type TeamMember = {
    name: string
    role: string
    photo: string | null
}

type Job = {
    title: string
    is_featured: boolean
}

const props = defineProps<{
    seoTitle?: string | null
    metaDescription?: string | null
    heroTitle?: string | null
    heroBgImage?: string | null
    heroAddress?: string | null
    heroPhone?: string | null
    heroEmail?: string | null
    introText?: string | null
    teamMembers?: TeamMember[]
    clientLogosLabel?: string | null
    clientLogos?: string[]
    joinIntroText?: string | null
    joinTitleLine1?: string | null
    joinTitleLine2?: string | null
    joinJobs?: Job[]
    joinButtonText?: string | null
    joinButtonHref?: string | null
}>()

const currentTeamIndex = ref(0)
const teamScrollEl = ref<HTMLElement | null>(null)

const heroInfoItems = computed(() =>
    [props.heroAddress, props.heroPhone, props.heroEmail].filter(Boolean),
)

function scrollTeamTo(index: number) {
    if (!teamScrollEl.value) return
    const cards = teamScrollEl.value.querySelectorAll<HTMLElement>('[data-team-card]')
    const card = cards[index]
    if (!card) return
    const containerLeft = teamScrollEl.value.getBoundingClientRect().left
    const cardLeft = card.getBoundingClientRect().left
    teamScrollEl.value.scrollLeft += cardLeft - containerLeft
    currentTeamIndex.value = index
}

function teamPrev() {
    if (currentTeamIndex.value > 0) scrollTeamTo(currentTeamIndex.value - 1)
}

function teamNext() {
    const max = (props.teamMembers?.length ?? 0) - 1
    if (currentTeamIndex.value < max) scrollTeamTo(currentTeamIndex.value + 1)
}

function pad2(n: number) {
    return String(n + 1).padStart(2, '0')
}

function isVideo(url: string | null | undefined) {
    return !!url && /\.(mp4|webm|ogg|mov|m3u8)(\?.*)?$/i.test(url)
}
</script>

<template>
    <Head>
        <title>{{ seoTitle || 'Contact — Lemon Scented Tea' }}</title>
        <meta v-if="metaDescription" name="description" :content="metaDescription" />
    </Head>

    <div class="bg-[#ffc700]">
        <SiteHeader />

        <!-- ============ HERO ============ -->
        <section class="relative h-[500px] overflow-hidden rounded-b-[30px] lg:h-[580px]">
            <!-- background image/video -->
            <div class="absolute inset-0">
                <video
                    v-if="isVideo(heroBgImage)"
                    :src="heroBgImage!"
                    autoplay muted loop playsinline
                    class="h-full w-full object-cover"
                />
                <img
                    v-else-if="heroBgImage"
                    :src="heroBgImage"
                    alt=""
                    class="h-full w-full object-cover"
                />
                <div class="absolute inset-0 bg-black/40" />
            </div>

            <!-- Title -->
            <div class="relative flex h-full flex-col items-center justify-center px-[59px]">
                <h1
                    class="text-center text-[90px] leading-[0.87] tracking-[-4.5px] text-[#ffc700] lg:text-[130px]"
                    style="font-family: 'Avenir', system-ui, sans-serif; font-weight: 900; font-style: oblique;"
                >
                    {{ heroTitle || 'Get in touch' }}
                </h1>

                <!-- Contact info row -->
                <div
                    v-if="heroInfoItems.length"
                    class="mt-8 flex flex-wrap items-center justify-center gap-x-8 gap-y-2"
                >
                    <span
                        v-for="item in heroInfoItems"
                        :key="item"
                        class="text-[18px] tracking-[-0.5px] text-white lg:text-[22px]"
                        style="font-family: 'Avenir', system-ui, sans-serif;"
                    >
                        {{ item }}
                    </span>
                </div>
            </div>
        </section>

        <!-- ============ INTRO TEXT ============ -->
        <section v-if="introText" class="px-[59px] py-[80px] lg:py-[120px]">
            <p
                class="mx-auto max-w-[780px] text-center text-[28px] leading-[1.5] tracking-[-0.84px] text-black lg:text-[36px] lg:leading-[1.35]"
                style="font-family: 'Avenir', system-ui, sans-serif;"
            >
                {{ introText }}
            </p>
        </section>

        <!-- ============ MEET THE LEMONS ============ -->
        <section v-if="teamMembers && teamMembers.length" class="pb-[80px] lg:pb-[120px]">
            <div class="mb-10 px-[59px]">
                <h2
                    class="text-[40px] leading-tight tracking-[-1.5px] text-black lg:text-[50px]"
                    style="font-family: 'Avenir', system-ui, sans-serif; font-weight: 900; font-style: oblique;"
                >
                    Meet the<br />Lemons
                </h2>
            </div>

            <!-- Carousel -->
            <div
                ref="teamScrollEl"
                class="flex gap-5 overflow-x-auto scroll-smooth px-[59px] pb-4"
                style="scrollbar-width: none; -ms-overflow-style: none;"
            >
                <div
                    v-for="(member, i) in teamMembers"
                    :key="i"
                    data-team-card
                    class="relative shrink-0 overflow-hidden rounded-[20px] bg-neutral-800"
                    style="width: min(340px, 75vw); aspect-ratio: 2/3;"
                >
                    <img
                        v-if="member.photo"
                        :src="member.photo"
                        :alt="member.name"
                        class="h-full w-full object-cover"
                    />
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent" />
                    <div class="absolute bottom-0 left-0 p-6">
                        <p
                            class="text-[28px] leading-none tracking-[-0.84px] text-[#ffc700]"
                            style="font-family: 'Avenir', system-ui, sans-serif; font-weight: 900; font-style: oblique;"
                        >
                            {{ member.name }}
                        </p>
                        <p
                            class="mt-1 text-[14px] font-semibold tracking-[-0.4px] text-white"
                            style="font-family: 'Avenir', system-ui, sans-serif;"
                        >
                            {{ member.role }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <div class="mt-6 flex items-center justify-between px-[59px]">
                <p
                    class="text-[14px] tracking-[-0.4px] text-black/50"
                    style="font-family: 'Avenir', system-ui, sans-serif;"
                >
                    {{ pad2(currentTeamIndex) }} — {{ pad2((teamMembers?.length ?? 1) - 1) }}
                </p>
                <div class="flex gap-3">
                    <button
                        class="flex h-[44px] w-[44px] items-center justify-center rounded-full border border-black/20 transition-colors hover:border-black/60"
                        :disabled="currentTeamIndex === 0"
                        :class="currentTeamIndex === 0 ? 'opacity-30' : ''"
                        @click="teamPrev"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M10 3L5 8l5 5" />
                        </svg>
                    </button>
                    <button
                        class="flex h-[44px] w-[44px] items-center justify-center rounded-full border border-black/20 transition-colors hover:border-black/60"
                        :disabled="currentTeamIndex >= (teamMembers?.length ?? 1) - 1"
                        :class="currentTeamIndex >= (teamMembers?.length ?? 1) - 1 ? 'opacity-30' : ''"
                        @click="teamNext"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6 3l5 5-5 5" />
                        </svg>
                    </button>
                </div>
            </div>
        </section>

        <!-- ============ CLIENT LOGOS ============ -->
        <section v-if="clientLogos && clientLogos.length" class="border-t border-black/10 px-[59px] py-[60px]">
            <p
                class="mb-8 flex items-center gap-3 text-[18px] tracking-[-0.54px] text-black lg:text-[22px]"
                style="font-family: 'Avenir', system-ui, sans-serif;"
            >
                <svg class="h-4 w-4 -rotate-90 shrink-0" viewBox="0 0 16 16" fill="none">
                    <path d="M8 2v12M2 8l6 6 6-6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                {{ clientLogosLabel || 'These brands called us before' }}
            </p>
            <div class="flex flex-wrap items-center gap-x-12 gap-y-6">
                <img
                    v-for="(logo, i) in clientLogos"
                    :key="i"
                    :src="logo"
                    alt=""
                    class="h-[28px] w-auto object-contain opacity-80"
                />
            </div>
        </section>

        <!-- ============ JOIN SECTION ============ -->
        <section class="bg-black px-[59px] py-[100px] lg:py-[140px]">
            <p
                v-if="joinIntroText"
                class="mb-8 text-center text-[18px] tracking-[-0.54px] text-[#ffc700]"
                style="font-family: 'Avenir', system-ui, sans-serif;"
            >
                {{ joinIntroText }}
            </p>

            <h2
                class="mb-12 text-center text-[80px] leading-none tracking-[-4.5px] lg:text-[130px]"
                style="font-family: 'Avenir', system-ui, sans-serif; font-weight: 900; font-style: oblique;"
            >
                <span class="text-white">{{ joinTitleLine1 || 'Join' }} </span>
                <span class="text-[#ffc700]">{{ joinTitleLine2 || 'lemon' }}</span>
            </h2>

            <!-- Jobs list -->
            <div v-if="joinJobs && joinJobs.length" class="mb-12 flex flex-col items-center gap-2">
                <p
                    v-for="(job, i) in joinJobs"
                    :key="i"
                    class="text-center leading-[1.25] tracking-[-1.2px] transition-opacity"
                    :class="job.is_featured
                        ? 'text-[32px] text-white lg:text-[40px]'
                        : 'text-[24px] text-white/25 lg:text-[32px]'"
                    style="font-family: 'Avenir', system-ui, sans-serif; font-weight: 900; font-style: oblique;"
                >
                    {{ job.title }}
                </p>
            </div>

            <!-- Button -->
            <div v-if="joinButtonText" class="flex justify-center">
                <a
                    :href="joinButtonHref || '#'"
                    class="flex h-[65px] items-center justify-center rounded-[35px] border border-white px-10 text-[20px] tracking-[-0.6px] text-white transition-colors hover:bg-white hover:text-black"
                    style="font-family: 'Avenir', system-ui, sans-serif;"
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
</style>
