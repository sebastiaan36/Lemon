<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import SiteFooter from '@/components/SiteFooter.vue';
import SiteHeader from '@/components/SiteHeader.vue';

type Blog = {
    title: string;
    slug: string;
    headerPhoto: string | null;
    excerpt: string | null;
    publishedAt: string | null;
};

type RowItem = { type: 'blog'; data: Blog };

const props = defineProps<{
    blogs: Blog[];
}>();

const items = computed<RowItem[]>(() =>
    props.blogs.map((blog) => ({ type: 'blog', data: blog })),
);

const rows = computed<[RowItem, RowItem | null][]>(() => {
    const pairs: [RowItem, RowItem | null][] = [];

    for (let i = 0; i < items.value.length; i += 2) {
        pairs.push([items.value[i], items.value[i + 1] ?? null]);
    }

    return pairs;
});

const rotations = [
    '-0.7deg',
    '0.08deg',
    '0.22deg',
    '-0.48deg',
    '0.13deg',
    '0.1deg',
    '0.14deg',
    '-0.54deg',
];
function cardRotation(seed: number): string {
    return rotations[seed % rotations.length];
}

function leftColClass(rowIndex: number): string {
    return rowIndex % 2 === 0
        ? 'w-full md:w-[calc(50%-10px)] md:flex-none'
        : 'w-full md:w-[calc(50%-10px)] md:flex-none md:flex md:justify-start';
}

function rightColClass(rowIndex: number): string {
    return rowIndex % 2 === 0
        ? 'w-full md:w-[calc(50%-10px)] md:flex-none md:flex md:justify-end'
        : 'w-full md:w-[calc(50%-10px)] md:flex-none';
}

function leftCardClass(rowIndex: number): string {
    return rowIndex % 2 === 0 ? 'w-full' : 'w-full md:w-[78%]';
}

function rightCardClass(rowIndex: number): string {
    return rowIndex % 2 === 0 ? 'w-full md:w-[78%]' : 'w-full';
}
</script>

<template>
    <Head>
        <title>Blog — Lemon Scented Tea</title>
    </Head>

    <div class="min-h-screen bg-black text-white">
        <SiteHeader />

        <section
            class="px-[24px] pt-[170px] pb-[70px] text-center md:px-[59px] lg:pt-[220px]"
        >
            <h1
                class="inline text-[76px] leading-none font-black tracking-[-3px] italic md:text-[120px] lg:text-[150px] lg:tracking-[-4.5px]"
                style="font-family: 'Avenir', system-ui, sans-serif"
            >
                <span class="text-[#ffc700]">Lemon </span>
                <span class="text-white">blog</span>
            </h1>
        </section>

        <section class="px-[24px] pb-[110px] md:px-[59px]">
            <p
                v-if="!blogs.length"
                class="text-center text-[22px] text-white/50"
                style="font-family: 'Avenir', system-ui, sans-serif"
            >
                Er zijn nog geen blogs gepubliceerd.
            </p>

            <div v-else class="flex flex-col gap-16 md:gap-32">
                <div
                    v-for="(row, rowIndex) in rows"
                    :key="rowIndex"
                    class="flex flex-col items-center gap-16 md:flex-row md:gap-5"
                >
                    <div :class="leftColClass(rowIndex)">
                        <div
                            :class="leftCardClass(rowIndex)"
                            :style="{
                                transform: `rotate(${cardRotation(rowIndex * 2)})`,
                            }"
                        >
                            <Link
                                :href="`/blog/${row[0].data.slug}`"
                                class="group relative block overflow-hidden rounded-[16px]"
                            >
                                <div
                                    class="relative aspect-[4/3] bg-neutral-900"
                                >
                                    <img
                                        v-if="row[0].data.headerPhoto"
                                        :src="row[0].data.headerPhoto"
                                        :alt="row[0].data.title"
                                        class="absolute inset-0 h-full w-full object-cover transition-transform duration-700 group-hover:scale-[1.03]"
                                    />
                                    <div
                                        v-else
                                        class="absolute inset-0 bg-[#ffc700]"
                                    />
                                </div>

                                <div
                                    class="pointer-events-none absolute inset-0 bg-gradient-to-b from-black/45 via-transparent to-black/20"
                                />

                                <p
                                    class="absolute top-[20px] left-[20px] max-w-[calc(100%-40px)] text-[26px] leading-[28px] tracking-[-0.8px] text-[#ffc700]"
                                    style="
                                        font-family:
                                            'Avenir', system-ui, sans-serif;
                                        font-weight: 900;
                                        font-style: oblique;
                                    "
                                >
                                    {{ row[0].data.title }}
                                </p>

                                <p
                                    v-if="row[0].data.publishedAt"
                                    class="absolute bottom-[20px] left-[20px] text-[14px] tracking-[-0.42px] text-white/80"
                                    style="
                                        font-family:
                                            'Avenir', system-ui, sans-serif;
                                    "
                                >
                                    {{ row[0].data.publishedAt }}
                                </p>
                            </Link>
                        </div>
                    </div>

                    <div v-if="row[1]" :class="rightColClass(rowIndex)">
                        <div
                            :class="rightCardClass(rowIndex)"
                            :style="{
                                transform: `rotate(${cardRotation(rowIndex * 2 + 1)})`,
                            }"
                        >
                            <Link
                                :href="`/blog/${row[1].data.slug}`"
                                class="group relative block overflow-hidden rounded-[16px]"
                            >
                                <div
                                    class="relative aspect-[4/3] bg-neutral-900"
                                >
                                    <img
                                        v-if="row[1].data.headerPhoto"
                                        :src="row[1].data.headerPhoto"
                                        :alt="row[1].data.title"
                                        class="absolute inset-0 h-full w-full object-cover transition-transform duration-700 group-hover:scale-[1.03]"
                                    />
                                    <div
                                        v-else
                                        class="absolute inset-0 bg-[#ffc700]"
                                    />
                                </div>

                                <div
                                    class="pointer-events-none absolute inset-0 bg-gradient-to-b from-black/45 via-transparent to-black/20"
                                />

                                <p
                                    class="absolute top-[20px] left-[20px] max-w-[calc(100%-40px)] text-[26px] leading-[28px] tracking-[-0.8px] text-[#ffc700]"
                                    style="
                                        font-family:
                                            'Avenir', system-ui, sans-serif;
                                        font-weight: 900;
                                        font-style: oblique;
                                    "
                                >
                                    {{ row[1].data.title }}
                                </p>

                                <p
                                    v-if="row[1].data.publishedAt"
                                    class="absolute bottom-[20px] left-[20px] text-[14px] tracking-[-0.42px] text-white/80"
                                    style="
                                        font-family:
                                            'Avenir', system-ui, sans-serif;
                                    "
                                >
                                    {{ row[1].data.publishedAt }}
                                </p>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <SiteFooter />
    </div>
</template>
