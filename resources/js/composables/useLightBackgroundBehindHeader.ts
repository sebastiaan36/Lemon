import { onBeforeUnmount, onMounted, ref } from 'vue';
import type { Ref } from 'vue';

/** Punt rechtsboven waar menu-icoon en "Get in touch" staan. */
const SAMPLE_OFFSET_RIGHT = 80;
const SAMPLE_Y = 50;
/** Lichtheid (0–1) waarboven we de achtergrond als licht behandelen. */
const LIGHT_THRESHOLD = 0.45;
/** Achtergronden die doorzichtiger zijn dan dit (bv. een zwart/40-overlay) slaan we over. */
const MIN_OPACITY = 0.5;

/**
 * Lichtheid (0–1) en dekking van een computed background-color. Ondersteunt
 * rgb()/rgba() én de oklab()/oklch()-notatie die Tailwind v4 oplevert.
 */
function parseBackground(
    color: string,
): { lightness: number; alpha: number } | null {
    const rgb = color.match(
        /^rgba?\(([\d.]+),\s*([\d.]+),\s*([\d.]+)(?:,\s*([\d.]+))?/,
    );

    if (rgb) {
        return {
            lightness: luminance([
                Number(rgb[1]),
                Number(rgb[2]),
                Number(rgb[3]),
            ]),
            alpha: Number(rgb[4] ?? 1),
        };
    }

    const ok = color.match(
        /^okl(?:ab|ch)\(\s*([\d.]+)(%?)[^/)]*(?:\/\s*([\d.]+)(%?))?/,
    );

    if (ok) {
        const lightness = Number(ok[1]) / (ok[2] ? 100 : 1);
        const alpha =
            ok[3] === undefined ? 1 : Number(ok[3]) / (ok[4] ? 100 : 1);

        // OKLab-lichtheid is perceptueel; ** 3 benadert relatieve luminantie.
        return { lightness: lightness ** 3, alpha };
    }

    return null;
}

function luminance([r, g, b]: [number, number, number]): number {
    const channel = (value: number) => {
        const c = value / 255;

        return c <= 0.03928 ? c / 12.92 : ((c + 0.055) / 1.055) ** 2.4;
    };

    return 0.2126 * channel(r) + 0.7152 * channel(g) + 0.0722 * channel(b);
}

/**
 * Bepaalt of er een lichte achtergrond (geel, wit) achter de knoppen rechtsboven
 * in de header ligt, zodat de header daar donkere knoppen kan tonen en op zwart,
 * foto's en video's witte. Meet alleen bij scroll/resize, gebundeld per frame.
 */
export function useLightBackgroundBehindHeader(initial = true): Ref<boolean> {
    const isLight = ref(initial);
    let frame = 0;

    function measure(): void {
        frame = 0;
        const x = window.innerWidth - SAMPLE_OFFSET_RIGHT;

        for (const element of document.elementsFromPoint(x, SAMPLE_Y)) {
            if (element.closest('header')) {
                continue;
            }

            // Foto's en video's behandelen we als donker (witte knoppen).
            if (
                element instanceof HTMLImageElement ||
                element instanceof HTMLVideoElement
            ) {
                isLight.value = false;

                return;
            }

            // elementsFromPoint geeft alle lagen van boven naar onder (incl. ouders),
            // dus we nemen de eerste laag met een (vrijwel) dekkende achtergrond.
            const background = parseBackground(
                window.getComputedStyle(element).backgroundColor,
            );

            if (background && background.alpha >= MIN_OPACITY) {
                isLight.value = background.lightness > LIGHT_THRESHOLD;

                return;
            }
        }
    }

    function schedule(): void {
        if (!frame) {
            frame = requestAnimationFrame(measure);
        }
    }

    onMounted(() => {
        measure();
        window.addEventListener('scroll', schedule, { passive: true });
        window.addEventListener('resize', schedule, { passive: true });
    });

    onBeforeUnmount(() => {
        window.removeEventListener('scroll', schedule);
        window.removeEventListener('resize', schedule);

        if (frame) {
            cancelAnimationFrame(frame);
        }
    });

    return isLight;
}
