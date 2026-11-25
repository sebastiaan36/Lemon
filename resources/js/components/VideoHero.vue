<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue';
import CmsImage from '@/components/CmsImage.vue';
import HlsVideo from '@/components/HlsVideo.vue';
import { isVideoUrl } from '@/lib/scrollAnimation';

/**
 * Schermvullende hero met achtergrondvideo of -foto, gele titel en witte tekst
 * ervoor, en de scroll-hint met gele voortgangsring. Zelfde opmaak als de
 * CXbyEX-hero; gebruikt op About en Industries. Zonder media: donkere achtergrond.
 */
const props = withDefaults(
    defineProps<{
        title: string | null | undefined;
        /** Enter = nieuwe regel. */
        subtitle?: string | null;
        media?: { url: string; type: string } | null;
        /** Extra klassen voor de scroll-hint, bv. om hem boven een overlappende pill te tillen. */
        scrollHintClass?: string;
    }>(),
    { subtitle: null, media: null, scrollHintClass: 'bottom-2' },
);

const heroRef = ref<HTMLElement | null>(null);
const heroProgress = ref(0);
const scrollHintOpacity = ref(1);
let frame = 0;

function isVideo(): boolean {
    return props.media?.type === 'video' || isVideoUrl(props.media?.url);
}

function update(): void {
    frame = 0;
    const p = Math.max(0, Math.min(window.scrollY / window.innerHeight, 1));
    heroProgress.value = p;
    // Fade uit over de tweede helft van de hero-scroll.
    scrollHintOpacity.value = Math.max(0, Math.min(1, 1 - (p - 0.5) / 0.4));
}

function schedule(): void {
    if (!frame) {
        frame = requestAnimationFrame(update);
    }
}

function scrollDown(): void {
    const hero = heroRef.value;

    if (hero) {
        window.scrollTo({
            top: hero.offsetTop + hero.offsetHeight,
            behavior: 'smooth',
        });
    }
}

onMounted(() => {
    update();
    window.addEventListener('scroll', schedule, { passive: true });
    window.addEventListener('resize', schedule, { passive: true });
});

onBeforeUnmount(() => {
    window.removeEventListener('scroll', schedule);
    window.removeEventListener('resize', schedule);

    if (frame) {
        cancelAnimationFrame(frame);
    }
});
</script>

<template>
    <section
        ref="heroRef"
        data-section="hero"
        data-pill-section="hero"
        class="relative flex min-h-svh flex-col items-center justify-center overflow-hidden bg-[#0c0c0c] py-28 md:min-h-screen"
    >
        <template v-if="media">
            <HlsVideo
                v-if="isVideo()"
                :src="media.url"
                autoplay
                muted
                loop
                playsinline
                class="absolute inset-0 h-full w-full object-cover"
            />
            <CmsImage
                v-else
                priority
                :src="media.url"
                alt=""
                class="absolute inset-0 h-full w-full object-cover"
            />
        </template>
        <div
            v-else
            class="absolute inset-0 bg-[radial-gradient(ellipse_at_50%_35%,#2a2a2a_0%,#0c0c0c_70%)]"
            aria-hidden="true"
        />
        <div class="absolute inset-0 bg-black/40" aria-hidden="true" />

        <div
            class="relative z-10 flex flex-col items-center px-5 text-center md:px-[59px]"
        >
            <h1
                class="font-avenir text-[clamp(56px,16vw,100px)] leading-none tracking-[-0.03em] text-[#ffc700] lg:text-[150px]"
                style="font-weight: 900; font-style: oblique"
            >
                {{ title }}
            </h1>
            <p
                v-if="subtitle"
                class="mt-6 max-w-[900px] font-avenir text-[18px] leading-[1.3] tracking-[-0.02em] whitespace-pre-line text-white md:mt-8 md:text-[22px] lg:text-[29px]"
            >
                {{ subtitle }}
            </p>
        </div>

        <!-- Scroll-hint: gele ring vult zich naarmate je door de hero scrollt (zelfde als CXbyEX en home) -->
        <button
            type="button"
            aria-label="Scroll down"
            class="absolute left-1/2 z-10 -translate-x-1/2 cursor-pointer border-none bg-transparent p-0"
            :class="scrollHintClass"
            :style="{ opacity: scrollHintOpacity }"
            @click="scrollDown"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                width="66"
                height="80"
                viewBox="0 0 105 127"
                fill="none"
                aria-hidden="true"
            >
                <rect
                    x="20.1202"
                    y="30.3433"
                    width="64.3133"
                    height="64.3133"
                    rx="32.1567"
                    stroke="white"
                    stroke-opacity="0.3"
                    stroke-width="0.686695"
                />
                <path
                    d="M45.4885 66.0251L52.7772 73.3138L60.3922 65.6988"
                    stroke="#FFC700"
                    stroke-width="1.53846"
                />
                <path
                    d="M52.777 72.8786V50.6863"
                    stroke="#FFC700"
                    stroke-width="1.53846"
                />
                <circle
                    opacity="0.2"
                    cx="52.2769"
                    cy="62.5"
                    r="32"
                    stroke="white"
                />
                <circle
                    cx="52.2769"
                    cy="62.5"
                    r="32"
                    stroke="#FFC700"
                    stroke-width="1"
                    fill="none"
                    :stroke-dasharray="201.062"
                    :stroke-dashoffset="201.062 * (1 - heroProgress)"
                    transform="rotate(-90 52.2769 62.5)"
                />
            </svg>
        </button>
    </section>
</template>
