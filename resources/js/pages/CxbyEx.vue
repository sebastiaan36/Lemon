<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import SiteFooter from '@/components/SiteFooter.vue'
import SiteHeader from '@/components/SiteHeader.vue'

// Figma assets — replace with permanent storage URLs after uploading
const imgHeroBg = 'https://www.figma.com/api/mcp/asset/113382bf-0259-44f8-a639-8c200b1ef1f9'
const imgQuoteBg = 'https://www.figma.com/api/mcp/asset/4aaed28f-d0f0-4776-beea-149d4d89e52b'
const imgChecklist = 'https://www.figma.com/api/mcp/asset/b32fc3ec-71d0-4861-8d59-62acdba40901'

// Brand logos (Leading brands section)
const imgLogo1Rating = 'https://www.figma.com/api/mcp/asset/3c7d8c54-124f-4bef-95f7-9109d49559c1'
const imgLogo1Name   = 'https://www.figma.com/api/mcp/asset/2f30696c-0369-4795-b9aa-50ef6a8ff126'
const imgLogo1Icon   = 'https://www.figma.com/api/mcp/asset/61f3bfde-e728-4365-bdd3-ce9e259208e2'
const imgLogo1Brand  = 'https://www.figma.com/api/mcp/asset/786a8b62-413b-4dc6-92da-9e31a530a9c2'
const imgLogo2Rating = 'https://www.figma.com/api/mcp/asset/c0eab227-2670-4b3a-8fb5-04be22651949'
const imgLogo2Name   = 'https://www.figma.com/api/mcp/asset/3608a6fd-0579-401d-8754-ccdd6e3873b1'
const imgLogo2Icon   = 'https://www.figma.com/api/mcp/asset/576e43fe-0d14-4f0b-9652-7c38f5c89330'
const imgLogo2Brand  = 'https://www.figma.com/api/mcp/asset/f9beba74-3931-434b-a1d2-55c282d5c63e'
const imgLogo3Name   = 'https://www.figma.com/api/mcp/asset/891d9a76-6d17-47d3-bf97-37cf54695b23'
const imgLogo3Brand  = 'https://www.figma.com/api/mcp/asset/d6eda159-34d2-4b15-893f-48635bca5795'

const steps = [
    {
        number: '01',
        title: 'Define your brand narrative',
        body: 'What makes you different? We help you craft a compelling internal story that inspires and aligns.',
    },
    {
        number: '02',
        title: 'Translate values into behaviors',
        body: 'We turn abstract values into clear, daily actions your teams can understand, embrace, and deliver.',
    },
    {
        number: '03',
        title: 'Bring it to life',
        body: 'Creative internal campaigns, rituals, and storytelling that activate belief and energy across teams.',
    },
    {
        number: '04',
        title: 'Optimize customer & employee journeys',
        body: 'We identify the high-impact moments where human interaction shapes the brand and make them better.',
    },
    {
        number: '05',
        title: 'Build momentum',
        body: 'We keep the fire burning with recognition programs, measurement, and ongoing culture nudges.',
    },
]

function scrollDown() {
    document.getElementById('cxbyex-content')?.scrollIntoView({ behavior: 'smooth' })
}
</script>

<template>
    <Head>
        <title>CXbyEX — Lemon Scented Tea</title>
        <meta name="description" content="Customer Experience by Employee Experience. A Lemon specialty." />
    </Head>

    <div class="bg-[#ffc700]">
        <SiteHeader />

        <!-- ============ HERO ============ -->
        <section class="relative flex h-screen flex-col items-center justify-center overflow-hidden rounded-b-[30px]">
            <img :src="imgHeroBg" alt="" class="absolute inset-0 h-full w-full object-cover" />
            <div class="absolute inset-0 bg-black/60" />

            <div class="relative z-10 flex flex-col items-center px-[59px] text-center">
                <h1
                    class="text-[100px] leading-none tracking-[-4.5px] text-[#ffc700] lg:text-[150px]"
                    style="font-family: 'Avenir', system-ui, sans-serif; font-weight: 900; font-style: oblique;"
                >
                    CXbyEX
                </h1>
                <p
                    class="mt-8 max-w-[840px] text-[20px] leading-[1.5] tracking-[-0.6px] text-white lg:text-[26px]"
                    style="font-family: 'Avenir', system-ui, sans-serif;"
                >
                    Credible Creativity ensures that what you promise on the outside through campaigns, identities, and customer experiences is truly delivered on the inside by your employees and your product.
                </p>
            </div>

            <!-- Scroll indicator -->
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
        <section class="px-[59px] py-[120px]">
            <p
                class="mx-auto max-w-[971px] text-center text-[38px] leading-[58px] tracking-[-1.5px] lg:text-[50px] lg:leading-[60px]"
                style="font-family: 'Avenir', system-ui, sans-serif; background: linear-gradient(180deg, #000 17%, rgba(0,0,0,0.05) 92%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;"
            >
                Your Brand Narrative can be told with confidence because it reflects your core essence. It helps employees live the brand story not only because they can, but because they want to. In short, Customer Experience by Employee Experience. Or shorter still, CXbyEX. A Lemon specialty.
            </p>
        </section>

        <!-- ============ DELTA CASE STUDY CARD ============ -->
        <section class="mx-[59px] overflow-hidden rounded-[30px] lg:mx-[95px]">
            <div class="relative min-h-[700px] lg:min-h-[1048px]">
                <img :src="imgHeroBg" alt="" class="absolute inset-0 h-full w-full object-cover" />
                <div class="absolute inset-0 bg-black/50" />

                <div class="relative z-10 flex h-full flex-col justify-between gap-[60px] px-[60px] py-[80px] lg:flex-row lg:items-end lg:px-[80px]">
                    <!-- Left: body -->
                    <p
                        class="max-w-[573px] text-[26px] leading-[1.4] tracking-[-0.8px] text-white lg:text-[32px]"
                        style="font-family: 'Avenir', system-ui, sans-serif;"
                    >
                        Boost your brand from the inside out with CXbyEX. Deliver unforgettable customer experiences by building an exceptional employee experience.
                    </p>

                    <!-- Right: case info -->
                    <div class="flex flex-col gap-4">
                        <p
                            class="text-[18px] tracking-[-0.54px] text-white"
                            style="font-family: 'Avenir', system-ui, sans-serif;"
                        >
                            Case example
                        </p>
                        <p
                            class="text-[28px] tracking-[-0.8px] text-[#ffc700]"
                            style="font-family: 'Avenir', system-ui, sans-serif; font-weight: 900; font-style: oblique;"
                        >
                            Delta Air Lines
                        </p>
                        <div class="flex flex-wrap gap-2">
                            <span
                                class="flex h-[36px] items-center rounded-full border border-white px-4 text-[14px] tracking-[-0.14px] text-white"
                                style="font-family: 'Avenir', system-ui, sans-serif;"
                            >
                                Positioning
                            </span>
                            <span
                                class="flex h-[36px] items-center rounded-full border border-white px-4 text-[14px] tracking-[-0.14px] text-white"
                                style="font-family: 'Avenir', system-ui, sans-serif;"
                            >
                                Employer branding
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============ TWO COLUMN BODY TEXT ============ -->
        <section class="grid gap-[40px] px-[59px] py-[100px] lg:grid-cols-2 lg:gap-[60px]">
            <p
                class="text-[20px] leading-[32px] tracking-[-0.6px] text-black lg:text-[24px]"
                style="font-family: 'Avenir', system-ui, sans-serif;"
            >
                You can't grow a brand from the outside in. Real, lasting growth happens when your employees live your brand every day, at every touchpoint. When employee experience (EX) fuels customer experience (CX).
            </p>
            <p
                class="text-[20px] leading-[32px] tracking-[-0.6px] text-black lg:text-[24px]"
                style="font-family: 'Avenir', system-ui, sans-serif;"
            >
                That's when companies outperform in loyalty, retention, and reputation. That's where Lemon CXbyEX comes in. We help service-driven brands in aviation, hospitality, finance, healthcare, and retail turn internal energy into external impact.
            </p>
        </section>

        <!-- ============ QUOTE SECTION ============ -->
        <section class="relative overflow-hidden" style="min-height: 600px;">
            <img :src="imgQuoteBg" alt="" class="absolute inset-0 h-full w-full object-cover" />
            <div class="absolute inset-0 bg-black/50" />

            <div class="relative z-10 flex flex-col items-center justify-center px-[59px] py-[140px] text-center">
                <blockquote
                    class="max-w-[1280px] text-[46px] leading-[1.1] tracking-[-1.5px] text-[#ffc700] lg:text-[65px]"
                    style="font-family: 'Avenir', system-ui, sans-serif; font-weight: 900; font-style: oblique;"
                >
                    <span class="text-white">'</span>Our people live the brand, passengers feel it instantly, journeys become memorable and your airline becomes unmistakable<span class="text-white">'</span>
                </blockquote>
                <p
                    class="mt-6 text-[18px] tracking-[-0.54px] text-white"
                    style="font-family: 'Avenir', system-ui, sans-serif;"
                >
                    Michelle &nbsp;—&nbsp; DELTA airlines
                </p>

                <!-- CTA pill -->
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
            <!-- Left column -->
            <div class="lg:w-[420px] lg:shrink-0">
                <h2
                    class="text-[56px] leading-[1.0] tracking-[-2px] text-black lg:text-[68px]"
                    style="font-family: 'Avenir', system-ui, sans-serif; font-weight: 900; font-style: oblique;"
                >
                    From story<br />to strategy<br />to action
                </h2>

                <!-- Arrow -->
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

            <!-- Right column: numbered steps -->
            <div class="min-w-0 flex-1">
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

                <!-- Download checklist CTA -->
                <div class="mt-[60px] flex items-center gap-4">
                    <div class="relative h-[90px] w-[74px] shrink-0 overflow-hidden bg-white">
                        <img :src="imgChecklist" alt="" class="absolute inset-0 h-full w-full object-cover opacity-50" />
                        <p
                            class="absolute inset-0 flex items-center justify-center p-2 text-center text-[10px] font-bold leading-tight text-black"
                            style="font-family: 'Avenir', system-ui, sans-serif;"
                        >
                            CX starts with your employees
                        </p>
                    </div>
                    <a
                        href="#"
                        class="flex h-[73px] items-center gap-3 rounded-full border border-black/10 bg-black/80 pl-6 pr-3 text-[18px] tracking-[-0.18px] text-white transition-opacity hover:opacity-80"
                        style="font-family: 'Avenir', system-ui, sans-serif;"
                    >
                        Download our free checklist
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
                <span class="text-white">Leading brands<br /></span>
                <span class="text-[#ffc700]">choose Lemon</span>
            </h2>

            <!-- Brand logo groups -->
            <div class="mb-[80px] flex flex-wrap items-center justify-center gap-[60px] lg:gap-[120px]">
                <!-- Group 1 -->
                <div class="flex flex-col items-center gap-3">
                    <img :src="imgLogo1Name" alt="" class="h-[29px] w-auto" />
                    <img :src="imgLogo1Rating" alt="" class="h-[80px] w-auto" />
                    <img :src="imgLogo1Icon" alt="" class="h-[63px] w-auto" />
                    <img :src="imgLogo1Brand" alt="" class="h-[21px] w-auto" />
                </div>
                <!-- Group 2 -->
                <div class="flex flex-col items-center gap-3">
                    <img :src="imgLogo2Name" alt="" class="h-[29px] w-auto" />
                    <img :src="imgLogo2Rating" alt="" class="h-[80px] w-auto" />
                    <img :src="imgLogo2Icon" alt="" class="h-[63px] w-auto" />
                    <img :src="imgLogo2Brand" alt="" class="h-[21px] w-auto" />
                </div>
                <!-- Group 3 -->
                <div class="flex flex-col items-center gap-3">
                    <img :src="imgLogo3Name" alt="" class="h-[80px] w-auto" />
                    <img :src="imgLogo3Brand" alt="" class="h-[21px] w-auto" />
                </div>
            </div>

            <!-- Get in touch -->
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
