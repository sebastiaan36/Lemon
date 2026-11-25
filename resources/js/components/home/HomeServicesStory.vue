<script setup lang="ts">
import { computed } from 'vue';
import type { CSSProperties } from 'vue';
import CmsImage from '@/components/CmsImage.vue';
import HlsVideo from '@/components/HlsVideo.vue';
import { ease, lerp, rangeProgress } from '@/lib/scrollAnimation';
import type { HomeService } from '@/types';

/**
 * Rustige services-sectie (Home V2): één doorlopende video in een sticky
 * scherm, alleen de tekst wisselt per dienst. De video groeit bij binnenkomst
 * van een kaart op geel naar schermvullend en schuift aan het eind gewoon mee
 * omhoog — geen scroll-locks, geen vaste lagen over de pagina.
 */
const props = defineProps<{
    services: HomeService[];
    video: { url: string; type: 'image' | 'video' } | null;
    /** 0 → 1 terwijl de sectie van onderaf het scherm in schuift. */
    enterProgress: number;
    /** 0 → 1 over het sticky deel; verdeelt de diensten. */
    storyProgress: number;
    viewportWidth: number;
}>();

const isCompact = computed(() => props.viewportWidth < 768);

// Val terug op de media van de eerste dienst zolang er geen doorlopende video is.
const media = computed(() => {
    if (props.video) {
        return props.video;
    }

    const first = props.services.find((service) => service.mediaUrl);

    return first?.mediaUrl
        ? { url: first.mediaUrl, type: first.mediaType ?? 'image' }
        : null;
});

const activeIndex = computed(() =>
    Math.min(
        props.services.length - 1,
        Math.floor(props.storyProgress * props.services.length),
    ),
);

/** Voortgang binnen de actieve dienst (voor de dunne voortgangsbalkjes). */
function serviceFill(index: number): number {
    const span = 1 / props.services.length;

    return rangeProgress(props.storyProgress, index * span, (index + 1) * span);
}

const expandT = computed(() =>
    ease(rangeProgress(props.enterProgress, 0.15, 1)),
);

// Kaart op geel → schermvullend: inset krimpt mee met het binnenscrollen.
const frameStyle = computed<CSSProperties>(() => {
    const t = expandT.value;
    const side = isCompact.value ? 20 : props.viewportWidth * 0.1;
    const vertical = isCompact.value ? 18 : 12;

    return {
        clipPath: `inset(${lerp(vertical, 0, t).toFixed(2)}vh ${lerp(side, 0, t).toFixed(1)}px round ${lerp(24, 0, t).toFixed(1)}px)`,
    };
});

const videoScale = computed(() => lerp(1.08, 1, expandT.value));

const textOpacity = computed(() =>
    ease(rangeProgress(props.enterProgress, 0.75, 1)),
);
</script>

<template>
    <div class="sticky top-0 h-svh overflow-hidden bg-[#ffc700] md:h-screen">
        <div class="absolute inset-0 bg-[#0c0c0c]" :style="frameStyle">
            <div
                class="absolute inset-0"
                :style="{ transform: `scale(${videoScale.toFixed(3)})` }"
            >
                <HlsVideo
                    v-if="media?.type === 'video'"
                    :src="media.url"
                    autoplay
                    loop
                    muted
                    playsinline
                    class="h-full w-full object-cover"
                />
                <CmsImage
                    v-else-if="media"
                    sizes="100vw"
                    :src="media.url"
                    alt=""
                    class="h-full w-full object-cover"
                />
            </div>
            <div
                class="absolute inset-0"
                style="
                    background: linear-gradient(
                        180deg,
                        rgba(0, 0, 0, 0.1) 0%,
                        rgba(0, 0, 0, 0) 30%,
                        rgba(0, 0, 0, 0.45) 100%
                    );
                "
            />
        </div>

        <div
            class="absolute inset-0 px-5 pt-[110px] pb-[96px] md:px-[59px] md:pb-[88px]"
            :style="{ opacity: textOpacity }"
        >
            <div
                class="flex h-full flex-col justify-end gap-5 md:flex-row md:items-end md:justify-between md:gap-12"
            >
                <div>
                    <p
                        class="mb-1 font-avenir text-[16px] tracking-[-0.16px] text-[#ffc700] md:mb-2 md:text-[26px] md:tracking-[-0.78px]"
                    >
                        Services
                    </p>
                    <ul>
                        <li
                            v-for="(service, i) in services"
                            :key="service.name"
                            class="font-avenir text-[clamp(32px,9vw,44px)] leading-[1.05] font-black tracking-[-0.03em] text-white italic transition-opacity duration-700 md:text-[clamp(56px,6.8vw,86px)] md:leading-[1.25]"
                            :style="{ opacity: activeIndex === i ? 1 : 0.25 }"
                        >
                            {{ service.name }}
                        </li>
                    </ul>
                </div>

                <div class="relative md:w-[min(559px,42vw)]">
                    <!-- Alle teksten gestapeld in één cel: de hoogste bepaalt de hoogte, dus niets springt. -->
                    <div class="grid">
                        <div
                            v-for="(service, i) in services"
                            :key="service.name + '-detail'"
                            class="col-start-1 row-start-1 self-end transition-[opacity,transform] duration-700 ease-out"
                            :style="{
                                opacity: activeIndex === i ? 1 : 0,
                                transform: `translateY(${activeIndex === i ? 0 : 12}px)`,
                            }"
                            :aria-hidden="activeIndex !== i"
                        >
                            <p
                                class="mb-4 font-avenir text-[16px] leading-[24px] tracking-[-0.16px] text-white md:mb-5 md:text-[22px] md:leading-[32px] md:tracking-[-0.22px]"
                            >
                                {{ service.description }}
                            </p>
                            <div class="flex flex-wrap gap-2">
                                <span
                                    v-for="tag in service.tags"
                                    :key="tag"
                                    class="rounded-[40px] border border-white px-3 py-1 font-avenir text-[12px] leading-[20px] tracking-[-0.12px] text-white md:px-4 md:py-1.5 md:text-[14px] md:leading-[24px] md:tracking-[-0.14px]"
                                >
                                    {{ tag }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex gap-2 md:mt-8" aria-hidden="true">
                        <span
                            v-for="(service, i) in services"
                            :key="service.name + '-bar'"
                            class="h-[2px] flex-1 overflow-hidden rounded-full bg-white/25"
                        >
                            <span
                                class="block h-full origin-left bg-[#ffc700]"
                                :style="{
                                    transform: `scaleX(${serviceFill(i).toFixed(3)})`,
                                }"
                            />
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
