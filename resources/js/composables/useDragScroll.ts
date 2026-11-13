import { onBeforeUnmount, watch } from 'vue';
import type { Ref } from 'vue';

const DRAG_THRESHOLD = 5;

/**
 * Laat een horizontaal scrollende carousel met de muis slepen. Touch-swipen
 * werkt al native via overflow-x; dit voegt hetzelfde gedrag toe voor muizen
 * (bv. een smal browservenster op desktop). Scroll-snap staat tijdens het slepen
 * uit en springt bij loslaten naar de dichtstbijzijnde kaart.
 */
export function useDragScroll(target: Ref<HTMLElement | null>): void {
    let startX = 0;
    let startScroll = 0;
    let dragging = false;
    let moved = false;
    let element: HTMLElement | null = null;

    function onPointerDown(event: PointerEvent): void {
        if (event.pointerType !== 'mouse' || event.button !== 0 || !element) {
            return;
        }

        dragging = true;
        moved = false;
        startX = event.clientX;
        startScroll = element.scrollLeft;
    }

    function onPointerMove(event: PointerEvent): void {
        if (!dragging || !element) {
            return;
        }

        const delta = event.clientX - startX;

        if (!moved && Math.abs(delta) < DRAG_THRESHOLD) {
            return;
        }

        if (!moved) {
            moved = true;
            element.style.scrollSnapType = 'none';
            element.style.scrollBehavior = 'auto';
            element.style.cursor = 'grabbing';
            element.style.userSelect = 'none';
        }

        element.scrollLeft = startScroll - delta;
    }

    function onPointerUp(): void {
        if (!dragging || !element) {
            return;
        }

        dragging = false;

        if (moved) {
            const el = element;
            const restoreFrom = el.scrollLeft;
            el.style.scrollSnapType = '';
            el.style.scrollBehavior = '';
            el.style.cursor = '';
            el.style.userSelect = '';
            // Snap herstellen zonder sprong: zet de positie terug zodat de browser vloeiend naar de dichtstbijzijnde kaart snapt.
            el.scrollLeft = restoreFrom;
        }
    }

    // Na slepen geen klik op een kaart-link laten doorgaan.
    function onClickCapture(event: MouseEvent): void {
        if (moved) {
            event.preventDefault();
            event.stopPropagation();
            moved = false;
        }
    }

    function onDragStart(event: DragEvent): void {
        event.preventDefault();
    }

    function attach(el: HTMLElement): void {
        element = el;
        el.addEventListener('pointerdown', onPointerDown);
        el.addEventListener('click', onClickCapture, true);
        el.addEventListener('dragstart', onDragStart);
        window.addEventListener('pointermove', onPointerMove);
        window.addEventListener('pointerup', onPointerUp);
    }

    function detach(): void {
        if (!element) {
            return;
        }

        element.removeEventListener('pointerdown', onPointerDown);
        element.removeEventListener('click', onClickCapture, true);
        element.removeEventListener('dragstart', onDragStart);
        window.removeEventListener('pointermove', onPointerMove);
        window.removeEventListener('pointerup', onPointerUp);
        element = null;
    }

    watch(
        target,
        (el) => {
            detach();

            if (el) {
                attach(el);
            }
        },
        { immediate: true, flush: 'post' },
    );

    onBeforeUnmount(detach);
}
