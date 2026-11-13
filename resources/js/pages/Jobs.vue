<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { nextTick, onMounted, onUnmounted, ref } from 'vue';
import CmsImage from '@/components/CmsImage.vue';
import SiteFooter from '@/components/SiteFooter.vue';
import SiteHeader from '@/components/SiteHeader.vue';

type Job = {
    slug: string;
    title: string;
    jobTitle: string;
    shortDescription: string | null;
    body: string | null;
    tags: string[];
    applyButtonText: string;
    applyEmail: string | null;
    applyContactName: string | null;
    applyContactPhoto: string | null;
};

const props = defineProps<{
    jobs: Job[];
    seoTitle?: string | null;
    metaDescription?: string | null;
    heroBgImage?: string | null;
    heroTitle?: string | null;
    heroSubtitle?: string | null;
}>();

const activeSlug = ref(props.jobs[0]?.slug ?? null);
const jobRefs = ref<Record<string, HTMLElement>>({});

function jobExists(slug: string): boolean {
    return props.jobs.some((job) => job.slug === slug);
}

function slugFromHash(): string | null {
    const slug = decodeURIComponent(window.location.hash.replace(/^#/, ''));

    return slug && jobExists(slug) ? slug : null;
}

function scrollToJob(slug: string) {
    activeSlug.value = slug;

    if (window.location.hash !== `#${slug}`) {
        // replaceState met de bestaande state, zodat Inertia's history-state intact blijft.
        window.history.replaceState(window.history.state, '', `#${slug}`);
    }
}

function setJobRef(el: Element | null, slug: string) {
    if (el instanceof HTMLElement) {
        jobRefs.value[slug] = el;
    }
}

async function activateFromHash(): Promise<void> {
    const slug = slugFromHash();

    if (!slug) {
        return;
    }

    activeSlug.value = slug;
    await nextTick();
    jobRefs.value[slug]?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function onHashChange(): void {
    void activateFromHash();
}

let observer: IntersectionObserver | null = null;

onMounted(async () => {
    await activateFromHash();
    window.addEventListener('hashchange', onHashChange);

    observer = new IntersectionObserver(
        (entries) => {
            for (const entry of entries) {
                if (entry.isIntersecting) {
                    const slug = (entry.target as HTMLElement).dataset.jobSlug;

                    if (slug) {
                        activeSlug.value = slug;
                    }
                }
            }
        },
        { threshold: 0.3 },
    );

    Object.values(jobRefs.value).forEach((el) => observer?.observe(el));
});

onUnmounted(() => {
    observer?.disconnect();
    window.removeEventListener('hashchange', onHashChange);
});

function scrollToContent() {
    document
        .getElementById('jobs-content')
        ?.scrollIntoView({ behavior: 'smooth' });
}
</script>

<template>
    <Head>
        <title>{{ seoTitle || 'Jobs — Lemon Scented Tea' }}</title>
        <meta
            v-if="metaDescription"
            name="description"
            :content="metaDescription"
        />
    </Head>

    <div class="bg-black">
        <SiteHeader />

        <!-- ============ HERO ============ -->
        <section
            id="jobs-hero"
            class="relative flex h-svh items-center justify-center overflow-hidden md:h-screen"
        >
            <CmsImage
                priority
                v-if="heroBgImage"
                :src="heroBgImage"
                alt=""
                class="absolute inset-0 h-full w-full object-cover"
            />
            <div class="absolute inset-0 bg-black/60" />
            <div class="relative text-center">
                <h1
                    class="font-avenir text-[100px] leading-none tracking-[-4.5px] text-[#ffc700] lg:text-[150px]"
                    style="font-weight: 900; font-style: oblique"
                >
                    {{ heroTitle || 'Join Lemon' }}
                </h1>
                <p
                    v-if="heroSubtitle"
                    class="mt-6 font-avenir text-[22px] tracking-[-0.66px] text-white"
                >
                    {{ heroSubtitle }}
                </p>
            </div>

            <!-- Scroll indicator -->
            <button
                class="absolute bottom-10 left-1/2 -translate-x-1/2"
                @click="scrollToContent"
            >
                <div
                    class="flex h-[44px] w-[44px] items-center justify-center rounded-full border border-[#ffc700] transition-colors hover:bg-[#ffc700]/20"
                >
                    <svg
                        class="h-4 w-4 text-[#ffc700]"
                        viewBox="0 0 16 16"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.5"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <path d="M8 3v10M3 9l5 5 5-5" />
                    </svg>
                </div>
            </button>
        </section>

        <!-- ============ NO JOBS ============ -->
        <section
            v-if="!jobs.length"
            id="jobs-content"
            class="px-[59px] py-[120px] text-center"
        >
            <p class="font-avenir text-[24px] text-white/50">
                Er zijn momenteel geen openstaande vacatures.
            </p>
        </section>

        <!-- ============ JOBS LAYOUT ============ -->
        <section
            v-else
            id="jobs-content"
            class="flex min-h-screen flex-col gap-12 px-5 py-[64px] md:flex-row md:gap-0 md:px-[59px] md:py-[80px] lg:py-[100px]"
        >
            <!-- LEFT: Sticky sidebar -->
            <aside class="w-full shrink-0 md:w-[300px] lg:w-[480px]">
                <div class="md:sticky md:top-[100px]">
                    <p
                        class="mb-6 pl-[34px] font-avenir text-[16px] tracking-[-0.5px] text-[#ffc700]"
                    >
                        Available jobs at Lemon
                    </p>

                    <nav class="flex flex-col">
                        <button
                            v-for="job in jobs"
                            :key="job.slug"
                            class="flex items-center text-left font-avenir text-[22px] leading-[1.8] tracking-[-0.66px] transition-opacity"
                            :class="
                                activeSlug === job.slug
                                    ? 'text-white opacity-100'
                                    : 'text-white opacity-30'
                            "
                            style="font-weight: 900; font-style: oblique"
                            @click="scrollToJob(job.slug)"
                        >
                            <span class="flex w-[34px] shrink-0 items-center">
                                <svg
                                    v-if="activeSlug === job.slug"
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="22"
                                    height="22"
                                    viewBox="0 0 23 23"
                                    fill="none"
                                    class="-translate-y-[5px]"
                                >
                                    <path
                                        d="M8.18557 21.7141L21.7143 21.7141L21.7143 7.5796"
                                        stroke="#ffc700"
                                        stroke-width="2.01919"
                                    />
                                    <path
                                        d="M21.3099 21.3098L0.713889 0.713818"
                                        stroke="#ffc700"
                                        stroke-width="2.01919"
                                    />
                                </svg>
                            </span>
                            <span>{{ job.title }}</span>
                        </button>
                    </nav>

                    <!-- Apply button for active job -->
                    <template v-for="job in jobs" :key="job.slug">
                        <a
                            v-if="activeSlug === job.slug && job.applyEmail"
                            :href="`mailto:${job.applyEmail}`"
                            class="mt-8 ml-[34px] flex h-[102px] items-center justify-between rounded-[15px] bg-[#ffc700] px-6 transition-opacity hover:opacity-90"
                        >
                            <span
                                class="font-avenir text-[24px] leading-[1.2] tracking-[-0.72px] text-black"
                                style="font-weight: 900; font-style: oblique"
                            >
                                {{ job.applyButtonText || 'Want this job?' }}
                            </span>
                            <span
                                v-if="job.applyContactName"
                                class="flex shrink-0 items-center gap-2 rounded-[30px] border border-black px-4 py-2 font-avenir text-[14px] tracking-[-0.42px] text-black"
                            >
                                <CmsImage
                                    sizes="28px"
                                    v-if="job.applyContactPhoto"
                                    :src="job.applyContactPhoto"
                                    class="h-[28px] w-[28px] rounded-full object-cover"
                                    :alt="`Foto van ${job.applyContactName}`"
                                />
                                {{ job.applyContactName }}
                            </span>
                        </a>
                    </template>
                </div>
            </aside>

            <!-- RIGHT: Job content -->
            <div class="min-w-0 flex-1 md:pl-[48px] lg:pl-[80px]">
                <article
                    v-for="job in jobs"
                    v-show="activeSlug === job.slug"
                    :key="job.slug"
                    :id="job.slug"
                    :ref="(el) => setJobRef(el as Element | null, job.slug)"
                    :data-job-slug="job.slug"
                >
                    <!-- Job title -->
                    <h2
                        class="font-avenir text-[34px] leading-[1.1] tracking-[-0.03em] text-white md:text-[46px] lg:text-[56px]"
                        style="font-weight: 900; font-style: oblique"
                    >
                        {{ job.jobTitle }}
                    </h2>

                    <!-- Short description -->
                    <p
                        v-if="job.shortDescription"
                        class="mt-6 font-avenir text-[18px] leading-[1.5] tracking-[-0.02em] text-white md:text-[22px]"
                    >
                        {{ job.shortDescription }}
                    </p>

                    <!-- Tags -->
                    <div
                        v-if="job.tags.length"
                        class="mt-6 flex flex-wrap gap-2"
                    >
                        <span
                            v-for="tag in job.tags"
                            :key="tag"
                            class="flex h-[36px] items-center rounded-full border border-white px-4 font-avenir text-[14px] tracking-[-0.4px] text-white"
                        >
                            {{ tag }}
                        </span>
                    </div>

                    <!-- Body -->
                    <div
                        v-if="job.body"
                        class="job-body mt-10 font-avenir text-[18px] leading-[1.7] tracking-[-0.54px] text-white/70"
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
