<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
import CmsImage from '@/components/CmsImage.vue';
import HlsVideo from '@/components/HlsVideo.vue';
import { SITE_NAV_ITEMS, telHref } from '@/lib/siteNavigation';

type HighlightedCase = {
    name: string;
    slug: string;
    photo: string | null;
    video: string | null;
};

type MenuItem = {
    label: string;
    href: string;
};

const props = withDefaults(
    defineProps<{
        navOnYellow?: boolean;
        navColor?: string | null;
        contactHref?: string;
        menuItems?: MenuItem[];
        /**
         * Achtergrond achter de "Get in touch"-knop zodat hij niet meer over tekst
         * wegvalt. 'filled' = volledig gevulde knop, 'gradient' = gradient-balk achter de nav,
         * 'outline' = alleen een dunne rand (Figma op de donkere case-pagina's).
         */
        ctaStyle?: 'filled' | 'gradient' | 'outline';
        /**
         * Verberg de donkere fade-gradient achter de header op desktop (bijv. op de
         * /cases-pagina's). Op mobiel blijft hij staan zodat logo en menu leesbaar blijven.
         */
        hideTopGradient?: boolean;
        /** Wit logovlak met zwarte letters (Figma op donkere pagina's). */
        invertLogo?: boolean;
        /** Maak de CTA wit zodra hij over een vlak in dezelfde kleur schuift. */
        avoidMatchingCtaBackground?: boolean;
    }>(),
    {
        navOnYellow: false,
        navColor: null,
        contactHref: '/contact',
        ctaStyle: 'filled',
        hideTopGradient: false,
        invertLogo: false,
        avoidMatchingCtaBackground: false,
        menuItems: () => SITE_NAV_ITEMS,
    },
);

const LOGO_URL = '/figma-assets/c6c22c65-e565-4464-8acd-8dd235f3f7f6.svg';
const CONTACT_PHONE = '+31 (0) 20 606 3580';
const CONTACT_EMAIL = 'info@lemonscentedtea.com';

const isMenuOpen = ref(false);
const MENU_ID = 'site-menu-overlay';
const menuToggleRef = ref<HTMLButtonElement | null>(null);
const menuOverlayRef = ref<HTMLElement | null>(null);
const menuNavRef = ref<HTMLElement | null>(null);
const headerRef = ref<HTMLElement | null>(null);
const ctaRef = ref<HTMLElement | null>(null);
const ctaOverMatchingBackground = ref(false);
const page = usePage<{
    siteHeader?: {
        highlightedCase?: HighlightedCase | null;
        industries?: { name: string; slug: string }[];
    };
}>();

const highlightedCase = computed(
    () => page.props.siteHeader?.highlightedCase ?? null,
);
const INDUSTRIES_HREF = '/industries';
const industries = computed(() => page.props.siteHeader?.industries ?? []);
const resolvedNavColor = computed(
    () => props.navColor ?? (props.navOnYellow ? '#0c0c0c' : 'white'),
);

// Logo fadet weg zodra de footer in beeld komt (de footer heeft zijn eigen logo).
const logoHidden = ref(false);
const WHITE_NAV_COLORS = ['white', '#fff', '#ffffff'];
// Zodra het menu-icoon wit is (donkere achtergrond) tonen we ook het witte logo.
const navIsWhite = computed(() =>
    WHITE_NAV_COLORS.includes(effectiveNavColor.value.toLowerCase()),
);
const logoStyle = computed(() => ({
    filter:
        isMenuOpen.value || props.invertLogo || navIsWhite.value
            ? 'invert(1)'
            : 'none',
    opacity: logoHidden.value && !isMenuOpen.value ? 0 : 1,
    transition: 'opacity 0.4s ease, filter 0.5s ease',
    pointerEvents:
        logoHidden.value && !isMenuOpen.value
            ? ('none' as const)
            : ('auto' as const),
}));

// "Get in touch"-knop: gevulde variant gebruikt navColor als achtergrond met contrasterende tekst.
const ctaIsFilled = computed(() => props.ctaStyle === 'filled');
const effectiveCtaBackground = computed(() =>
    ctaOverMatchingBackground.value ? '#ffffff' : resolvedNavColor.value,
);
const effectiveNavColor = computed(() =>
    ctaOverMatchingBackground.value ? '#ffffff' : resolvedNavColor.value,
);
const ctaTextColor = computed(() => {
    if (!ctaIsFilled.value) {
        return resolvedNavColor.value;
    }

    if (ctaOverMatchingBackground.value) {
        return resolvedNavColor.value;
    }

    return resolvedNavColor.value === 'white' ? '#0c0c0c' : '#ffffff';
});
const ctaBackground = computed(() =>
    ctaIsFilled.value ? effectiveCtaBackground.value : 'transparent',
);

let footerObserver: IntersectionObserver | null = null;
let ctaBackgroundFrame = 0;

function colorToRgb(
    color: string | null | undefined,
): [number, number, number] | null {
    if (!color) {
        return null;
    }

    const probe = document.createElement('span');
    probe.style.color = color;
    document.body.appendChild(probe);
    const computedColor = window.getComputedStyle(probe).color;
    probe.remove();

    const match = computedColor.match(/^rgba?\((\d+),\s*(\d+),\s*(\d+)/);

    if (!match) {
        return null;
    }

    return [Number(match[1]), Number(match[2]), Number(match[3])];
}

function colorsAreClose(
    a: [number, number, number],
    b: [number, number, number],
) {
    return a.every((channel, index) => Math.abs(channel - b[index]) <= 3);
}

function elementBackgroundRgb(
    element: Element,
): [number, number, number] | null {
    const background = window.getComputedStyle(element).backgroundColor;
    const match = background.match(
        /^rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*([.\d]+))?/,
    );

    if (!match || Number(match[4] ?? 1) === 0) {
        return null;
    }

    return [Number(match[1]), Number(match[2]), Number(match[3])];
}

function updateCtaBackgroundContrast() {
    ctaBackgroundFrame = 0;

    if (
        !props.avoidMatchingCtaBackground ||
        !ctaIsFilled.value ||
        isMenuOpen.value ||
        typeof document === 'undefined' ||
        typeof window === 'undefined'
    ) {
        ctaOverMatchingBackground.value = false;

        return;
    }

    const cta = ctaRef.value;
    const header = headerRef.value;

    if (!cta || !header) {
        return;
    }

    const navRgb = colorToRgb(resolvedNavColor.value);

    if (!navRgb) {
        return;
    }

    const rect = cta.getBoundingClientRect();
    const x = rect.left + rect.width / 2;
    const y = rect.top + rect.height / 2;
    const underlyingElements = document.elementsFromPoint(x, y);
    const matchingElement = underlyingElements.find((element) => {
        if (header.contains(element)) {
            return false;
        }

        let current: Element | null = element;

        while (current && current !== document.documentElement) {
            const backgroundRgb = elementBackgroundRgb(current);

            if (backgroundRgb) {
                return colorsAreClose(backgroundRgb, navRgb);
            }

            current = current.parentElement;
        }

        return false;
    });

    ctaOverMatchingBackground.value = !!matchingElement;
}

function queueCtaBackgroundContrastUpdate() {
    if (ctaBackgroundFrame || typeof window === 'undefined') {
        return;
    }

    ctaBackgroundFrame = window.requestAnimationFrame(
        updateCtaBackgroundContrast,
    );
}

onMounted(() => {
    if (typeof window === 'undefined') {
        return;
    }

    const footer = document.getElementById('contact');

    if (footer && typeof IntersectionObserver !== 'undefined') {
        footerObserver = new IntersectionObserver(
            (entries) => {
                logoHidden.value = entries[0]?.isIntersecting ?? false;
            },
            { rootMargin: '-80px 0px 0px 0px' },
        );
        footerObserver.observe(footer);
    }

    queueCtaBackgroundContrastUpdate();
    window.addEventListener('scroll', queueCtaBackgroundContrastUpdate, {
        passive: true,
    });
    window.addEventListener('resize', queueCtaBackgroundContrastUpdate);
});

const FOCUSABLE_SELECTOR =
    'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

function menuFocusableElements(): HTMLElement[] {
    if (!menuOverlayRef.value) {
        return [];
    }

    return Array.from(
        menuOverlayRef.value.querySelectorAll<HTMLElement>(FOCUSABLE_SELECTOR),
    );
}

function onMenuKeydown(event: KeyboardEvent): void {
    if (event.key === 'Escape') {
        event.preventDefault();
        closeMenu();

        return;
    }

    if (event.key !== 'Tab') {
        return;
    }

    const focusable = menuFocusableElements();

    if (focusable.length === 0) {
        event.preventDefault();

        return;
    }

    const first = focusable[0];
    const last = focusable[focusable.length - 1];
    const active = document.activeElement;
    const focusIsInside =
        active instanceof Node && !!menuOverlayRef.value?.contains(active);

    if (event.shiftKey && (active === first || !focusIsInside)) {
        event.preventDefault();
        last.focus();
    } else if (!event.shiftKey && (active === last || !focusIsInside)) {
        event.preventDefault();
        first.focus();
    }
}

watch(isMenuOpen, async (open) => {
    if (typeof document === 'undefined') {
        return;
    }

    document.body.style.overflow = open ? 'hidden' : '';
    queueCtaBackgroundContrastUpdate();

    if (open) {
        document.addEventListener('keydown', onMenuKeydown);
        await nextTick();
        menuNavRef.value?.querySelector<HTMLElement>('a[href]')?.focus();
    } else {
        document.removeEventListener('keydown', onMenuKeydown);
        menuToggleRef.value?.focus();
    }
});

onUnmounted(() => {
    if (typeof document === 'undefined') {
        return;
    }

    document.body.style.overflow = '';
    document.removeEventListener('keydown', onMenuKeydown);
    footerObserver?.disconnect();
    window.removeEventListener('scroll', queueCtaBackgroundContrastUpdate);
    window.removeEventListener('resize', queueCtaBackgroundContrastUpdate);

    if (ctaBackgroundFrame) {
        window.cancelAnimationFrame(ctaBackgroundFrame);
    }
});

function closeMenu() {
    isMenuOpen.value = false;
}
</script>

<template>
    <header
        ref="headerRef"
        class="pointer-events-none fixed top-0 right-0 left-0 z-50 flex items-start justify-between px-5 md:px-[59px]"
    >
        <div
            v-if="!navOnYellow && !isMenuOpen"
            class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-[100px] bg-gradient-to-b from-black/90 via-black/45 to-transparent"
            :class="{ 'md:hidden': hideTopGradient }"
        />

        <Link
            href="/"
            class="pointer-events-auto block size-[72px] md:size-[clamp(72px,6.1vw,100px)]"
        >
            <img
                :src="LOGO_URL"
                alt="Lemon Scented Tea"
                class="h-full w-full object-contain transition duration-300"
                :style="logoStyle"
            />
        </Link>

        <div
            class="pointer-events-auto relative flex items-center gap-5 pt-[22px] md:pt-[29px]"
        >
            <!-- Optie 'gradient': gradient-balk achter de nav-knoppen zodat ze niet over tekst wegvallen -->
            <div
                v-if="ctaStyle === 'gradient' && !isMenuOpen"
                class="pointer-events-none absolute -top-[29px] -right-[59px] -bottom-6 -left-10 -z-10"
                :style="{
                    background: navOnYellow
                        ? 'linear-gradient(to left, rgba(255,199,0,0.95) 30%, transparent 100%)'
                        : 'linear-gradient(to left, rgba(12,12,12,0.85) 30%, transparent 100%)',
                }"
            />
            <a
                ref="ctaRef"
                :href="contactHref"
                class="hidden h-[50px] w-[138px] items-center justify-center rounded-[30px] font-avenir text-[16px] transition-colors duration-500 md:flex"
                :style="{
                    borderWidth: ctaIsFilled ? '0' : '0.5px',
                    borderStyle: 'solid',
                    borderColor: resolvedNavColor,
                    background: ctaBackground,
                    color: ctaTextColor,
                    letterSpacing: '-0.48px',
                }"
            >
                Get in touch
            </a>

            <button
                ref="menuToggleRef"
                type="button"
                class="flex cursor-pointer flex-col gap-[14.22px]"
                :aria-label="isMenuOpen ? 'Menu sluiten' : 'Menu openen'"
                :aria-expanded="isMenuOpen"
                :aria-controls="MENU_ID"
                @click="isMenuOpen = !isMenuOpen"
            >
                <span
                    class="block h-[2.5px] w-[38px] rounded-[1px] transition-colors duration-500"
                    :style="{ backgroundColor: effectiveNavColor }"
                />
                <span
                    class="block h-[2.5px] w-[38px] rounded-[1px] transition-colors duration-500"
                    :style="{ backgroundColor: effectiveNavColor }"
                />
            </button>
        </div>
    </header>

    <Transition name="menu">
        <!-- Figma (node 36:127): zwart paneel bovenin, daaronder blijft de pagina
             wazig zichtbaar. Klik op het wazige deel sluit het menu. -->
        <div
            v-if="isMenuOpen"
            :id="MENU_ID"
            ref="menuOverlayRef"
            role="dialog"
            aria-modal="true"
            aria-label="Hoofdmenu"
            class="fixed inset-0 z-[60] overflow-y-auto overscroll-contain bg-black/40 backdrop-blur-[15px]"
            @click.self="closeMenu"
        >
            <div
                class="relative border-b border-white/20 bg-black px-5 pb-12 text-white md:min-h-[561px] md:px-0 md:pb-[90px]"
            >
                <div
                    class="flex items-start justify-between md:pr-[55px] md:pl-[59px]"
                >
                    <Link
                        href="/"
                        class="block size-[72px] md:size-[100px]"
                        @click="closeMenu"
                    >
                        <img
                            :src="LOGO_URL"
                            alt="Lemon Scented Tea"
                            class="h-full w-full object-contain transition duration-300"
                            :style="logoStyle"
                        />
                    </Link>

                    <button
                        type="button"
                        class="relative mt-[22px] size-[40px] md:mt-[42px]"
                        aria-label="Menu sluiten"
                        @click="closeMenu"
                    >
                        <span
                            class="absolute top-1/2 left-1/2 block h-[2px] w-[40px] -translate-x-1/2 -translate-y-1/2 rotate-45 rounded-[3.5px] bg-white"
                        />
                        <span
                            class="absolute top-1/2 left-1/2 block h-[2px] w-[40px] -translate-x-1/2 -translate-y-1/2 -rotate-45 rounded-[3.5px] bg-white"
                        />
                    </button>
                </div>

                <!-- Figma (menu D/M): navigatie | Industries met branches | adres + contact | uitgelichte case.
                     Mobiel: navigatie en Industries naast elkaar, daaronder adres en contact naast
                     elkaar, en de uitgelichte case over de volle breedte. -->
                <div
                    class="grid grid-cols-2 items-start gap-x-6 gap-y-10 pt-12 md:grid-cols-[minmax(140px,183px)_minmax(200px,254px)_minmax(200px,1fr)_auto] md:gap-x-0 md:gap-y-0 md:pt-[100px] md:pr-[clamp(32px,11vw,182px)] md:pl-[clamp(32px,8.5vw,139px)]"
                >
                    <nav
                        ref="menuNavRef"
                        aria-label="Hoofdnavigatie"
                        class="flex flex-col items-start font-avenir text-[22px] leading-[38px] tracking-[-0.03em] md:text-[28px] md:leading-[48px]"
                    >
                        <a
                            v-for="item in menuItems"
                            :key="item.label"
                            :href="item.href"
                            class="transition-opacity hover:opacity-60"
                            :class="{
                                'md:hidden':
                                    item.href === INDUSTRIES_HREF &&
                                    industries.length > 0,
                            }"
                            @click="closeMenu"
                        >
                            {{ item.label }}
                        </a>
                    </nav>

                    <nav
                        v-if="industries.length"
                        aria-label="Industries"
                        class="flex flex-col items-start font-avenir tracking-[-0.03em]"
                    >
                        <a
                            :href="INDUSTRIES_HREF"
                            class="text-[22px] leading-[38px] transition-opacity hover:opacity-60 md:text-[28px] md:leading-[42px]"
                            @click="closeMenu"
                        >
                            Industries
                        </a>
                        <a
                            v-for="industry in industries"
                            :key="industry.slug"
                            :href="`${INDUSTRIES_HREF}#${industry.slug}`"
                            class="text-[19px] leading-[38px] text-white/50 transition-colors hover:text-white md:text-[24px] md:leading-[36px]"
                            @click="closeMenu"
                        >
                            {{ industry.name }}
                        </a>
                    </nav>
                    <div v-else class="hidden md:block" />

                    <div class="contents md:flex md:flex-col md:gap-[13px]">
                        <div>
                            <p
                                class="mb-1 font-avenir text-[15px] leading-[30px] tracking-[-0.03em] md:mb-0 md:text-[16.363px] md:leading-[34.544px]"
                                style="font-weight: 900; font-style: oblique"
                            >
                                Address
                            </p>
                            <p
                                class="font-avenir text-[13px] leading-[22px] tracking-[-0.01em] text-white/90 md:text-[14.545px] md:leading-[25.454px]"
                            >
                                Korte Prinsengracht 26<br />
                                1013 GS Amsterdam
                            </p>
                        </div>

                        <div>
                            <p
                                class="mb-1 font-avenir text-[15px] leading-[30px] tracking-[-0.03em] md:mb-0 md:text-[16.363px] md:leading-[34.544px]"
                                style="font-weight: 900; font-style: oblique"
                            >
                                Contact
                            </p>
                            <p
                                class="font-avenir text-[13px] leading-[22px] tracking-[-0.01em] text-white/90 md:text-[14.545px] md:leading-[25.454px]"
                            >
                                <a
                                    :href="telHref(CONTACT_PHONE)"
                                    class="hover:opacity-60"
                                    >{{ CONTACT_PHONE }}</a
                                ><br />
                                <a
                                    :href="`mailto:${CONTACT_EMAIL}`"
                                    class="break-all hover:opacity-60 md:break-normal"
                                    >{{ CONTACT_EMAIL }}</a
                                >
                            </p>
                        </div>
                    </div>

                    <!-- Uitgelichte case met verticaal label links ernaast -->
                    <div
                        class="col-span-2 flex items-end gap-2 md:col-span-1 md:-mt-[30px] md:gap-[5px]"
                    >
                        <p
                            class="shrink-0 font-avenir text-[13px] leading-[1] tracking-[-0.03em] whitespace-nowrap text-white/80 [writing-mode:vertical-rl] md:text-[14px]"
                            style="
                                font-style: oblique;
                                transform: rotate(180deg);
                            "
                        >
                            Highlighted case
                        </p>

                        <a
                            v-if="highlightedCase"
                            :href="`/cases/${highlightedCase.slug}`"
                            class="relative block aspect-[476/260] w-full overflow-hidden rounded-[18.783px] bg-neutral-800 md:w-[clamp(300px,29vw,476px)]"
                            @click="closeMenu"
                        >
                            <CmsImage
                                sizes="(min-width: 768px) 480px, 100vw"
                                v-if="highlightedCase.photo"
                                :src="highlightedCase.photo"
                                :alt="highlightedCase.name"
                                class="absolute inset-0 h-full w-full object-cover"
                            />
                            <HlsVideo
                                v-else-if="highlightedCase.video"
                                :src="highlightedCase.video"
                                autoplay
                                muted
                                loop
                                playsinline
                                class="absolute inset-0 h-full w-full object-cover"
                            />
                            <div
                                class="absolute inset-0 bg-gradient-to-r from-black/35 via-black/10 to-transparent"
                            />
                            <p
                                class="absolute top-[8%] left-[8%] max-w-[149px] font-avenir text-[20px] leading-[1] tracking-[-0.03em] text-[#ffc700] md:text-[22.539px]"
                                style="font-weight: 900; font-style: oblique"
                            >
                                {{ highlightedCase.name }}
                            </p>
                        </a>

                        <div
                            v-else
                            class="flex aspect-[476/260] w-full items-center justify-center overflow-hidden rounded-[18.783px] bg-neutral-800 font-avenir text-white/40 md:w-[clamp(300px,29vw,476px)]"
                        >
                            No case image
                        </div>
                    </div>
                </div>
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
