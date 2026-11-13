<script setup lang="ts">
import CmsImage from '@/components/CmsImage.vue';
import HlsVideo from '@/components/HlsVideo.vue';

defineProps<{
    media: { url: string; type: string } | null;
    clientName?: string | null;
    tags?: string[];
}>();
</script>

<template>
    <!-- Figma: grote afgeronde kaart (805×978) met klantnaam linksonder en tags rechtsonder -->
    <div
        class="relative aspect-[805/978] overflow-hidden rounded-[20px] bg-neutral-900 lg:rounded-[30px]"
    >
        <HlsVideo
            v-if="media && media.type === 'video'"
            :src="media.url"
            autoplay
            loop
            muted
            playsinline
            class="absolute inset-0 size-full object-cover"
        />
        <CmsImage
            sizes="(min-width: 1024px) 50vw, 100vw"
            v-else-if="media"
            :src="media.url"
            :alt="clientName ?? ''"
            class="absolute inset-0 size-full object-cover"
        />

        <div
            v-if="clientName || tags?.length"
            class="absolute inset-x-0 bottom-0 flex items-end justify-between gap-3 bg-gradient-to-t from-black/40 to-transparent px-5 pt-16 pb-5 md:flex-wrap md:px-[5.6%] md:pb-[4.7%]"
        >
            <p
                v-if="clientName"
                class="font-avenir text-[clamp(20px,1.62vw,26.5px)] leading-none font-black tracking-[-0.03em] text-[#ffc700] italic"
            >
                {{ clientName }}
            </p>
            <ul
                v-if="tags?.length"
                class="ml-auto flex shrink-0 flex-col items-end gap-1.5 md:flex-row md:flex-wrap md:justify-end md:gap-2"
            >
                <li
                    v-for="tag in tags.slice(0, 3)"
                    :key="tag"
                    class="flex h-[28px] items-center rounded-[40px] border border-white px-3 font-avenir text-[12px] tracking-[-0.12px] whitespace-nowrap text-white md:h-[36px] md:px-[18px] md:text-[14px] md:tracking-[-0.14px]"
                >
                    {{ tag }}
                </li>
            </ul>
        </div>
    </div>
</template>
