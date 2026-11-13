import { computed, onBeforeUnmount, onMounted, ref, shallowRef } from 'vue';
import type { ComputedRef, Ref } from 'vue';

export type ElementBox = {
    top: number;
    height: number;
};

export type UseScrollLayoutReturn<K extends string> = {
    scrollY: Ref<number>;
    viewportWidth: Ref<number>;
    viewportHeight: Ref<number>;
    boxes: Ref<Partial<Record<K, ElementBox>>>;
    sectionProgress: (key: K) => ComputedRef<number>;
};

/**
 * Houdt scrollpositie en viewportmaten bij, en cachet offsetTop/offsetHeight
 * van de opgegeven elementen. Metingen gebeuren alleen bij mount, resize en
 * wanneer de layout verandert (ResizeObserver) — niet op elk scroll-event.
 * Scroll-updates worden via requestAnimationFrame gebundeld.
 */
export function useScrollLayout<K extends string>(
    elements: Record<K, Ref<HTMLElement | null>>,
    fallbackViewport: { width: number; height: number },
): UseScrollLayoutReturn<K> {
    const hasWindow = typeof window !== 'undefined';
    const scrollY = ref(0);
    const viewportWidth = ref(
        hasWindow ? window.innerWidth : fallbackViewport.width,
    );
    const viewportHeight = ref(
        hasWindow ? window.innerHeight : fallbackViewport.height,
    );
    const boxes = shallowRef<Partial<Record<K, ElementBox>>>({});
    const keys = Object.keys(elements) as K[];

    let scrollFrame: number | null = null;
    let layoutFrame: number | null = null;
    let resizeObserver: ResizeObserver | null = null;

    function measure(): void {
        const next: Partial<Record<K, ElementBox>> = {};

        for (const key of keys) {
            const el = elements[key].value;

            if (el) {
                next[key] = { top: el.offsetTop, height: el.offsetHeight };
            }
        }

        boxes.value = next;
    }

    function syncScroll(): void {
        scrollFrame = null;
        scrollY.value = window.scrollY;
    }

    function onScroll(): void {
        if (scrollFrame !== null) {
            return;
        }

        scrollFrame = requestAnimationFrame(syncScroll);
    }

    function syncLayout(): void {
        layoutFrame = null;
        viewportWidth.value = window.innerWidth;
        viewportHeight.value = window.innerHeight;
        measure();
        scrollY.value = window.scrollY;
    }

    function scheduleLayout(): void {
        if (layoutFrame !== null) {
            return;
        }

        layoutFrame = requestAnimationFrame(syncLayout);
    }

    onMounted(() => {
        syncLayout();
        window.addEventListener('scroll', onScroll, { passive: true });
        window.addEventListener('resize', scheduleLayout, { passive: true });

        if (typeof ResizeObserver !== 'undefined') {
            resizeObserver = new ResizeObserver(scheduleLayout);
            resizeObserver.observe(document.body);

            for (const key of keys) {
                const el = elements[key].value;

                if (el) {
                    resizeObserver.observe(el);
                }
            }
        }
    });

    onBeforeUnmount(() => {
        window.removeEventListener('scroll', onScroll);
        window.removeEventListener('resize', scheduleLayout);
        resizeObserver?.disconnect();
        resizeObserver = null;

        if (scrollFrame !== null) {
            cancelAnimationFrame(scrollFrame);
            scrollFrame = null;
        }

        if (layoutFrame !== null) {
            cancelAnimationFrame(layoutFrame);
            layoutFrame = null;
        }
    });

    /**
     * Voortgang (0–1) van het scrollen door een sectie: 0 wanneer de bovenkant
     * van de sectie de bovenkant van de viewport raakt, 1 wanneer de onderkant
     * de onderkant van de viewport raakt. Secties die niet hoger zijn dan de
     * viewport springen direct van 0 naar 1 (geen deling door nul).
     */
    function sectionProgress(key: K): ComputedRef<number> {
        return computed(() => {
            const box = boxes.value[key];

            if (!box) {
                return 0;
            }

            const scrolled = scrollY.value - box.top;
            const maxScroll = box.height - viewportHeight.value;

            if (maxScroll <= 0) {
                return scrolled >= 0 ? 1 : 0;
            }

            return Math.max(0, Math.min(scrolled / maxScroll, 1));
        });
    }

    return { scrollY, viewportWidth, viewportHeight, boxes, sectionProgress };
}
