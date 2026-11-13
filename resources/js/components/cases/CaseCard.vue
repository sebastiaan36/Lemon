<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import CmsImage from '@/components/CmsImage.vue';
import HlsVideo from '@/components/HlsVideo.vue';

export type CaseCardItem = {
    name: string;
    slug: string;
    photo: string | null;
    video: string | null;
    autoplayVideo: boolean;
    touchpoints: string[];
};

const props = defineProps<{
    caseItem: CaseCardItem;
    rotation: string;
    widthClass: string;
    /** Verhouding van de kaart; Figma gebruikt bredere kaarten dan 4:3. */
    aspectClass?: string;
}>();

const videoRef = ref<InstanceType<typeof HlsVideo> | null>(null);

function onMouseEnter(): void {
    if (props.caseItem.autoplayVideo) {
        return;
    }

    void videoRef.value?.videoEl?.play().catch(() => undefined);
}

function onMouseLeave(): void {
    const video = videoRef.value?.videoEl;

    if (props.caseItem.autoplayVideo || !video) {
        return;
    }

    video.pause();
    video.currentTime = 0;
}
</script>

<template>
    <div :class="widthClass" :style="{ transform: `rotate(${rotation})` }">
        <Link
            :href="`/cases/${caseItem.slug}`"
            class="group relative block overflow-hidden rounded-[16px]"
            @mouseenter="onMouseEnter"
            @mouseleave="onMouseLeave"
        >
            <div
                class="relative bg-neutral-900"
                :class="aspectClass ?? 'aspect-[4/3]'"
            >
                <CmsImage
                    sizes="(min-width: 1024px) 50vw, 100vw"
                    v-if="caseItem.photo && !caseItem.autoplayVideo"
                    :src="caseItem.photo"
                    :alt="caseItem.name"
                    class="absolute inset-0 h-full w-full object-cover transition-transform duration-700 group-hover:scale-[1.03]"
                />
                <HlsVideo
                    v-if="caseItem.video"
                    ref="videoRef"
                    :src="caseItem.video"
                    :autoplay="caseItem.autoplayVideo"
                    muted
                    loop
                    playsinline
                    :preload="caseItem.autoplayVideo ? 'auto' : 'none'"
                    class="absolute inset-0 h-full w-full object-cover transition-transform duration-700 group-hover:scale-[1.03]"
                    :class="
                        caseItem.autoplayVideo
                            ? ''
                            : 'opacity-0 transition-opacity duration-300 group-hover:opacity-100'
                    "
                />
            </div>
            <div
                class="pointer-events-none absolute inset-0 bg-gradient-to-b from-black/35 via-transparent to-transparent"
            />
            <p
                class="absolute top-[20px] left-[20px] font-avenir text-[26px] leading-[28px] tracking-[-0.8px] text-[#ffc700]"
                style="font-weight: 900; font-style: oblique"
            >
                {{ caseItem.name }}
            </p>
        </Link>
    </div>
</template>
