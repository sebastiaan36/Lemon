<script setup lang="ts">
import type { CSSProperties } from 'vue';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

/**
 * Gele scroll-hero (About, Industries): grote titel die krimpt en wegschuift,
 * en een introtekst die meegroeit (29 → 48px) en via een verloopmasker in- en
 * uitvaagt. Overgenomen van de homepage-hero.
 */
defineProps<{
    title: string | null | undefined;
    /** Lege regel of enter = nieuwe regel in de introtekst. */
    description: string | null | undefined;
}>();

const FIGMA_DESKTOP_WIDTH = 1640;
const FIGMA_DESKTOP_HEIGHT = 900;
const TEXT_LOCK_WIDTH = 1169.4;
const TEXT_LOCK_FONT_SIZE = 48.03;
const TEXT_LOCK_LINE_HEIGHT = 60;
const TEXT_LOCK_LETTER_SPACING = -1.44;
const TEXT_START_TOP = 744;
const TEXT_SCALE_START = 0.065;
const TEXT_SCALE_END = 0.095;

const heroRef = ref<HTMLElement | null>(null);
const progress = ref(0);
const scrollY = ref(0);
const windowWidth = ref(
    typeof window !== 'undefined' ? window.innerWidth : FIGMA_DESKTOP_WIDTH,
);
const windowHeight = ref(
    typeof window !== 'undefined' ? window.innerHeight : FIGMA_DESKTOP_HEIGHT,
);

function rp(value: number, from: number, to: number): number {
    return Math.max(0, Math.min((value - from) / (to - from), 1));
}

function ease(t: number): number {
    return t < 0.5 ? 2 * t * t : -1 + (4 - 2 * t) * t;
}

function lerp(from: number, to: number, t: number): number {
    return from + (to - from) * t;
}

function sx(value: number): string {
    return `${(value / FIGMA_DESKTOP_WIDTH) * windowWidth.value}px`;
}

function sy(value: number): string {
    return `${(value / FIGMA_DESKTOP_HEIGHT) * windowHeight.value}px`;
}

const fontScale = computed(() => Math.min(1, windowWidth.value / 900));

const titleStyle = computed<CSSProperties>(() => {
    const phase1 = ease(rp(progress.value, 0, 0.1));
    const phase2 = ease(rp(progress.value, 0.1, 0.2));
    const s = fontScale.value;
    // Figma: 120px → 82.493px. Lettergrootte, regelhoogte en letterspatiëring krimpen
    // met dezelfde factor, dus een scale-transform geeft exact hetzelfde beeld. Een
    // animerende font-size liet bij heen-en-weer scrollen een "spookkopie" van een
    // eerder frame achter; transform/opacity draaien op de compositor zonder repaint.
    const scale = (120 - 37.507 * phase1) / 120;

    return {
        fontSize: `${(120 * s).toFixed(2)}px`,
        lineHeight: `${(70 * s).toFixed(2)}px`,
        letterSpacing: `${(-4.8 * s).toFixed(4)}px`,
        transform: `translateY(${(28 * phase1 - 553 * phase2).toFixed(1)}px) scale(${scale.toFixed(4)})`,
        transformOrigin: 'center top',
        willChange: 'transform, opacity',
        opacity: 1 - ease(rp(progress.value, 0.13, 0.2)),
    };
});

const textScaleT = computed(() =>
    ease(rp(progress.value, TEXT_SCALE_START, TEXT_SCALE_END)),
);
/**
 * Figma: de intro groeit van 29.407px naar 48.03px. Lettergrootte, regelhoogte en
 * letterspatiëring krimpen met dezelfde factor (~0.612), dus we zetten de tekst
 * direct op eindmaat en eindbreedte en laten hem groeien met een scale-transform.
 * Zo liggen de regelafbrekingen vast en verspringen er geen woorden tijdens het scrollen.
 */
const TEXT_START_SCALE = 29.407 / TEXT_LOCK_FONT_SIZE;
const textScale = computed(() => lerp(TEXT_START_SCALE, 1, textScaleT.value));

const textStyle = computed<CSSProperties>(() => {
    const s = fontScale.value;

    return {
        fontSize: `${(TEXT_LOCK_FONT_SIZE * s).toFixed(2)}px`,
        lineHeight: `${(TEXT_LOCK_LINE_HEIGHT * s).toFixed(2)}px`,
        letterSpacing: `${(TEXT_LOCK_LETTER_SPACING * s).toFixed(4)}px`,
    };
});

const textContainerStyle = computed<CSSProperties>(() => {
    const gradient =
        'linear-gradient(180deg, transparent 0%, black 30%, black 70%, transparent 100%)';

    return {
        position: 'fixed',
        inset: 0,
        overflow: 'hidden',
        pointerEvents: 'none',
        opacity:
            progress.value <= 0.995
                ? 1
                : 1 - ease(rp(progress.value, 0.995, 1)),
        maskImage: gradient,
        WebkitMaskImage: gradient,
        zIndex: 3,
    };
});

const textInnerStyle = computed<CSSProperties>(() => {
    // Viewport-relatieve positie: de tekst start op baseTop en scrollt 1:1 mee, zodat het
    // fixed verloopmasker hem boven- en onderin het scherm laat vervagen.
    const baseTop =
        (TEXT_START_TOP / FIGMA_DESKTOP_HEIGHT) * windowHeight.value;

    return {
        position: 'absolute',
        left: '50%',
        top: `${(baseTop - scrollY.value).toFixed(1)}px`,
        width: `min(${sx(TEXT_LOCK_WIDTH)}, 90vw)`,
        transform: `translate(-50%, 0) scale(${textScale.value.toFixed(4)})`,
        transformOrigin: 'center top',
        willChange: 'transform',
    };
});

function updateProgress(): void {
    if (!heroRef.value) {
        return;
    }

    // De animatie loopt over de eerste viewport-hoogte van de hero.
    const scrolled = window.scrollY - heroRef.value.offsetTop;
    progress.value = Math.max(0, Math.min(scrolled / window.innerHeight, 1));
}

function onScroll(): void {
    scrollY.value = window.scrollY;
    updateProgress();
}

function onResize(): void {
    windowWidth.value = window.innerWidth;
    windowHeight.value = window.innerHeight;
    updateProgress();
}

onMounted(() => {
    scrollY.value = window.scrollY;
    onResize();
    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onResize, { passive: true });
});

onBeforeUnmount(() => {
    window.removeEventListener('scroll', onScroll);
    window.removeEventListener('resize', onResize);
});
</script>

<template>
    <!-- Gele opening, 140vh: de tekst schaalt op ~111vh; daarna 30vh ademruimte. -->
    <section
        ref="heroRef"
        data-section="hero"
        class="relative overflow-hidden bg-[#ffc700]"
        style="height: 140vh"
    >
        <div
            class="pointer-events-none absolute right-0 left-0"
            :style="{ top: sy(387) }"
        >
            <h1
                class="text-center font-avenir font-black text-black italic"
                :style="titleStyle"
            >
                {{ title }}
            </h1>
        </div>

        <div :style="textContainerStyle">
            <div :style="textInnerStyle">
                <p
                    class="text-center font-avenir whitespace-pre-line text-black"
                    :style="textStyle"
                >
                    {{ description }}
                </p>
            </div>
        </div>
    </section>
</template>
