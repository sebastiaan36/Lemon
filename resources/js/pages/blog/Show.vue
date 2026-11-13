<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import type { BlogCardItem } from '@/components/blog/BlogCard.vue';
import BlogCarousel from '@/components/blog/BlogCarousel.vue';
import CmsImage from '@/components/CmsImage.vue';
import SiteFooter from '@/components/SiteFooter.vue';
import SiteHeader from '@/components/SiteHeader.vue';

type Blog = {
    title: string;
    headerPhoto: string | null;
    intro: string | null;
    tags: string[];
    author: { name: string; photo: string | null } | null;
    content: string | null;
    publishedAt: string | null;
    seoTitle: string | null;
    metaDescription: string | null;
};

defineProps<{
    blog: Blog;
    moreBlogs: BlogCardItem[];
}>();
</script>

<template>
    <Head>
        <title>
            {{ blog.seoTitle || `${blog.title} — Lemon Scented Tea` }}
        </title>
        <meta
            v-if="blog.metaDescription"
            name="description"
            :content="blog.metaDescription"
        />
    </Head>

    <div class="min-h-screen overflow-x-clip bg-black text-white">
        <SiteHeader hide-top-gradient invert-logo cta-style="outline" />

        <!-- Figma (1640): foto links (779px) blijft staan, tekst rechts scrolt -->
        <article class="lg:flex lg:items-start">
            <aside
                class="relative h-[max(560px,88svh)] w-full shrink-0 overflow-hidden lg:sticky lg:top-0 lg:h-screen lg:w-[47.5%]"
            >
                <CmsImage
                    sizes="(min-width: 1024px) 47.5vw, 100vw"
                    priority
                    v-if="blog.headerPhoto"
                    :src="blog.headerPhoto"
                    :alt="blog.title"
                    class="absolute inset-0 size-full object-cover"
                />
                <div v-else class="absolute inset-0 bg-neutral-900" />
                <div
                    class="pointer-events-none absolute inset-0"
                    style="
                        background-image: linear-gradient(
                            183deg,
                            rgba(0, 0, 0, 0) 34%,
                            rgba(0, 0, 0, 0.8) 80%
                        );
                    "
                />

                <div
                    class="absolute inset-x-0 bottom-0 px-5 pb-10 md:px-[4.8vw] md:pb-[56px] lg:pr-[120px]"
                >
                    <h1
                        class="max-w-[485px] font-avenir text-[clamp(34px,3.05vw,50px)] leading-[1.2] font-black tracking-[-0.03em] italic"
                    >
                        {{ blog.title }}
                    </h1>

                    <ul
                        v-if="blog.tags.length"
                        class="mt-6 flex flex-wrap gap-3 md:mt-[58px]"
                    >
                        <li
                            v-for="tag in blog.tags"
                            :key="tag"
                            class="flex h-[36px] items-center rounded-[40px] border border-white px-[14px] font-avenir text-[14px] tracking-[-0.14px]"
                        >
                            {{ tag }}
                        </li>
                    </ul>

                    <div
                        v-if="blog.author || blog.publishedAt"
                        class="mt-6 flex flex-wrap items-center gap-x-[17px] gap-y-1 font-avenir md:mt-[24px]"
                    >
                        <CmsImage
                            sizes="33px"
                            v-if="blog.author?.photo"
                            :src="blog.author.photo"
                            alt=""
                            width="33"
                            height="33"
                            class="size-[33px] rounded-full object-cover"
                        />
                        <span
                            v-if="blog.author"
                            class="text-[20px] tracking-[-0.03em] md:text-[24px]"
                        >
                            by {{ blog.author.name }}
                        </span>
                        <span
                            v-if="blog.publishedAt"
                            class="text-[17px] tracking-[-0.03em] text-[#ffc700] md:text-[19px]"
                        >
                            {{ blog.publishedAt }}
                        </span>
                    </div>
                </div>

                <img
                    src="/figma-assets/blog-hero-arrow.svg"
                    alt=""
                    width="80.6991"
                    height="80.7016"
                    class="pointer-events-none absolute right-[3.6%] bottom-[56px] hidden size-[clamp(48px,4.45vw,73px)] rotate-180 md:block"
                />
            </aside>

            <div
                class="px-5 pt-12 pb-20 md:px-[6.1vw] md:pt-[90px] lg:w-[52.5%] lg:pt-[225px] lg:pb-[67px]"
            >
                <div class="max-w-[645px]">
                    <p
                        v-if="blog.intro"
                        class="font-avenir text-[24px] leading-[1.31] tracking-[-0.03em] whitespace-pre-line md:text-[32px]"
                    >
                        {{ blog.intro }}
                    </p>

                    <div
                        v-if="blog.content"
                        class="blog-content font-avenir text-[18px] leading-[1.45] tracking-[-0.03em] text-white/80 md:text-[24px] md:leading-[32px]"
                        :class="{ 'mt-12 md:mt-[80px]': blog.intro }"
                        v-html="blog.content"
                    />
                </div>
            </div>
        </article>

        <section
            v-if="moreBlogs.length"
            class="border-t border-white/20 pt-14 pb-20 md:pt-[78px] md:pb-[94px]"
        >
            <h2
                class="mb-10 flex items-center gap-6 px-5 font-avenir text-[36px] leading-none tracking-[-0.03em] md:mb-[80px] md:gap-[53px] md:pl-[8.2vw] md:text-[50px]"
            >
                <img
                    loading="lazy"
                    src="/figma-assets/more-articles-arrow.svg"
                    alt=""
                    width="33.5439"
                    height="33.5447"
                    class="size-[24px] -rotate-90 md:size-[31px]"
                />
                More articles
            </h2>

            <BlogCarousel :blogs="moreBlogs" />
        </section>

        <SiteFooter id="contact" />
    </div>
</template>

<style scoped>
.blog-content :deep(p) {
    margin-bottom: 1lh;
}

.blog-content :deep(p:last-child) {
    margin-bottom: 0;
}

.blog-content :deep(h1),
.blog-content :deep(h2),
.blog-content :deep(h3),
.blog-content :deep(h4) {
    margin-top: 1lh;
    color: #ffc700;
    font-size: inherit;
    font-weight: 800;
    line-height: inherit;
}

.blog-content :deep(:is(h1, h2, h3, h4):first-child) {
    margin-top: 0;
}

.blog-content :deep(strong),
.blog-content :deep(b) {
    color: #fff;
    font-weight: 800;
}

.blog-content :deep(a) {
    color: #ffc700;
    text-decoration: underline;
    text-underline-offset: 0.2em;
}

.blog-content :deep(ul),
.blog-content :deep(ol) {
    margin: 0 0 1lh 1.2em;
}

.blog-content :deep(ul) {
    list-style: disc;
}

.blog-content :deep(ol) {
    list-style: decimal;
}

/* Figma: geel citaatkaartje (637×343) met auteur onderin */
.blog-content :deep(blockquote) {
    margin: 1lh 0 calc(1lh + 8px) -5px;
    border-radius: 16.6px;
    background: #ffc700;
    padding: clamp(28px, 2.9vw, 48px) clamp(24px, 3.3vw, 53px);
    color: #000;
    font-size: clamp(20px, 1.71vw, 28px);
    line-height: 1.36;
    letter-spacing: -0.03em;
}

.blog-content :deep(blockquote p) {
    margin: 0;
}

.blog-content :deep(.blog-quote-author) {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-top: clamp(24px, 2.6vw, 42px);
    font-size: 16px;
    line-height: 24px;
}

.blog-content :deep(.blog-quote-avatar) {
    width: 38px;
    height: 38px;
    border-radius: 9999px;
    object-fit: cover;
}

/* Figma: beeld is breder dan de tekstkolom (708 vs 645) */
.blog-content :deep(img:not(.blog-quote-avatar)) {
    display: block;
    width: min(110%, 100vw - 40px);
    max-width: none;
    height: auto;
    margin: calc(1lh + 8px) 0;
    border-radius: 16.6px;
}

.blog-content :deep(figure) {
    margin: 0;
}
</style>
