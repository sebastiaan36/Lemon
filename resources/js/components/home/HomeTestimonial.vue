<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';
import CmsImage from '@/components/CmsImage.vue';
import { useAnimationLoop } from '@/composables/useAnimationLoop';
import { wrap } from '@/lib/scrollAnimation';

const props = defineProps<{
    quote: string;
    author: string;
    authorAvatar: string;
    logo: string;
    clientLogos: string[];
    viewportWidth: number;
}>();

const TESTIMONIAL_LOGO_SPEED = 36; // px/s

// Logo-afmetingen: desktop volgens Figma, kleiner op mobiel zodat er ~2,5 logo's in beeld passen.
const isCompact = computed(() => props.viewportWidth < 768);
const TESTIMONIAL_LOGO_GAP = computed(() => (isCompact.value ? 40 : 80));
const TESTIMONIAL_LOGO_WIDTH = computed(() => (isCompact.value ? 120 : 200));
const TESTIMONIAL_LOGO_HEIGHT = computed(() => (isCompact.value ? 64 : 100));

const rootRef = ref<HTMLElement | null>(null);
const logoTrackRef = ref<HTMLElement | null>(null);

const logoItems = computed(() => [
    ...props.clientLogos,
    ...props.clientLogos,
    ...props.clientLogos,
]);

const logoTrackWidth = computed(() => {
    const count = props.clientLogos.length;

    if (count === 0) {
        return 0;
    }

    // Eén volledige periode inclusief de gap naar het volgende (herhaalde) logo.
    return count * (TESTIMONIAL_LOGO_WIDTH.value + TESTIMONIAL_LOGO_GAP.value);
});

// Offset leeft buiten Vue's reactiviteit; de transform wordt direct op het
// track-element gezet zodat de logo-marquee geen re-renders veroorzaakt.
let logoOffset = 0;

function applyLogoTransform(): void {
    if (logoTrackRef.value) {
        logoTrackRef.value.style.transform = `translateX(${(-logoTrackWidth.value + logoOffset).toFixed(1)}px)`;
    }
}

useAnimationLoop(rootRef, (dt) => {
    const total = logoTrackWidth.value;

    if (total > 0) {
        logoOffset = wrap(logoOffset + TESTIMONIAL_LOGO_SPEED * dt, total);
        applyLogoTransform();
    }
});

onMounted(applyLogoTransform);
watch([logoTrackRef, logoTrackWidth], applyLogoTransform);

function shiftLogos(direction: 'prev' | 'next'): void {
    const total = logoTrackWidth.value;

    if (total <= 0) {
        return;
    }

    const delta = TESTIMONIAL_LOGO_WIDTH.value + TESTIMONIAL_LOGO_GAP.value;
    const nextOffset =
        direction === 'next' ? logoOffset + delta : logoOffset - delta;

    logoOffset = wrap(nextOffset, total);
    applyLogoTransform();
}
</script>

<template>
    <div
        ref="rootRef"
        class="relative flex min-h-[50vh] flex-col items-center bg-[#0c0c0c] px-6 pt-[96px] pb-[110px] md:px-8 md:pt-[120px] md:pb-[170px]"
    >
        <!-- Quote + attributie -->
        <div
            class="z-10 flex w-full flex-col items-center"
            style="opacity: 1; transform: translateY(0)"
        >
            <blockquote
                class="mb-8 max-w-[477px] text-center font-avenir text-[19px] leading-[30px] font-normal tracking-[-0.01em] text-white md:mb-10 md:text-[24px] md:leading-[38px]"
            >
                {{ quote }}
            </blockquote>

            <!-- Attributie: logo + avatar + naam -->
            <div class="flex items-center gap-4">
                <!-- Geen organisatienaam beschikbaar in de props: logo blijft decoratief naast de auteursnaam.
                     Figma: logo ~105px breed, even hoog als de naam ernaast. -->
                <CmsImage
                    sizes="140px"
                    v-if="logo"
                    :src="logo"
                    alt=""
                    class="h-[16px] w-auto max-w-[140px] object-contain brightness-0 invert md:h-[18px]"
                />
                <CmsImage
                    sizes="38px"
                    v-if="authorAvatar"
                    :src="authorAvatar"
                    :alt="author ? `Foto van ${author}` : ''"
                    class="size-[38px] rounded-full object-cover"
                />
                <span
                    v-if="author"
                    class="font-avenir text-white"
                    style="font-size: 18px; letter-spacing: -0.18px"
                >
                    {{ author }}
                </span>
            </div>

            <div
                class="mt-[104px] w-full overflow-hidden md:mt-[170px]"
                :style="{ height: `${TESTIMONIAL_LOGO_HEIGHT + 12}px` }"
            >
                <div
                    ref="logoTrackRef"
                    class="flex items-center"
                    :style="{
                        gap: `${TESTIMONIAL_LOGO_GAP}px`,
                        willChange: 'transform',
                    }"
                >
                    <div
                        v-for="(clientLogo, i) in logoItems"
                        :key="`${clientLogo}-${i}`"
                        class="flex shrink-0 items-center justify-center"
                        :style="{
                            width: `${TESTIMONIAL_LOGO_WIDTH}px`,
                            height: `${TESTIMONIAL_LOGO_HEIGHT}px`,
                        }"
                    >
                        <!-- Geel ingekleurd via CSS-mask, ongeacht de bronkleur van het logo -->
                        <div
                            class="h-full w-full"
                            :style="{
                                backgroundColor: '#ffc700',
                                maskImage: `url(${clientLogo})`,
                                WebkitMaskImage: `url(${clientLogo})`,
                                maskRepeat: 'no-repeat',
                                WebkitMaskRepeat: 'no-repeat',
                                maskPosition: 'center',
                                WebkitMaskPosition: 'center',
                                maskSize: 'contain',
                                WebkitMaskSize: 'contain',
                            }"
                        />
                    </div>
                </div>
            </div>
        </div>

        <div
            class="relative z-10 mt-8 h-8 w-full md:mt-10 md:max-w-[calc(100vw-118px)]"
        >
            <div class="ml-auto h-8 w-[71px]">
                <div
                    class="pointer-events-none flex h-8 w-[71px] items-center justify-center opacity-90"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="71"
                        height="24"
                        viewBox="0 0 71 24"
                        fill="none"
                        aria-hidden="true"
                    >
                        <path
                            d="M62.125 19.3467L69.7044 11.7673L61.7856 3.84849"
                            stroke="#FFC700"
                            stroke-width="1.59984"
                        />
                        <path
                            d="M69.2497 11.7649H46.1721"
                            stroke="#FFC700"
                            stroke-width="1.59984"
                        />
                        <g opacity="0.5">
                            <path
                                d="M8.71094 4.4834L1.13153 12.0628L9.05031 19.9816"
                                stroke="white"
                                stroke-width="1.59984"
                            />
                            <path
                                d="M1.58424 12.0613L24.6618 12.0613"
                                stroke="white"
                                stroke-width="1.59984"
                            />
                        </g>
                    </svg>
                </div>
                <button
                    class="absolute inset-y-0 left-0 w-[28px]"
                    aria-label="Vorige logo's"
                    type="button"
                    @click="shiftLogos('prev')"
                />
                <button
                    class="absolute inset-y-0 right-0 w-[28px]"
                    aria-label="Volgende logo's"
                    type="button"
                    @click="shiftLogos('next')"
                />
            </div>
        </div>
    </div>
</template>
