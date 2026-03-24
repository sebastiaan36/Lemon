<script setup lang="ts">
type FooterItem = {
    label: string
    href: string
}

const props = withDefaults(
    defineProps<{
        id?: string
        footerItems?: FooterItem[]
    }>(),
    {
        id: 'contact',
        footerItems: () => [
            { label: 'About', href: '#about' },
            { label: 'CxByEx', href: '#services' },
            { label: 'Cases', href: '#cases' },
            { label: 'Industries', href: '#services' },
            { label: 'Contact', href: '#contact' },
        ],
    },
)

const FOOTER_LOGO_URL = '/figma-assets/e844dc52-473d-4ba7-a100-5a9aa00c53ce.svg'
</script>

<template>
    <footer :id="id" class="bg-[#ffc700]" style="position: relative; z-index: 11">
        <div class="border-t border-black/20 px-[59px] pt-[59px] pb-8">
            <div
                class="grid items-start pb-[60px]"
                :style="{
                    gridTemplateColumns: '123px minmax(140px, 1fr) minmax(120px, 160px) minmax(180px, 240px) minmax(180px, 240px)',
                    columnGap: 'clamp(2rem, 5vw, 6rem)',
                }"
            >
                <div class="-mt-[59px] flex items-start justify-start self-start">
                    <img :src="FOOTER_LOGO_URL" alt="Lemon Scented Tea" class="h-[123px] w-[123px] object-contain" />
                </div>

                <div />

                <nav class="pt-[10px]">
                    <a
                        v-for="item in footerItems"
                        :key="item.label"
                        :href="item.href"
                        class="block text-black transition-opacity hover:opacity-60"
                        style="
                            font-family: 'Avenir', sans-serif;
                            font-weight: 400;
                            font-size: 22px;
                            line-height: 1.8;
                            letter-spacing: -0.65px;
                        "
                    >
                        {{ item.label }}
                    </a>
                </nav>

                <div class="pt-[2px]">
                    <p
                        class="mb-3 text-black"
                        style="font-family: 'Avenir', sans-serif; font-weight: 900; font-style: oblique; font-size: 16px; letter-spacing: -0.49px;"
                    >
                        Adres
                    </p>
                    <p
                        class="text-black"
                        style="font-family: 'Avenir', sans-serif; font-weight: 300; font-size: 15px; line-height: 1.7; letter-spacing: -0.15px;"
                    >
                        Korte Prinsengracht 26<br />
                        1013 GS Amsterdam
                    </p>
                </div>

                <div class="pt-[2px]">
                    <p
                        class="mb-3 text-black"
                        style="font-family: 'Avenir', sans-serif; font-weight: 900; font-style: oblique; font-size: 16px; letter-spacing: -0.49px;"
                    >
                        Contact
                    </p>
                    <p
                        class="text-black"
                        style="font-family: 'Avenir', sans-serif; font-weight: 300; font-size: 15px; line-height: 1.7; letter-spacing: -0.15px;"
                    >
                        +31 (0) 20 606 3580<br />
                        info@lemonscentedtea.com
                    </p>
                </div>
            </div>

            <div class="flex items-end justify-start pt-6">
                <p
                    class="pb-2 text-black"
                    style="font-family: 'Avenir', sans-serif; font-weight: 300; font-size: 14px; letter-spacing: -0.14px;"
                >
                    All rights reserved - 2026 Lemon Scented Tea &nbsp;&nbsp;&nbsp; Privacy statement
                </p>
            </div>
        </div>
    </footer>
</template>
