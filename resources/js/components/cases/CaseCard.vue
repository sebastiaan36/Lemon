<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

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
}>();

const videoRef = ref<HTMLVideoElement | null>(null);

function onMouseEnter(): void {
    if (props.caseItem.autoplayVideo) {
        return;
    }

    void videoRef.value?.play().catch(() => undefined);
}

function onMouseLeave(): void {
    if (props.caseItem.autoplayVideo || !videoRef.value) {
        return;
    }

    videoRef.value.pause();
    videoRef.value.currentTime = 0;
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
            <div class="relative aspect-[4/3] bg-neutral-900">
                <img
                    v-if="caseItem.photo && !caseItem.autoplayVideo"
                    :src="caseItem.photo"
                    :alt="caseItem.name"
                    class="absolute inset-0 h-full w-full object-cover transition-transform duration-700 group-hover:scale-[1.03]"
                />
                <video
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
