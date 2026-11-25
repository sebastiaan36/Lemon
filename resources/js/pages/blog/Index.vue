<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import BlogCard from '@/components/blog/BlogCard.vue';
import PagePills from '@/components/PagePills.vue';
import SiteFooter from '@/components/SiteFooter.vue';
import SiteHeader from '@/components/SiteHeader.vue';

type Blog = {
    title: string;
    slug: string;
    category: string | null;
    headerPhoto: string | null;
    excerpt: string | null;
    publishedAt: string | null;
};

const props = defineProps<{
    blogs: Blog[];
}>();

const SHOW_ALL = '';

const selectedCategory = ref(SHOW_ALL);

const categories = computed(() =>
    [
        ...new Set(
            props.blogs
                .map((blog) => blog.category?.trim())
                .filter((category): category is string => !!category),
        ),
    ].sort((a, b) => a.localeCompare(b)),
);

const visibleBlogs = computed(() =>
    selectedCategory.value === SHOW_ALL
        ? props.blogs
        : props.blogs.filter(
              (blog) => blog.category?.trim() === selectedCategory.value,
          ),
);
</script>

<template>
    <Head>
        <title>Blog — Lemon Scented Tea</title>
    </Head>

    <div class="min-h-screen overflow-x-clip bg-black text-white">
        <SiteHeader hide-top-gradient invert-logo cta-style="outline" />

        <!-- Figma: "Lemon insights" (150px) met filter eronder -->
        <section
            data-pill-section="hero"
            class="px-5 pt-[130px] pb-10 text-center md:pt-[180px] md:pb-[72px] lg:pt-[253px] lg:pb-[127px]"
        >
            <h1
                class="font-avenir text-[clamp(64px,9.15vw,150px)] leading-[0.87] font-black tracking-[-0.03em] italic"
            >
                <span class="text-[#ffc700]">Lemon </span>
                <span class="text-white">insights</span>
            </h1>

            <div
                class="mt-8 flex items-center justify-center gap-[23px] font-avenir text-[16px] tracking-[-0.48px] md:mt-[37px]"
            >
                <label for="blog-filter">Filter</label>
                <!-- Rand op de wrapper i.p.v. de select: iOS Safari tekent
                     randen/focus op een native select onvoorspelbaar. -->
                <div
                    class="relative h-[50px] w-[207px] overflow-hidden rounded-[30px] border border-white/40 bg-black transition-colors focus-within:border-white"
                >
                    <select
                        id="blog-filter"
                        v-model="selectedCategory"
                        class="block size-full cursor-pointer appearance-none rounded-none border-0 bg-transparent pr-12 pl-8 font-avenir text-[16px] text-white shadow-none outline-none [-webkit-tap-highlight-color:transparent]"
                    >
                        <option :value="SHOW_ALL">Show all</option>
                        <option
                            v-for="category in categories"
                            :key="category"
                            :value="category"
                        >
                            {{ category }}
                        </option>
                    </select>
                    <img
                        src="/figma-assets/filter-chevron.svg"
                        alt=""
                        width="11.6379"
                        height="11.1724"
                        class="pointer-events-none absolute top-[17px] right-[22px] -rotate-45"
                    />
                </div>
            </div>
        </section>

        <section
            data-pill-section="posts"
            class="px-5 pb-[110px] md:px-[6.2vw] md:pb-[160px]"
        >
            <p
                v-if="!visibleBlogs.length"
                class="text-center font-avenir text-[22px] text-white/50"
            >
                There are no insights published yet.
            </p>

            <div
                v-else
                class="mx-auto grid max-w-[1428px] grid-cols-1 gap-x-[clamp(16px,2.2vw,36px)] gap-y-[clamp(16px,1.65vw,27px)] sm:grid-cols-2 lg:grid-cols-3"
            >
                <BlogCard
                    v-for="blog in visibleBlogs"
                    :key="blog.slug"
                    :blog="blog"
                />
            </div>
        </section>

        <PagePills page="blog-index" />

        <SiteFooter id="contact" />
    </div>
</template>
