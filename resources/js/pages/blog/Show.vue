<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import SiteFooter from '@/components/SiteFooter.vue'
import SiteHeader from '@/components/SiteHeader.vue'

type Blog = {
    title: string
    headerPhoto: string | null
    content: string | null
    publishedAt: string | null
    seoTitle: string | null
    metaDescription: string | null
}

const props = defineProps<{
    blog: Blog
}>()
</script>

<template>
    <Head>
        <title>{{ blog.seoTitle || `${blog.title} — Lemon Scented Tea` }}</title>
        <meta v-if="blog.metaDescription" name="description" :content="blog.metaDescription" />
    </Head>

    <div class="min-h-screen bg-[#ffc700] text-black">
        <SiteHeader />

        <article>
            <section class="relative flex min-h-[85vh] items-end overflow-hidden px-[24px] pb-[70px] md:px-[59px]">
                <img v-if="blog.headerPhoto" :src="blog.headerPhoto" :alt="blog.title" class="absolute inset-0 h-full w-full object-cover" />
                <div class="absolute inset-0 bg-black/60" />

                <div class="relative max-w-[1120px]">
                    <Link
                        href="/blog"
                        class="mb-8 inline-flex h-[46px] items-center rounded-[30px] border border-white/40 px-5 text-[15px] tracking-[-0.45px] text-white transition-colors hover:border-[#ffc700] hover:text-[#ffc700]"
                        style="font-family: 'Avenir', system-ui, sans-serif;"
                    >
                        Terug naar blog
                    </Link>

                    <p
                        v-if="blog.publishedAt"
                        class="mb-4 text-[16px] tracking-[-0.48px] text-[#ffc700]"
                        style="font-family: 'Avenir', system-ui, sans-serif;"
                    >
                        {{ blog.publishedAt }}
                    </p>

                    <h1
                        class="text-[58px] font-black leading-none tracking-[-2.5px] text-[#ffc700] italic md:text-[96px] lg:text-[130px]"
                        style="font-family: 'Avenir', system-ui, sans-serif;"
                    >
                        {{ blog.title }}
                    </h1>
                </div>
            </section>

            <section class="px-[24px] py-[85px] md:px-[59px]">
                <div
                    v-if="blog.content"
                    class="blog-content mx-auto max-w-[900px] text-[21px] leading-[1.65] tracking-[-0.6px] text-black/80"
                    style="font-family: 'Avenir', system-ui, sans-serif;"
                    v-html="blog.content"
                />
            </section>
        </article>

        <SiteFooter />
    </div>
</template>

<style scoped>
.blog-content :deep(h1),
.blog-content :deep(h2),
.blog-content :deep(h3) {
    margin: 3rem 0 1.2rem;
    color: #000;
    font-family: 'Avenir', system-ui, sans-serif;
    font-style: oblique;
    font-weight: 900;
    line-height: 1.05;
    letter-spacing: -0.04em;
}

.blog-content :deep(h1) {
    font-size: clamp(3.5rem, 8vw, 6.5rem);
}

.blog-content :deep(h2) {
    font-size: clamp(2.6rem, 5vw, 4.6rem);
}

.blog-content :deep(h3) {
    font-size: clamp(2rem, 3.5vw, 3.2rem);
}

.blog-content :deep(p) {
    margin-bottom: 1.35rem;
}

.blog-content :deep(strong),
.blog-content :deep(b) {
    color: #000;
    font-weight: 900;
}

.blog-content :deep(a) {
    color: #000;
    text-decoration: underline;
    text-underline-offset: 0.2em;
}

.blog-content :deep(ul),
.blog-content :deep(ol) {
    margin: 1.35rem 0 1.35rem 1.5rem;
}

.blog-content :deep(ul) {
    list-style: disc;
}

.blog-content :deep(ol) {
    list-style: decimal;
}

.blog-content :deep(li) {
    margin-bottom: 0.45rem;
}

.blog-content :deep(blockquote) {
    margin: 2.5rem 0;
    border-left: 4px solid #000;
    padding-left: 1.5rem;
    color: #000;
    font-size: 1.25em;
    font-style: oblique;
}

.blog-content :deep(img) {
    margin: 3rem 0;
    max-width: 100%;
    border-radius: 16px;
}
</style>
