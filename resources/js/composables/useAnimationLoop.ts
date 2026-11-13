import { onBeforeUnmount, onMounted, ref } from 'vue';
import type { Ref } from 'vue';

export type UseAnimationLoopReturn = {
    prefersReducedMotion: Ref<boolean>;
};

/**
 * Draait `tick(dt)` elk frame, maar alleen zolang `target` in beeld is en het
 * tabblad zichtbaar is. Bij `prefers-reduced-motion: reduce` draait de loop
 * helemaal niet. `dt` is in seconden; na een pauze begint dt weer op 0 zodat
 * er geen sprong ontstaat.
 */
export function useAnimationLoop(
    target: Ref<HTMLElement | null>,
    tick: (dt: number) => void,
): UseAnimationLoopReturn {
    const prefersReducedMotion = ref(false);

    let frame: number | null = null;
    let lastTime = 0;
    let isIntersecting = false;
    let observer: IntersectionObserver | null = null;
    let motionQuery: MediaQueryList | null = null;

    function loop(timestamp: number): void {
        const dt = lastTime ? (timestamp - lastTime) / 1000 : 0;
        lastTime = timestamp;
        tick(dt);
        frame = requestAnimationFrame(loop);
    }

    function stop(): void {
        if (frame !== null) {
            cancelAnimationFrame(frame);
            frame = null;
        }

        lastTime = 0;
    }

    function update(): void {
        const shouldRun =
            isIntersecting && !document.hidden && !prefersReducedMotion.value;

        if (shouldRun && frame === null) {
            lastTime = 0;
            frame = requestAnimationFrame(loop);
        } else if (!shouldRun) {
            stop();
        }
    }

    function onMotionChange(event: MediaQueryListEvent): void {
        prefersReducedMotion.value = event.matches;
        update();
    }

    onMounted(() => {
        motionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
        prefersReducedMotion.value = motionQuery.matches;
        motionQuery.addEventListener('change', onMotionChange);
        document.addEventListener('visibilitychange', update);

        if (typeof IntersectionObserver === 'undefined' || !target.value) {
            isIntersecting = true;
            update();

            return;
        }

        observer = new IntersectionObserver((entries) => {
            isIntersecting = entries.some((entry) => entry.isIntersecting);
            update();
        });
        observer.observe(target.value);
    });

    onBeforeUnmount(() => {
        stop();
        observer?.disconnect();
        observer = null;
        motionQuery?.removeEventListener('change', onMotionChange);
        motionQuery = null;
        document.removeEventListener('visibilitychange', update);
    });

    return { prefersReducedMotion };
}
