<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3'
import { computed, onUnmounted, ref, watch } from 'vue'

type HighlightedCase = {
    name: string
    slug: string
    photo: string | null
    video: string | null
}

type MenuItem = {
    label: string
    href: string
}

const props = withDefaults(
    defineProps<{
        navOnYellow?: boolean
        navColor?: string | null
        contactHref?: string
        menuItems?: MenuItem[]
    }>(),
    {
        navOnYellow: false,
        navColor: null,
        contactHref: '#contact',
        menuItems: () => [
            { label: 'About', href: '#about' },
            { label: 'CxByEx', href: '#services' },
            { label: 'Cases', href: '#cases' },
            { label: 'Industries', href: '#services' },
            { label: 'Contact', href: '#contact' },
        ],
    },
)

const LOGO_URL = '/figma-assets/c6c22c65-e565-4464-8acd-8dd235f3f7f6.svg'

const isMenuOpen = ref(false)
const page = usePage<{
    siteHeader?: {
        highlightedCase?: HighlightedCase | null
    }
}>()

const highlightedCase = computed(() => page.props.siteHeader?.highlightedCase ?? null)
const hasHighlightedCaseMedia = computed(() => !!highlightedCase.value?.photo || !!highlightedCase.value?.video)
const logoStyle = computed(() => ({
    filter: isMenuOpen.value ? 'invert(1)' : 'none',
}))
const resolvedNavColor = computed(() => props.navColor ?? (props.navOnYellow ? '#0c0c0c' : 'white'))

watch(isMenuOpen, (open) => {
    if (typeof document === 'undefined') return
    document.body.style.overflow = open ? 'hidden' : ''
})

onUnmounted(() => {
    if (typeof document === 'undefined') return
    document.body.style.overflow = ''
})

function closeMenu() {
    isMenuOpen.value = false
}
</script>

<template>
    <header class="pointer-events-none fixed top-0 right-0 left-0 z-50 flex items-start justify-between px-[59px]">
        <Link href="/" class="pointer-events-auto block h-[100px] w-[100px]">
            <img :src="LOGO_URL" alt="Lemon Scented Tea" class="h-full w-full object-contain transition duration-300" :style="logoStyle" />
        </Link>

        <div class="pointer-events-auto flex items-center gap-5 pt-[29px]">
            <a
                :href="contactHref"
                class="flex h-[50px] w-[138px] items-center justify-center rounded-[30px] text-[16px] transition-colors duration-500"
                :style="{
                    borderWidth: '0.5px',
                    borderStyle: 'solid',
                    borderColor: resolvedNavColor,
                    color: resolvedNavColor,
                    fontFamily: '\'Avenir\', system-ui, sans-serif',
                    letterSpacing: '-0.48px',
                }"
            >
                Get in touch
            </a>

            <button
                class="flex cursor-pointer flex-col gap-[14.22px]"
                :aria-label="isMenuOpen ? 'Menu sluiten' : 'Menu openen'"
                @click="isMenuOpen = !isMenuOpen"
            >
                <span
                    class="block h-[2.5px] w-[38px] rounded-[1px] transition-colors duration-500"
                    :style="{ backgroundColor: resolvedNavColor }"
                />
                <span
                    class="block h-[2.5px] w-[38px] rounded-[1px] transition-colors duration-500"
                    :style="{ backgroundColor: resolvedNavColor }"
                />
            </button>
        </div>
    </header>

    <Transition name="menu">
        <div
            v-if="isMenuOpen"
            class="fixed inset-0 z-[60] overflow-hidden bg-black/40 backdrop-blur-[15px]"
        >
            <div
                class="absolute inset-x-[-228px] top-[-34px] h-[595px] border border-white/20 bg-black/95 backdrop-blur-[37px]"
            />

            <div class="relative flex min-h-full flex-col px-[59px] pt-[42px] pb-12 text-white">
                <div class="flex items-start justify-between">
                    <Link href="/" class="-mt-[42px] block h-[100px] w-[100px]" @click="closeMenu">
                        <img :src="LOGO_URL" alt="Lemon Scented Tea" class="h-full w-full object-contain transition duration-300" :style="logoStyle" />
                    </Link>

                    <button
                        class="relative mt-[2px] h-[40px] w-[40px]"
                        aria-label="Menu sluiten"
                        @click="closeMenu"
                    >
                        <span class="absolute top-1/2 left-1/2 block h-[2px] w-[40px] -translate-x-1/2 -translate-y-1/2 rotate-45 rounded-[3.5px] bg-white" />
                        <span class="absolute top-1/2 left-1/2 block h-[2px] w-[40px] -translate-x-1/2 -translate-y-1/2 -rotate-45 rounded-[3.5px] bg-white" />
                    </button>
                </div>

                <div
                    class="grid flex-1 items-start pt-[68px]"
                    :style="{
                        gridTemplateColumns:
                            'minmax(140px, 175px) minmax(180px, 240px) minmax(180px, 240px) minmax(420px, 540px)',
                        columnGap: 'clamp(2rem, 4vw, 4.5rem)',
                    }"
                >
                    <nav class="flex flex-col items-start text-[28px] leading-[42px] tracking-[-0.84px]">
                        <a
                            v-for="item in menuItems"
                            :key="item.label"
                            :href="item.href"
                            class="transition-opacity hover:opacity-60"
                            style="font-family: 'Avenir', sans-serif;"
                            @click="closeMenu"
                        >
                            {{ item.label }}
                        </a>
                    </nav>

                    <div>
                        <p
                            class="mb-3 text-[16px] leading-[34.544px] tracking-[-0.4909px]"
                            style="font-family: 'Avenir', sans-serif; font-weight: 900; font-style: oblique;"
                        >
                            Adres
                        </p>
                        <p
                            class="text-[14.545px] leading-[25.454px] tracking-[-0.1454px] text-white/90"
                            style="font-family: 'Avenir', sans-serif;"
                        >
                            Korte Prinsengracht 26<br />
                            1013 GS Amsterdam
                        </p>
                    </div>

                    <div>
                        <p
                            class="mb-3 text-[16px] leading-[34.544px] tracking-[-0.4909px]"
                            style="font-family: 'Avenir', sans-serif; font-weight: 900; font-style: oblique;"
                        >
                            Contact
                        </p>
                        <p
                            class="text-[14.545px] leading-[25.454px] tracking-[-0.1454px] text-white/90"
                            style="font-family: 'Avenir', sans-serif;"
                        >
                            +31 (0) 20 606 3580<br />
                            info@lemonscentedtea.com
                        </p>
                    </div>

                    <div class="justify-self-end overflow-visible pt-[2px]">
                        <div class="relative h-[260px] w-[540px] overflow-visible">
                            <p
                                class="absolute bottom-[9px] left-[46px] origin-bottom-left -rotate-90 whitespace-nowrap text-[14px] leading-[34.544px] tracking-[-0.42px] text-white/80"
                                style="font-family: 'Avenir', sans-serif; font-style: oblique;"
                            >
                                Highlighted case
                            </p>

                            <a
                                v-if="highlightedCase"
                                :href="`/cases/${highlightedCase.slug}`"
                                class="absolute right-0 bottom-0 block h-[260px] w-[476px] overflow-hidden rounded-[18.783px] bg-neutral-800"
                            >
                                <img
                                    v-if="highlightedCase.photo"
                                    :src="highlightedCase.photo"
                                    :alt="highlightedCase.name"
                                    class="absolute inset-0 h-full w-full object-cover"
                                />
                                <video
                                    v-else-if="highlightedCase.video"
                                    :src="highlightedCase.video"
                                    autoplay
                                    muted
                                    loop
                                    playsinline
                                    class="absolute inset-0 h-full w-full object-cover"
                                />
                                <div class="absolute inset-0 bg-gradient-to-r from-black/35 via-black/10 to-transparent" />
                                <p
                                    class="absolute left-[39px] top-[36px] max-w-[149px] text-[22.539px] leading-[22.539px] tracking-[-0.6762px] text-[#ffc700]"
                                    style="font-family: 'Avenir', sans-serif; font-weight: 900; font-style: oblique;"
                                >
                                    {{ highlightedCase.name }}
                                </p>
                            </a>

                            <div
                                v-else
                                class="absolute right-0 bottom-0 flex h-[260px] w-[476px] items-center justify-center overflow-hidden rounded-[18.783px] bg-neutral-800 text-white/40"
                                style="font-family: 'Avenir', sans-serif;"
                            >
                                No case image
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    v-if="hasHighlightedCaseMedia"
                    class="pointer-events-none absolute inset-x-0 bottom-0 h-[42vh] bg-black/25 backdrop-blur-[16px]"
                />
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.menu-enter-active,
.menu-leave-active {
    transition: opacity 0.35s ease;
}

.menu-enter-from,
.menu-leave-to {
    opacity: 0;
}
</style>
