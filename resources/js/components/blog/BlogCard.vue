<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import CmsImage from '@/components/CmsImage.vue';

export type BlogCardItem = {
    title: string;
    slug: string;
    headerPhoto: string | null;
    publishedAt: string | null;
};

defineProps<{
    blog: BlogCardItem;
}>();
</script>

<template>
    <!--
        Figma: staande kaart (452×550 in het overzicht, 537×653 bij "More articles").
        Beide varianten hebben dezelfde verhoudingen, dus tekst en padding schalen
        met de kaartbreedte via container query units (cqw).
    -->
    <Link
        :href="`/blog/${blog.slug}`"
        class="group [container-type:inline-size] relative block aspect-[452/550] overflow-hidden rounded-[16px] bg-neutral-900"
    >
        <CmsImage
            sizes="(min-width: 768px) 452px, 90vw"
            v-if="blog.headerPhoto"
            :src="blog.headerPhoto"
            :alt="blog.title"
            draggable="false"
            class="absolute inset-0 size-full object-cover transition-transform duration-700 group-hover:scale-[1.03]"
        />
        <div v-else class="absolute inset-0 bg-[#ffc700]/80" />

        <div
            class="pointer-events-none absolute inset-0"
            style="
                background-image: linear-gradient(
                    186deg,
                    rgba(0, 0, 0, 0) 39%,
                    rgba(0, 0, 0, 0.7) 69%
                );
            "
        />

        <div class="absolute inset-x-0 bottom-0 px-[11.5cqw] pb-[8cqw]">
            <p
                class="max-w-[77cqw] font-avenir text-[max(20px,6.33cqw)] leading-[1.29] font-black tracking-[-0.03em] text-white italic"
            >
                {{ blog.title }}
            </p>
            <p
                v-if="blog.publishedAt"
                class="mt-[3.2cqw] font-avenir text-[max(14px,3.54cqw)] tracking-[-0.03em] text-[#ffc700]"
            >
                {{ blog.publishedAt }}
            </p>
        </div>
    </Link>
</template>
