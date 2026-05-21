<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { onMounted, onUnmounted, ref } from 'vue'
import SiteFooter from '@/components/SiteFooter.vue'
import SiteHeader from '@/components/SiteHeader.vue'

type Job = {
    slug: string
    title: string
    jobTitle: string
    shortDescription: string | null
    body: string | null
    tags: string[]
    applyButtonText: string
    applyEmail: string | null
    applyContactName: string | null
    applyContactPhoto: string | null
}

const props = defineProps<{
    jobs: Job[]
    seoTitle?: string | null
    metaDescription?: string | null
    heroBgImage?: string | null
    heroTitle?: string | null
    heroSubtitle?: string | null
}>()

const activeSlug = ref(props.jobs[0]?.slug ?? null)
const jobRefs = ref<Record<string, HTMLElement>>({})

function scrollToJob(slug: string) {
    activeSlug.value = slug
    jobRefs.value[slug]?.scrollIntoView({ behavior: 'smooth', block: 'start' })
}

function setJobRef(el: HTMLElement | null, slug: string) {
    if (el) jobRefs.value[slug] = el
}

let observer: IntersectionObserver | null = null

onMounted(() => {
    observer = new IntersectionObserver(
        (entries) => {
            for (const entry of entries) {
                if (entry.isIntersecting) {
                    const slug = (entry.target as HTMLElement).dataset.jobSlug
                    if (slug) activeSlug.value = slug
                }
            }
        },
        { threshold: 0.3 },
    )

    Object.values(jobRefs.value).forEach((el) => observer?.observe(el))
})

onUnmounted(() => observer?.disconnect())

function scrollToContent() {
    document.getElementById('jobs-content')?.scrollIntoView({ behavior: 'smooth' })
}

</script>

<template>
    <Head>
        <title>{{ seoTitle || 'Jobs — Lemon Scented Tea' }}</title>
        <meta v-if="metaDescription" name="description" :content="metaDescription" />
    </Head>

    <div class="bg-black">
        <SiteHeader />

        <!-- ============ HERO ============ -->
        <section id="jobs-hero" class="relative flex h-screen items-center justify-center overflow-hidden">
            <img v-if="heroBgImage" :src="heroBgImage" alt="" class="absolute inset-0 h-full w-full object-cover" />
            <div class="absolute inset-0 bg-black/60" />
            <div class="relative text-center">
                <h1
                    class="text-[100px] leading-none tracking-[-4.5px] text-[#ffc700] lg:text-[150px]"
                    style="font-family: 'Avenir', system-ui, sans-serif; font-weight: 900; font-style: oblique;"
                >
                    {{ heroTitle || 'Join Lemon' }}
                </h1>
                <p
                    v-if="heroSubtitle"
                    class="mt-6 text-[22px] tracking-[-0.66px] text-white"
                    style="font-family: 'Avenir', system-ui, sans-serif;"
                >
                    {{ heroSubtitle }}
                </p>
            </div>

            <!-- Scroll indicator -->
            <button
                class="absolute bottom-10 left-1/2 -translate-x-1/2"
                @click="scrollToContent"
            >
                <div class="flex h-[44px] w-[44px] items-center justify-center rounded-full border border-[#ffc700] transition-colors hover:bg-[#ffc700]/20">
                    <svg class="h-4 w-4 text-[#ffc700]" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M8 3v10M3 9l5 5 5-5" />
                    </svg>
                </div>
            </button>
        </section>

        <!-- ============ NO JOBS ============ -->
        <section v-if="!jobs.length" id="jobs-content" class="px-[59px] py-[120px] text-center">
            <p class="text-[24px] text-white/50" style="font-family: 'Avenir', system-ui, sans-serif;">
                Er zijn momenteel geen openstaande vacatures.
            </p>
        </section>

        <!-- ============ JOBS LAYOUT ============ -->
        <section v-else id="jobs-content" class="flex min-h-screen px-[59px] py-[80px] lg:py-[100px]">

            <!-- LEFT: Sticky sidebar -->
            <aside class="w-[340px] shrink-0 lg:w-[480px]">
                <div class="sticky top-[100px]">
                    <p
                        class="mb-6 text-[16px] tracking-[-0.5px] text-[#ffc700]"
                        style="font-family: 'Avenir', system-ui, sans-serif;"
                    >
                        Available jobs at Lemon
                    </p>

                    <nav class="flex flex-col">
                        <button
                            v-for="job in jobs"
                            :key="job.slug"
                            class="flex items-center gap-2 text-left text-[22px] leading-[1.8] tracking-[-0.66px] transition-opacity"
                            :class="activeSlug === job.slug ? 'text-white opacity-100' : 'text-white opacity-30'"
                            style="font-family: 'Avenir', system-ui, sans-serif; font-weight: 900; font-style: oblique;"
                            @click="scrollToJob(job.slug)"
                        >
                            <svg v-if="activeSlug === job.slug" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 23 23" fill="none" class="shrink-0">
                                <path d="M8.18557 21.7141L21.7143 21.7141L21.7143 7.5796" stroke="white" stroke-width="2.01919"/>
                                <path d="M21.3099 21.3098L0.713889 0.713818" stroke="white" stroke-width="2.01919"/>
                            </svg>
                            <span v-else class="w-[18px] shrink-0" />
                            {{ job.title }}
                        </button>
                    </nav>

                    <!-- Apply button for active job -->
                    <template v-for="job in jobs" :key="job.slug">
                        <a
                            v-if="activeSlug === job.slug && job.applyEmail"
                            :href="`mailto:${job.applyEmail}`"
                            class="mt-8 flex h-[102px] w-[90%] items-center justify-between rounded-[15px] bg-[#ffc700] px-6 transition-opacity hover:opacity-90"
                        >
                            <span
                                class="text-[24px] leading-[1.2] tracking-[-0.72px] text-black"
                                style="font-family: 'Avenir', system-ui, sans-serif; font-weight: 900; font-style: oblique;"
                            >
                                {{ job.applyButtonText || 'Want this job?' }}
                            </span>
                            <span
                                v-if="job.applyContactName"
                                class="flex shrink-0 items-center gap-2 rounded-[30px] border border-black px-4 py-2 text-[14px] tracking-[-0.42px] text-black"
                                style="font-family: 'Avenir', system-ui, sans-serif;"
                            >
                                <img
                                    v-if="job.applyContactPhoto"
                                    :src="job.applyContactPhoto"
                                    class="h-[28px] w-[28px] rounded-full object-cover"
                                    alt=""
                                />
                                {{ job.applyContactName }}
                            </span>
                        </a>
                    </template>
                </div>
            </aside>

            <!-- RIGHT: Job content -->
            <div class="min-w-0 flex-1 pl-[60px] lg:pl-[80px]">
                <article
                    v-for="(job, i) in jobs"
                    :key="job.slug"
                    :id="job.slug"
                    :ref="(el) => setJobRef(el as HTMLElement, job.slug)"
                    :data-job-slug="job.slug"
                    :class="i > 0 ? 'mt-[120px]' : ''"
                >
                    <!-- Job title -->
                    <h2
                        class="text-[46px] leading-[1.1] tracking-[-1.5px] text-white lg:text-[56px]"
                        style="font-family: 'Avenir', system-ui, sans-serif; font-weight: 900; font-style: oblique;"
                    >
                        {{ job.jobTitle }}
                    </h2>

                    <!-- Short description -->
                    <p
                        v-if="job.shortDescription"
                        class="mt-6 text-[22px] leading-[1.5] tracking-[-0.66px] text-white"
                        style="font-family: 'Avenir', system-ui, sans-serif;"
                    >
                        {{ job.shortDescription }}
                    </p>

                    <!-- Tags -->
                    <div v-if="job.tags.length" class="mt-6 flex flex-wrap gap-2">
                        <span
                            v-for="tag in job.tags"
                            :key="tag"
                            class="flex h-[36px] items-center rounded-full border border-white px-4 text-[14px] tracking-[-0.4px] text-white"
                            style="font-family: 'Avenir', system-ui, sans-serif;"
                        >
                            {{ tag }}
                        </span>
                    </div>

                    <!-- Body -->
                    <div
                        v-if="job.body"
                        class="job-body mt-10 text-[18px] leading-[1.7] tracking-[-0.54px] text-white/70"
                        style="font-family: 'Avenir', system-ui, sans-serif;"
                        v-html="job.body"
                    />
                </article>
            </div>
        </section>

        <SiteFooter />
    </div>
</template>

<style scoped>
.job-body :deep(p) {
    margin-bottom: 0.75rem;
}
.job-body :deep(p:empty) {
    display: none;
}
.job-body :deep(ul),
.job-body :deep(ol) {
    margin-left: 1.5rem;
    margin-bottom: 0.75rem;
    list-style: disc;
}
.job-body :deep(li) {
    margin-bottom: 0;
}
.job-body :deep(li p) {
    margin-bottom: 0;
    padding-bottom: 0;
}
.job-body :deep(strong),
.job-body :deep(b) {
    color: #ffc700;
    font-weight: 800;
}
</style>
