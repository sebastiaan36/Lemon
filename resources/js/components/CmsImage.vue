<script setup lang="ts">
/**
 * Image from the CMS (Curator). The backend hands every page a
 * `mediaSources` map with the AVIF/WebP srcsets generated on upload; this
 * component looks its `src` up there and renders a <picture>, so the
 * browser downloads the smallest suitable file. Images without variants
 * (SVG, GIF, static assets) render as a plain <img>.
 *
 * - `sizes`: rendered width of the image, e.g. `(min-width: 1024px) 50vw, 100vw`.
 * - `priority`: for the hero/LCP image; loads eagerly with high priority.
 *   Everything else is lazy-loaded.
 *
 * The <picture> is `display: contents`, so classes on this component style
 * the <img> exactly like a plain image would. The <source> elements are
 * explicitly hidden: otherwise they become empty flex/grid items and pick
 * up the parent's `gap`, shifting the image sideways.
 */
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

type MediaSource = {
    avif?: string;
    webp?: string;
    width?: number;
    height?: number;
};

defineOptions({ inheritAttrs: false });

const props = withDefaults(
    defineProps<{
        src?: string | null;
        sizes?: string;
        priority?: boolean;
    }>(),
    { src: null, sizes: '100vw', priority: false },
);

const page = usePage<{ mediaSources?: Record<string, MediaSource> }>();

const source = computed<MediaSource | null>(() =>
    props.src ? (page.props.mediaSources?.[props.src] ?? null) : null,
);
</script>

<template>
    <picture class="contents">
        <source
            v-if="source?.avif"
            :srcset="source.avif"
            :sizes="sizes"
            type="image/avif"
            class="hidden"
        />
        <source
            v-if="source?.webp"
            :srcset="source.webp"
            :sizes="sizes"
            type="image/webp"
            class="hidden"
        />
        <img
            :src="src ?? undefined"
            :width="source?.width"
            :height="source?.height"
            :loading="priority ? 'eager' : 'lazy'"
            :fetchpriority="priority ? 'high' : undefined"
            decoding="async"
            v-bind="$attrs"
        />
    </picture>
</template>
