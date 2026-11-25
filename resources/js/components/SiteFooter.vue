<script setup lang="ts">
import CookieConsent from '@/components/CookieConsent.vue';
import { useCookieConsent } from '@/composables/useCookieConsent';
import { SITE_NAV_ITEMS, telHref } from '@/lib/siteNavigation';

type FooterItem = {
    label: string;
    href: string;
};

withDefaults(
    defineProps<{
        id?: string;
        footerItems?: FooterItem[];
    }>(),
    {
        id: 'contact',
        footerItems: () => SITE_NAV_ITEMS,
    },
);

const { openBanner: openCookieSettings } = useCookieConsent();

const CONTACT_PHONE = '+31 (0) 20 606 3580';
const CONTACT_EMAIL = 'info@lemonscentedtea.com';

const FOOTER_LOGO_URL =
    '/figma-assets/e844dc52-473d-4ba7-a100-5a9aa00c53ce.svg';
</script>

<template>
    <footer
        data-site-footer
        :id="id"
        class="bg-[#ffc700]"
        style="position: relative; z-index: 11"
    >
        <div
            class="border-t border-black/20 px-6 pt-0 pb-8 md:px-[59px] md:pt-[59px]"
        >
            <!-- Mobiel (Figma): logo bovenaan, daaronder navigatie | adres + contact.
                 Desktop: logo links, navigatie/adres/contact rechts in kolommen. -->
            <div
                class="grid grid-cols-[minmax(0,0.75fr)_minmax(0,1.25fr)] items-start gap-x-6 gap-y-12 pb-12 md:grid-cols-[100px_minmax(0,1fr)_minmax(100px,160px)_minmax(150px,240px)_minmax(180px,240px)] md:gap-x-[clamp(1rem,3vw,6rem)] md:gap-y-0 md:pb-[60px] lg:grid-cols-[123px_minmax(0,1fr)_minmax(120px,160px)_minmax(180px,240px)_minmax(180px,240px)] lg:gap-x-[clamp(2rem,5vw,6rem)]"
            >
                <div
                    class="col-span-2 -mt-px flex items-start justify-start self-start md:col-span-1 md:-mt-[60px]"
                >
                    <img
                        loading="lazy"
                        :src="FOOTER_LOGO_URL"
                        alt="Lemon Scented Tea"
                        class="size-[100px] object-contain lg:size-[123px]"
                    />
                </div>

                <div class="hidden md:block" />

                <nav aria-label="Footer">
                    <a
                        v-for="item in footerItems"
                        :key="item.label"
                        :href="item.href"
                        class="block font-avenir text-[20px] leading-[1.7] font-normal tracking-[-0.03em] text-black transition-opacity hover:opacity-60 md:text-[22px] md:leading-[1.8]"
                    >
                        {{ item.label }}
                    </a>
                </nav>

                <div class="flex flex-col gap-8 md:contents">
                    <div class="pt-[2px]">
                        <p
                            class="mb-2 font-avenir text-[15px] tracking-[-0.03em] text-black md:mb-3 md:text-[16px]"
                            style="font-weight: 900; font-style: oblique"
                        >
                            Address
                        </p>
                        <p
                            class="font-avenir text-[14px] leading-[1.7] font-light tracking-[-0.01em] text-black md:text-[15px]"
                        >
                            Korte Prinsengracht 26<br />
                            1013 GS Amsterdam
                        </p>
                    </div>

                    <div class="pt-[2px]">
                        <p
                            class="mb-2 font-avenir text-[15px] tracking-[-0.03em] text-black md:mb-3 md:text-[16px]"
                            style="font-weight: 900; font-style: oblique"
                        >
                            Contact
                        </p>
                        <p
                            class="font-avenir text-[14px] leading-[1.7] font-light tracking-[-0.01em] text-black md:text-[15px]"
                        >
                            <a
                                :href="telHref(CONTACT_PHONE)"
                                class="hover:opacity-60"
                                >{{ CONTACT_PHONE }}</a
                            ><br />
                            <a
                                :href="`mailto:${CONTACT_EMAIL}`"
                                class="hover:opacity-60"
                                >{{ CONTACT_EMAIL }}</a
                            >
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex items-end justify-start pt-2 md:pt-6">
                <p
                    class="flex flex-wrap gap-x-6 gap-y-1 pb-2 font-avenir text-[12px] font-light tracking-[-0.01em] text-black md:text-[14px]"
                >
                    <span>All rights reserved - 2026 Lemon Scented Tea</span>
                    <span>Privacy statement</span>
                    <button
                        type="button"
                        class="hover:opacity-60"
                        @click="openCookieSettings"
                    >
                        Cookie settings
                    </button>
                </p>
            </div>
        </div>

        <CookieConsent />
    </footer>
</template>
