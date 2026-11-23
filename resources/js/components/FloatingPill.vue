<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import type { CSSProperties } from 'vue';
import CmsImage from '@/components/CmsImage.vue';
import HlsVideo from '@/components/HlsVideo.vue';
import { isLightBackgroundAt } from '@/composables/useLightBackgroundBehindHeader';
import { isVideoUrl } from '@/lib/scrollAnimation';

const props = withDefaults(
    defineProps<{
        label: string;
        href: string;
        avatars?: string[];
        /**
         * Zichtbaarheid 0–1 (scroll-gestuurd). Laat leeg voor automatisch gedrag:
         * zichtbaar na een stukje scrollen, verborgen zodra de footer in beeld komt.
         */
        visibility?: number | null;
        /** Afstand tot de onderkant van de viewport op desktop (px). */
        desktopBottom?: number;
        /** Fade in/uit bij een wisselende (aan/uit) visibility i.p.v. scroll-gestuurd. */
        animated?: boolean;
        /** Id van de footer die de pill wegduwt in automatische modus. */
        footerId?: string;
    }>(),
    {
        visibility: null,
        avatars: () => [],
        desktopBottom: 24,
        footerId: 'contact',
        animated: false,
    },
);

const AUTO_SHOW_AFTER_SCROLL = 160;

// Op zwart, foto's en video's wordt de pill wit met zwarte tekst; op geel/wit blijft hij zwart.
const pillRef = ref<HTMLElement | null>(null);
const overDarkBackground = ref(false);
let backgroundFrame = 0;

function measureBackground(): void {
    backgroundFrame = 0;
    const pill = pillRef.value;

    if (!pill) {
        return;
    }

    const rect = pill.getBoundingClientRect();
    const light = isLightBackgroundAt(
        rect.left + rect.width / 2,
        rect.top + rect.height / 2,
        (element) => pill.contains(element),
    );

    overDarkBackground.value = light === false;
}

function scheduleBackgroundMeasure(): void {
    if (!backgroundFrame) {
        backgroundFrame = requestAnimationFrame(measureBackground);
    }
}

onMounted(() => {
    measureBackground();
    window.addEventListener('scroll', scheduleBackgroundMeasure, {
        passive: true,
    });
    window.addEventListener('resize', scheduleBackgroundMeasure, {
        passive: true,
    });
});

onBeforeUnmount(() => {
    window.removeEventListener('scroll', scheduleBackgroundMeasure);
    window.removeEventListener('resize', scheduleBackgroundMeasure);

    if (backgroundFrame) {
        cancelAnimationFrame(backgroundFrame);
    }
});

const hasScrolled = ref(false);
const footerInView = ref(false);
let footerObserver: IntersectionObserver | null = null;

function onScroll(): void {
    hasScrolled.value = window.scrollY > AUTO_SHOW_AFTER_SCROLL;
}

onMounted(() => {
    if (props.visibility !== null) {
        return;
    }

    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });

    const footer = document.getElementById(props.footerId);

    if (footer && typeof IntersectionObserver !== 'undefined') {
        footerObserver = new IntersectionObserver((entries) => {
            footerInView.value = entries[0]?.isIntersecting ?? false;
        });
        footerObserver.observe(footer);
    }
});

onBeforeUnmount(() => {
    window.removeEventListener('scroll', onScroll);
    footerObserver?.disconnect();
});

const resolvedVisibility = computed(() => {
    if (props.visibility !== null) {
        return props.visibility;
    }

    return hasScrolled.value && !footerInView.value ? 1 : 0;
});

// Figma: zwevende pill rechtsonder (387×73), op mobiel gecentreerd onderin.
const wrapperStyle = computed<CSSProperties>(() => {
    const visibility = resolvedVisibility.value;

    return {
        '--pill-bottom': `${props.desktopBottom}px`,
        opacity: visibility,
        transform: `translateY(${((1 - visibility) * 24).toFixed(1)}px)`,
        pointerEvents: visibility > 0.5 ? 'auto' : 'none',
        visibility: visibility <= 0.01 ? 'hidden' : 'visible',
        transition:
            props.visibility === null || props.animated
                ? 'opacity 0.35s ease, transform 0.35s ease, visibility 0.35s'
                : undefined,
    };
});
</script>

<template>
    <div
        class="fixed inset-x-0 bottom-6 z-40 flex justify-center px-6 md:inset-x-auto md:right-[41px] md:bottom-[var(--pill-bottom)] md:px-0"
        :style="wrapperStyle"
    >
        <a
            ref="pillRef"
            :href="href"
            class="flex h-[60px] w-full max-w-[387px] items-center gap-3 rounded-[73px] border pr-2 pl-3 font-avenir backdrop-blur-[6px] transition-colors duration-300 md:h-[73px] md:w-auto md:max-w-none md:min-w-[387px] md:gap-4 md:pr-[15px] md:pl-4"
            :class="
                overDarkBackground
                    ? 'border-black/5 bg-white/50 text-black'
                    : 'border-white/5 bg-black/[0.82] text-white'
            "
            style="box-shadow: inset 0 -2.4px 4.9px 0 rgba(0, 0, 0, 0.1)"
        >
            <!-- #leading vervangt de avatars, bv. door een uitstekende cover-afbeelding -->
            <slot name="leading">
                <span class="flex shrink-0 -space-x-2">
                    <template v-for="(avatar, i) in avatars" :key="i">
                        <HlsVideo
                            v-if="isVideoUrl(avatar)"
                            :src="avatar"
                            autoplay
                            loop
                            muted
                            playsinline
                            class="size-8 shrink-0 rounded-full border-2 object-cover transition-colors duration-300 md:size-9"
                            :class="
                                overDarkBackground
                                    ? 'border-white'
                                    : 'border-black'
                            "
                        />
                        <CmsImage
                            sizes="36px"
                            v-else
                            :src="avatar"
                            alt=""
                            class="size-8 shrink-0 rounded-full border-2 object-cover transition-colors duration-300 md:size-9"
                            :class="
                                overDarkBackground
                                    ? 'border-white'
                                    : 'border-black'
                            "
                        />
                    </template>
                </span>
            </slot>
            <span
                class="min-w-0 flex-1 text-[15px] leading-[1.2] tracking-[-0.16px] md:text-[18px] md:tracking-[-0.18px] md:whitespace-nowrap"
            >
                {{ label }}
            </span>
            <span
                class="flex h-[40px] w-[54px] shrink-0 items-center justify-center rounded-[69px] bg-[#ffc700] md:h-[43px] md:w-[57px]"
                aria-hidden="true"
            >
                <svg width="20" height="20" viewBox="0 0 16 16" fill="none">
                    <path
                        d="M2 8H14M14 8L9 3M14 8L9 13"
                        stroke="black"
                        stroke-width="1.3"
                    />
                </svg>
            </span>
        </a>
    </div>
</template>
