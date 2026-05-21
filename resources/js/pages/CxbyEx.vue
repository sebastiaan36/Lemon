<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import SiteFooter from '@/components/SiteFooter.vue'
import SiteHeader from '@/components/SiteHeader.vue'

type Step = {
    number: string
    title: string
    body: string
}

type BrandLogo = {
    logo: string
    name: string
}

const props = defineProps<{
    seoTitle?: string | null
    metaDescription?: string | null
    heroBgImage?: string | null
    heroSubtitle?: string | null
    narrativeText?: string | null
    caseBgImage?: string | null
    caseBodyText?: string | null
    caseClientName?: string | null
    caseTags?: string[]
    bodyCol1?: string | null
    bodyCol2?: string | null
    quoteBgImage?: string | null
    quoteText?: string | null
    quoteAuthor?: string | null
    steps?: Step[]
    checklistImage?: string | null
    checklistButtonText?: string | null
    checklistHref?: string | null
    brandsTitleLine1?: string | null
    brandsTitleLine2?: string | null
    brandLogos?: BrandLogo[]
}>()

function scrollDown() {
    document.getElementById('cxbyex-content')?.scrollIntoView({ behavior: 'smooth' })
}
</script>

<template>
    <Head>
        <title>{{ seoTitle || 'CXbyEX — Lemon Scented Tea' }}</title>
        <meta v-if="metaDescription" name="description" :content="metaDescription" />
    </Head>

    <div class="bg-[#ffc700]">
        <SiteHeader />

        <!-- ============ HERO ============ -->
        <section class="relative flex h-screen flex-col items-center justify-center overflow-hidden rounded-b-[30px]">
            <img v-if="heroBgImage" :src="heroBgImage" alt="" class="absolute inset-0 h-full w-full object-cover" />
            <div class="absolute inset-0 bg-black/60" />

            <div class="relative z-10 flex flex-col items-center px-[59px] text-center">
                <h1
                    class="text-[100px] leading-none tracking-[-4.5px] text-[#ffc700] lg:text-[150px]"
                    style="font-family: 'Avenir', system-ui, sans-serif; font-weight: 900; font-style: oblique;"
                >
                    CXbyEX
                </h1>
                <p
                    v-if="heroSubtitle"
                    class="mt-8 max-w-[840px] text-[20px] leading-[1.5] tracking-[-0.6px] text-white lg:text-[26px]"
                    style="font-family: 'Avenir', system-ui, sans-serif;"
                >
                    {{ heroSubtitle }}
                </p>
            </div>

            <button
                class="absolute bottom-10 left-1/2 -translate-x-1/2 flex h-[65px] w-[65px] items-center justify-center rounded-full border border-white/30 backdrop-blur-md shadow-[0px_11.5px_19.8px_rgba(0,0,0,0.05)] transition-colors hover:bg-white/10"
                @click="scrollDown"
            >
                <svg class="h-5 w-5 text-white" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M8 3v10M3 9l5 5 5-5" />
                </svg>
            </button>
        </section>

        <div id="cxbyex-content" />

        <!-- ============ BRAND NARRATIVE ============ -->
        <section v-if="narrativeText" class="px-[59px] py-[120px]">
            <p
                class="mx-auto max-w-[971px] text-center text-[38px] leading-[58px] tracking-[-1.5px] lg:text-[50px] lg:leading-[60px]"
                style="font-family: 'Avenir', system-ui, sans-serif; background: linear-gradient(180deg, #000 17%, rgba(0,0,0,0.05) 92%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;"
            >
                {{ narrativeText }}
            </p>
        </section>

        <!-- ============ CASE STUDY CARD ============ -->
        <section class="mx-[59px] overflow-hidden rounded-[30px] lg:mx-[95px]">
            <div class="relative min-h-[700px] bg-black lg:min-h-[1048px]">
                <img v-if="caseBgImage" :src="caseBgImage" alt="" class="absolute inset-0 h-full w-full object-cover" />
                <div class="absolute inset-0 bg-black/50" />

                <div class="relative z-10 flex h-full flex-col justify-between gap-[60px] px-[60px] py-[80px] lg:flex-row lg:items-end lg:px-[80px]">
                    <p
                        v-if="caseBodyText"
                        class="max-w-[573px] text-[26px] leading-[1.4] tracking-[-0.8px] text-white lg:text-[32px]"
                        style="font-family: 'Avenir', system-ui, sans-serif;"
                    >
                        {{ caseBodyText }}
                    </p>

                    <div class="flex flex-col gap-4">
                        <p
                            class="text-[18px] tracking-[-0.54px] text-white"
                            style="font-family: 'Avenir', system-ui, sans-serif;"
                        >
                            Case example
                        </p>
                        <p
                            v-if="caseClientName"
                            class="text-[28px] tracking-[-0.8px] text-[#ffc700]"
                            style="font-family: 'Avenir', system-ui, sans-serif; font-weight: 900; font-style: oblique;"
                        >
                            {{ caseClientName }}
                        </p>
                        <div v-if="caseTags && caseTags.length" class="flex flex-wrap gap-2">
                            <span
                                v-for="tag in caseTags"
                                :key="tag"
                                class="flex h-[36px] items-center rounded-full border border-white px-4 text-[14px] tracking-[-0.14px] text-white"
                                style="font-family: 'Avenir', system-ui, sans-serif;"
                            >
                                {{ tag }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============ TWO COLUMN BODY TEXT ============ -->
        <section v-if="bodyCol1 || bodyCol2" class="grid gap-[40px] px-[59px] py-[100px] lg:grid-cols-2 lg:gap-[60px]">
            <p
                v-if="bodyCol1"
                class="text-[20px] leading-[32px] tracking-[-0.6px] text-black lg:text-[24px]"
                style="font-family: 'Avenir', system-ui, sans-serif;"
            >
                {{ bodyCol1 }}
            </p>
            <p
                v-if="bodyCol2"
                class="text-[20px] leading-[32px] tracking-[-0.6px] text-black lg:text-[24px]"
                style="font-family: 'Avenir', system-ui, sans-serif;"
            >
                {{ bodyCol2 }}
            </p>
        </section>

        <!-- ============ QUOTE SECTION ============ -->
        <section class="relative overflow-hidden" style="min-height: 600px;">
            <img v-if="quoteBgImage" :src="quoteBgImage" alt="" class="absolute inset-0 h-full w-full object-cover" />
            <div class="absolute inset-0 bg-black/50" />

            <div class="relative z-10 flex flex-col items-center justify-center px-[59px] py-[140px] text-center">
                <blockquote
                    v-if="quoteText"
                    class="max-w-[1280px] text-[46px] leading-[1.1] tracking-[-1.5px] text-[#ffc700] lg:text-[65px]"
                    style="font-family: 'Avenir', system-ui, sans-serif; font-weight: 900; font-style: oblique;"
                >
                    <span class="text-white">'</span>{{ quoteText }}<span class="text-white">'</span>
                </blockquote>
                <p
                    v-if="quoteAuthor"
                    class="mt-6 text-[18px] tracking-[-0.54px] text-white"
                    style="font-family: 'Avenir', system-ui, sans-serif;"
                >
                    {{ quoteAuthor }}
                </p>

                <a
                    href="/contact"
                    class="mt-12 flex h-[73px] items-center gap-3 rounded-full border border-white/10 bg-black/70 pl-6 pr-3 text-[18px] tracking-[-0.18px] text-white backdrop-blur-sm transition-opacity hover:opacity-80"
                    style="font-family: 'Avenir', system-ui, sans-serif;"
                >
                    Tell us about your project
                    <span class="flex h-[43px] w-[57px] shrink-0 items-center justify-center rounded-full bg-[#ffc700]">
                        <svg class="h-4 w-4 -rotate-45 text-black" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 8h10M8 3l5 5-5 5" />
                        </svg>
                    </span>
                </a>
            </div>
        </section>

        <!-- ============ FROM STORY TO STRATEGY TO ACTION ============ -->
        <section class="flex flex-col gap-[80px] px-[59px] py-[120px] lg:flex-row lg:gap-[100px]">
            <div class="lg:w-[420px] lg:shrink-0">
                <h2
                    class="text-[56px] leading-[1.0] tracking-[-2px] text-black lg:text-[68px]"
                    style="font-family: 'Avenir', system-ui, sans-serif; font-weight: 900; font-style: oblique;"
                >
                    From story<br />to strategy<br />to action
                </h2>

                <div class="my-6">
                    <svg class="h-[44px] w-[44px] -rotate-[135deg]" viewBox="0 0 23 23" fill="none">
                        <path d="M8.186 21.714L21.714 21.714L21.714 7.58" stroke="black" stroke-width="2.019"/>
                        <path d="M21.31 21.31L0.714 0.714" stroke="black" stroke-width="2.019"/>
                    </svg>
                </div>

                <p
                    class="text-[20px] leading-[32px] tracking-[-0.6px] text-black lg:text-[22px]"
                    style="font-family: 'Avenir', system-ui, sans-serif;"
                >
                    We partner with leadership teams to transform brand ambition into lived behavior — turning culture into a competitive advantage.
                </p>
            </div>

            <div v-if="steps && steps.length" class="min-w-0 flex-1">
                <div
                    v-for="step in steps"
                    :key="step.number"
                    class="border-t border-black/10 py-[36px]"
                >
                    <div class="flex items-start gap-5">
                        <span
                            class="flex h-[39px] w-[61px] shrink-0 items-center justify-center rounded-full bg-black text-[17px] text-white"
                            style="font-family: 'Avenir', system-ui, sans-serif;"
                        >
                            {{ step.number }}
                        </span>
                        <div>
                            <h3
                                class="text-[24px] leading-[32px] tracking-[-0.28px] text-black lg:text-[28px]"
                                style="font-family: 'Avenir', system-ui, sans-serif;"
                            >
                                {{ step.title }}
                            </h3>
                            <p
                                class="mt-2 text-[18px] leading-[24px] tracking-[-0.2px] text-[#101010] lg:text-[20px]"
                                style="font-family: 'Avenir', system-ui, sans-serif;"
                            >
                                {{ step.body }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="border-t border-black/10" />

                <!-- Checklist CTA -->
                <div v-if="checklistButtonText" class="mt-[60px] flex items-center gap-4">
                    <div v-if="checklistImage" class="relative h-[90px] w-[74px] shrink-0 overflow-hidden bg-white">
                        <img :src="checklistImage" alt="" class="absolute inset-0 h-full w-full object-cover" />
                    </div>
                    <a
                        :href="checklistHref || '#'"
                        class="flex h-[73px] items-center gap-3 rounded-full border border-black/10 bg-black/80 pl-6 pr-3 text-[18px] tracking-[-0.18px] text-white transition-opacity hover:opacity-80"
                        style="font-family: 'Avenir', system-ui, sans-serif;"
                    >
                        {{ checklistButtonText }}
                        <span class="flex h-[43px] w-[57px] shrink-0 items-center justify-center rounded-full bg-[#ffc700]">
                            <svg class="h-4 w-4 -rotate-45 text-black" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 8h10M8 3l5 5-5 5" />
                            </svg>
                        </span>
                    </a>
                </div>
            </div>
        </section>

        <!-- ============ LEADING BRANDS ============ -->
        <section class="bg-black px-[59px] py-[140px] text-center">
            <h2
                class="mb-[80px] leading-[1.0] tracking-[-4.5px] text-[80px] lg:text-[150px]"
                style="font-family: 'Avenir', system-ui, sans-serif; font-weight: 900; font-style: oblique;"
            >
                <span class="text-white">{{ brandsTitleLine1 || 'Leading brands' }}<br /></span>
                <span class="text-[#ffc700]">{{ brandsTitleLine2 || 'choose Lemon' }}</span>
            </h2>

            <div v-if="brandLogos && brandLogos.length" class="mb-[80px] flex flex-wrap items-center justify-center gap-[60px] lg:gap-[100px]">
                <img
                    v-for="(brand, i) in brandLogos"
                    :key="i"
                    :src="brand.logo"
                    :alt="brand.name"
                    class="h-[50px] w-auto max-w-[180px] object-contain brightness-0 invert"
                />
            </div>

            <a
                href="/contact"
                class="inline-flex h-[65px] items-center justify-center rounded-full border border-white px-10 text-[22px] tracking-[-0.69px] text-white transition-colors hover:bg-white hover:text-black"
                style="font-family: 'Avenir', system-ui, sans-serif;"
            >
                Get in touch
            </a>
        </section>

        <SiteFooter />
    </div>
</template>
