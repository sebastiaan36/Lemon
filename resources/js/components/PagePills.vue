<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import FloatingPill from '@/components/FloatingPill.vue';

/**
 * Zwevende pills uit de admin (menu "Pills"). Elke pill is zichtbaar vanaf de
 * gekozen begin- tot en met de eindsectie (data-pill-section in de pagina) en
 * verdwijnt zodra de footer in beeld komt. Overlappen pills, dan wint de eerste.
 */
type SitePill = {
    page: string;
    label: string;
    href: string;
    avatars: string[];
    startSection: string;
    endSection: string;
    desktopBottom: number;
};

const props = defineProps<{ page: string }>();

const sharedPills = computed(
    () => (usePage().props.floatingPills as SitePill[] | undefined) ?? [],
);
const pills = computed(() =>
    sharedPills.value.filter((pill) => pill.page === props.page),
);

/** Pill verschijnt als de beginsectie tot hier (deel van de viewport) is gescrold. */
const SHOW_LINE = 0.6;
/** Pill verdwijnt als de onderkant van de eindsectie boven deze lijn komt. */
const HIDE_LINE = 0.4;

const activeIndex = ref(-1);
let frame = 0;

/** Samengevoegde boven- en onderkant van alle zichtbare elementen met deze sectie-key. */
function sectionBounds(key: string): { top: number; bottom: number } | null {
    let top = Infinity;
    let bottom = -Infinity;

    document
        .querySelectorAll<HTMLElement>(`[data-pill-section="${key}"]`)
        .forEach((element) => {
            const rect = element.getBoundingClientRect();

            if (rect.height > 0) {
                top = Math.min(top, rect.top);
                bottom = Math.max(bottom, rect.bottom);
            }
        });

    return top === Infinity ? null : { top, bottom };
}

function footerInView(): boolean {
    const footer = document.querySelector('[data-site-footer]');

    return !!footer && footer.getBoundingClientRect().top < window.innerHeight;
}

function update(): void {
    frame = 0;

    if (footerInView()) {
        activeIndex.value = -1;

        return;
    }

    const vh = window.innerHeight;

    activeIndex.value = pills.value.findIndex((pill) => {
        const start = sectionBounds(pill.startSection);
        const end = sectionBounds(pill.endSection);

        return (
            !!start &&
            !!end &&
            start.top < vh * SHOW_LINE &&
            end.bottom > vh * HIDE_LINE
        );
    });
}

function scheduleUpdate(): void {
    if (!frame) {
        frame = requestAnimationFrame(update);
    }
}

onMounted(() => {
    update();
    window.addEventListener('scroll', scheduleUpdate, { passive: true });
    window.addEventListener('resize', scheduleUpdate, { passive: true });
});

onBeforeUnmount(() => {
    window.removeEventListener('scroll', scheduleUpdate);
    window.removeEventListener('resize', scheduleUpdate);

    if (frame) {
        cancelAnimationFrame(frame);
    }
});
</script>

<template>
    <div>
        <FloatingPill
            v-for="(pill, index) in pills"
            :key="`${pill.page}-${index}`"
            :label="pill.label"
            :href="pill.href"
            :avatars="pill.avatars"
            :desktop-bottom="pill.desktopBottom"
            :visibility="activeIndex === index ? 1 : 0"
            animated
        />
    </div>
</template>
