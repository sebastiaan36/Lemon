<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import SiteFooter from '@/components/SiteFooter.vue'
import SiteHeader from '@/components/SiteHeader.vue'

type VisualItem = {
    image: string | null
}

type ResultStat = {
    prefix?: string | null
    value: string
    label: string
}

type RelatedCase = {
    name: string
    slug: string
    photo: string | null
    video: string | null
}

const props = defineProps<{
    caseStudy: {
        name: string
        slug: string
        clientName?: string | null
        accentColor?: string | null
        heroTitle?: string | null
        heroSubtitle?: string | null
        heroMedia?: string | null
        heroDuration?: string | null
        introLogo?: string | null
        touchpoints?: string[]
        overviewTitle?: string | null
        overviewBody?: string | null
        galleryItems?: VisualItem[]
        highlightTitle?: string | null
        highlightBody?: string | null
        highlightMedia?: string | null
        highlightButtonText?: string | null
        campaignTitle?: string | null
        campaignBody?: string | null
        campaignMedia?: string | null
        storyTitle?: string | null
        storyBody?: string | null
        storyImages?: VisualItem[]
        resultsHeading?: string | null
        resultsStats?: ResultStat[]
        optionalPanelTitle?: string | null
        optionalPanelBody?: string | null
        optionalPanelButtonText?: string | null
    }
    moreCases?: RelatedCase[]
}>()

function isVideo(url: string | null | undefined): boolean {
    return !!url && /\.(mp4|webm|ogg|mov)(\?.*)?$/i.test(url)
}

const accentColor = props.caseStudy.accentColor || '#0A7949'
const siteItems = [
    { label: 'About', href: '/#about' },
    { label: 'CxByEx', href: '/#services' },
    { label: 'Cases', href: '/#cases' },
    { label: 'Industries', href: '/#services' },
    { label: 'Contact', href: '/#contact' },
]
</script>

<template>
    <Head :title="caseStudy.name" />

    <div class="bg-[#fcfcfc] text-[#101010]">
        <SiteHeader :nav-color="accentColor" contact-href="/#contact" :menu-items="siteItems" />

        <main>
            <section class="px-[59px] pt-[140px] pb-[70px]">
                <p
                    class="mb-4 text-center text-[30px] leading-[1.1] tracking-[-0.3px]"
                    style="font-family: 'Avenir', sans-serif;"
                >
                    {{ caseStudy.heroSubtitle || caseStudy.clientName }}
                </p>
                <h1
                    class="mx-auto mb-12 max-w-[700px] text-center text-[137px] leading-[0.9] tracking-[-4.11px]"
                    :style="{ color: accentColor, fontFamily: '\'Avenir\', sans-serif', fontWeight: '900', fontStyle: 'oblique' }"
                >
                    {{ caseStudy.heroTitle }}
                </h1>

                <div class="relative mx-auto max-w-[1529px] overflow-hidden rounded-[20px] bg-neutral-200">
                    <video
                        v-if="caseStudy.heroMedia && isVideo(caseStudy.heroMedia)"
                        :src="caseStudy.heroMedia"
                        autoplay
                        loop
                        muted
                        playsinline
                        class="aspect-[1529/901] w-full object-cover"
                    />
                    <img
                        v-else-if="caseStudy.heroMedia"
                        :src="caseStudy.heroMedia"
                        :alt="caseStudy.name"
                        class="aspect-[1529/901] w-full object-cover"
                    />
                    <div v-else class="aspect-[1529/901] w-full bg-neutral-200" />

                    <span
                        v-if="caseStudy.heroDuration"
                        class="absolute right-6 bottom-6 text-[15px] tracking-[-0.47px] text-white"
                        style="font-family: 'Avenir', sans-serif;"
                    >
                        {{ caseStudy.heroDuration }}
                    </span>
                </div>
            </section>

            <section class="border-t border-black/15 px-[59px] py-[72px]">
                <div class="grid grid-cols-[281px_1fr_281px] items-start gap-10">
                    <div>
                        <div v-if="caseStudy.introLogo" class="mb-6 h-6">
                            <img :src="caseStudy.introLogo" alt="" class="h-full w-auto object-contain" />
                        </div>
                        <p class="text-[12px] leading-[22px]" style="font-family: 'Avenir', sans-serif; font-weight: 300;">
                            Client
                        </p>
                        <p class="text-[18px] leading-[22px] tracking-[-0.54px]" style="font-family: 'Avenir', sans-serif;">
                            {{ caseStudy.clientName }}
                        </p>
                    </div>

                    <div />

                    <div class="text-right">
                        <p class="text-[12px] leading-[22px]" style="font-family: 'Avenir', sans-serif; font-weight: 300;">
                            Touchpoints
                        </p>
                        <p class="text-[18px] leading-[22px] tracking-[-0.54px]" style="font-family: 'Avenir', sans-serif;">
                            {{ (caseStudy.touchpoints || []).join(', ') }}
                        </p>
                    </div>
                </div>

                <div class="mt-20 grid grid-cols-[555px_638px] justify-between gap-10">
                    <h2
                        class="text-[60px] leading-[70px] tracking-[-1.8px]"
                        :style="{ color: accentColor, fontFamily: '\'Avenir\', sans-serif' }"
                    >
                        {{ caseStudy.overviewTitle }}
                    </h2>
                    <p
                        class="text-[28px] leading-[38px] tracking-[-0.84px]"
                        style="font-family: 'Avenir', sans-serif;"
                    >
                        {{ caseStudy.overviewBody }}
                    </p>
                </div>
            </section>

            <section v-if="(caseStudy.galleryItems || []).length" class="px-[59px] py-[90px]">
                <div class="grid grid-cols-3 gap-8">
                    <div
                        v-for="(item, index) in caseStudy.galleryItems"
                        :key="index"
                        class="overflow-hidden rounded-[20px] bg-neutral-200"
                    >
                        <img v-if="item.image" :src="item.image" alt="" class="h-full w-full object-cover" />
                    </div>
                </div>
            </section>

            <section class="mt-10 bg-[#0A7949] px-[59px] py-[120px]" :style="{ backgroundColor: accentColor }">
                <h2
                    class="mx-auto max-w-[1192px] text-center text-[140px] leading-[130px] tracking-[-4.2px] text-white"
                    style="font-family: 'Avenir', sans-serif; font-weight: 900; font-style: oblique;"
                >
                    {{ caseStudy.highlightTitle }}
                </h2>
                <p
                    class="mx-auto mt-10 max-w-[765px] text-center text-[22px] leading-[32px] tracking-[-0.66px] text-white"
                    style="font-family: 'Avenir', sans-serif;"
                >
                    {{ caseStudy.highlightBody }}
                </p>
                <div v-if="caseStudy.highlightButtonText" class="mt-10 flex justify-center">
                    <a
                        href="#contact"
                        class="rounded-[30px] border border-white px-8 py-3 text-[20px] tracking-[-0.6px] text-white"
                        style="font-family: 'Avenir', sans-serif;"
                    >
                        {{ caseStudy.highlightButtonText }}
                    </a>
                </div>
            </section>

            <section v-if="caseStudy.campaignMedia" class="relative">
                <div class="relative h-[994px] overflow-hidden bg-black">
                    <img
                        v-if="!isVideo(caseStudy.campaignMedia)"
                        :src="caseStudy.campaignMedia"
                        alt=""
                        class="h-full w-full object-cover"
                    />
                    <video
                        v-else
                        :src="caseStudy.campaignMedia"
                        autoplay
                        loop
                        muted
                        playsinline
                        class="h-full w-full object-cover"
                    />
                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-transparent to-black/35" />
                    <div class="absolute right-[211px] bottom-[278px] w-[437px] text-white">
                        <h3 class="text-[32px] leading-[38px] tracking-[-0.96px]" style="font-family: 'Avenir', sans-serif;">
                            {{ caseStudy.campaignTitle }}
                        </h3>
                        <p class="mt-6 text-[18px] leading-[28px] tracking-[-0.54px]" style="font-family: 'Avenir', sans-serif;">
                            {{ caseStudy.campaignBody }}
                        </p>
                    </div>
                </div>
            </section>

            <section class="px-[59px] py-[120px]">
                <div class="grid grid-cols-[380px_598px_598px] items-start gap-6">
                    <div>
                        <h2
                            class="text-[50px] leading-[60px] tracking-[-1.5px]"
                            :style="{ color: accentColor, fontFamily: '\'Avenir\', sans-serif' }"
                        >
                            {{ caseStudy.storyTitle }}
                        </h2>
                        <p class="mt-10 max-w-[373px] text-[18px] leading-[28px] tracking-[-0.54px]" style="font-family: 'Avenir', sans-serif;">
                            {{ caseStudy.storyBody }}
                        </p>
                    </div>

                    <div
                        v-for="(item, index) in caseStudy.storyImages"
                        :key="index"
                        class="overflow-hidden rounded-[17px] bg-neutral-200"
                    >
                        <img v-if="item.image" :src="item.image" alt="" class="h-[919px] w-full object-cover" />
                    </div>
                </div>
            </section>

            <section class="border-t border-black/10 px-[59px] py-[120px]">
                <h2
                    class="mb-16 text-[50px] leading-[60px] tracking-[-1.5px]"
                    :style="{ color: accentColor, fontFamily: '\'Avenir\', sans-serif' }"
                >
                    {{ caseStudy.resultsHeading }}
                </h2>

                <div class="grid grid-cols-3 gap-12">
                    <div
                        v-for="(stat, index) in caseStudy.resultsStats"
                        :key="index"
                        class="border-r border-black/10 pr-10 last:border-r-0"
                    >
                        <p
                            v-if="stat.prefix"
                            class="mb-2 text-[50px] leading-[50px] tracking-[-1.5px]"
                            :style="{ color: accentColor, fontFamily: '\'Avenir\', sans-serif' }"
                        >
                            {{ stat.prefix }}
                        </p>
                        <p
                            class="text-[160px] leading-[0.95] tracking-[-4.8px]"
                            :style="{ color: accentColor, fontFamily: '\'Avenir\', sans-serif', fontWeight: '900', fontStyle: 'oblique' }"
                        >
                            {{ stat.value }}
                        </p>
                        <p
                            class="mt-4 text-[24px] leading-[60px] tracking-[-0.72px]"
                            :style="{ color: accentColor, fontFamily: '\'Avenir\', sans-serif' }"
                        >
                            {{ stat.label }}
                        </p>
                    </div>
                </div>
            </section>

            <section class="bg-black px-[59px] py-[125px]">
                <div class="mb-16 flex items-start justify-between">
                    <h2
                        class="max-w-[223px] text-[40px] leading-[36px] tracking-[-1.21px] text-white"
                        style="font-family: 'Avenir', sans-serif; font-weight: 900; font-style: oblique;"
                    >
                        Explore more cases
                    </h2>
                    <a href="#contact" class="flex items-center gap-4 rounded-[73px] bg-white/8 px-4 py-3 text-white">
                        <span style="font-family: 'Avenir', sans-serif;">Tell us about your project</span>
                        <span class="flex h-12 w-12 items-center justify-center rounded-full bg-[#ffc700] text-black">&rarr;</span>
                    </a>
                </div>

                <div class="grid grid-cols-3 gap-8">
                    <a
                        v-for="item in moreCases"
                        :key="item.slug"
                        :href="`/cases/${item.slug}`"
                        class="group relative overflow-hidden rounded-[20px] bg-neutral-800"
                    >
                        <img v-if="item.photo" :src="item.photo" :alt="item.name" class="h-[321px] w-full object-cover transition duration-500 group-hover:scale-105" />
                        <video
                            v-else-if="item.video"
                            :src="item.video"
                            autoplay
                            loop
                            muted
                            playsinline
                            class="h-[321px] w-full object-cover transition duration-500 group-hover:scale-105"
                        />
                        <div v-else class="h-[321px] w-full bg-neutral-700" />
                        <p
                            class="absolute left-9 top-9 max-w-[220px] text-[24px] leading-[1.05] tracking-[-0.72px] text-[#ffc700]"
                            style="font-family: 'Avenir', sans-serif; font-weight: 900; font-style: oblique;"
                        >
                            {{ item.name }}
                        </p>
                    </a>
                </div>
            </section>
        </main>

        <SiteFooter id="contact" :footer-items="siteItems" />
    </div>
</template>
