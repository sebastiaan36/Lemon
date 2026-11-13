<script setup lang="ts">
import { computed } from 'vue';
import type { CSSProperties } from 'vue';
import HlsVideo from '@/components/HlsVideo.vue';
import { ease, isVideoUrl, rangeProgress } from '@/lib/scrollAnimation';

const props = defineProps<{
    description: string;
    buttonText: string;
    buttonHref: string;
    photos: string[];
    teamProgress: number;
    viewportHeight: number;
}>();

// Exacte posities uit Figma (frame "Team Lemon", 1640×900): left/width = % van breedte, top/height = vh.
const TEAM_PHOTO_POSITIONS = [
    {
        left: '12.6%',
        top: '19.6vh',
        width: '12.7%',
        height: '29.9vh',
        rotate: 2.66,
    },
    { left: '68%', top: '3vh', width: '13.6%', height: '32.9vh', rotate: 1.87 },
    {
        left: '92.3%',
        top: '34.8vh',
        width: '9.4%',
        height: '22.8vh',
        rotate: 0,
    },
    {
        left: '-1.2%',
        top: '55vh',
        width: '14.6%',
        height: '37vh',
        rotate: -2.14,
    },
    {
        left: '69.8%',
        top: 'calc(63vh + 50px)',
        width: '17.2%',
        height: '40.9vh',
        rotate: -2.67,
    },
] as const;

const PLACEHOLDER_CLASSES = [
    'bg-neutral-900',
    'bg-neutral-800',
    'bg-neutral-700',
    'bg-neutral-900',
    'bg-neutral-800',
] as const;

// Subtiele, continue upward drift vanaf teamProgress 0.
// Iedere foto beweegt net iets anders voor een natuurlijker parallax-effect.
const TEAM_PHOTO_DRIFT_VH = [0.05, 0.065, 0.08, 0.095, 0.11];

const enterT = computed(() => ease(rangeProgress(props.teamProgress, 0, 0.08)));

function teamPhotoStyle(index: number): CSSProperties {
    const pos = TEAM_PHOTO_POSITIONS[index];
    const dist = (TEAM_PHOTO_DRIFT_VH[index] ?? 0.08) * props.viewportHeight;
    const drift = ease(rangeProgress(props.teamProgress, 0, 1));
    const offsetY = (1 - enterT.value) * 18;

    return {
        left: pos.left,
        top: pos.top,
        width: pos.width,
        height: pos.height,
        transformOrigin: 'center',
        opacity: 1,
        transform: `translate3d(0, ${offsetY - drift * dist}px, 0) rotate(${pos.rotate}deg)`,
        willChange: 'transform',
        backfaceVisibility: 'hidden',
    };
}

// "Team Lemon" text exits slower than photos
const teamTextExitStyle = computed<CSSProperties>(() => ({
    opacity: 1,
    transform: `translate3d(0, ${(1 - enterT.value) * 28 - props.teamProgress * props.viewportHeight * 0.035}px, 0)`,
    willChange: 'transform',
    backfaceVisibility: 'hidden',
}));
</script>

<template>
    <div class="sticky top-0 min-h-screen bg-[#ffc700]">
        <div
            class="relative flex min-h-screen w-full flex-col overflow-visible pb-[80px]"
        >
            <!-- "Team Lemon" achtergrondtekst — exits upward faster than photos -->
            <div
                class="relative z-10 flex min-h-[720px] w-full items-center justify-center pt-[140px]"
            >
                <div
                    class="pointer-events-none flex flex-col items-center justify-center"
                    style="user-select: none"
                    :style="teamTextExitStyle"
                >
                    <p
                        class="text-center font-avenir text-black"
                        style="
                            font-weight: 900;
                            font-style: oblique;
                            font-size: clamp(160px, 21vw, 339px);
                            line-height: 0.9;
                            letter-spacing: -0.04em;
                        "
                    >
                        Team<br />Lemon
                    </p>
                </div>
            </div>

            <!-- Zwevende polaroid-foto's (5 stuks) — each exits at its own speed -->
            <div
                v-for="(pos, i) in TEAM_PHOTO_POSITIONS"
                :key="i"
                class="absolute overflow-hidden rounded-[14px]"
                :style="teamPhotoStyle(i)"
            >
                <template v-if="photos[i]">
                    <HlsVideo
                        v-if="isVideoUrl(photos[i])"
                        :src="photos[i]"
                        class="h-full w-full object-cover"
                        autoplay
                        loop
                        muted
                        playsinline
                    />
                    <img
                        v-else
                        :src="photos[i]"
                        :alt="`Team Lemon — foto ${i + 1}`"
                        class="h-full w-full object-cover"
                    />
                </template>
                <div
                    v-else
                    class="h-full w-full"
                    :class="PLACEHOLDER_CLASSES[i]"
                />
            </div>

            <!-- Beschrijving + knop -->
            <div class="relative z-20 mt-auto flex w-full justify-center px-8">
                <div
                    class="pointer-events-none flex flex-col items-center"
                    style="opacity: 1; transform: translateY(0)"
                >
                    <p
                        class="mb-6 max-w-[390px] text-center font-avenir text-black"
                        style="
                            font-weight: 400;
                            font-size: 18px;
                            line-height: 24px;
                            letter-spacing: -0.18px;
                        "
                    >
                        {{ description }}
                    </p>
                    <a
                        :href="buttonHref || '#about'"
                        class="pointer-events-auto flex h-[65px] w-[155px] items-center justify-center rounded-[34px] border border-black font-avenir text-black"
                        style="font-size: 23px; letter-spacing: -0.69px"
                    >
                        {{ buttonText || 'About us' }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>
