import { onBeforeUnmount, onMounted, ref } from 'vue';
import type { Ref } from 'vue';

/** Punt rechtsboven waar menu-icoon en "Get in touch" staan. */
const SAMPLE_OFFSET_RIGHT = 80;
const SAMPLE_Y = 50;
/** Lichtheid (0–1) waarboven we de achtergrond als licht behandelen. */
const LIGHT_THRESHOLD = 0.45;
/** Achtergronden die doorzichtiger zijn dan dit (bv. een zwart/40-overlay) slaan we over. */
const MIN_OPACITY = 0.5;
/** Een laag die minstens dit deel van de schermbreedte vult, telt als sectie-achtergrond. */
const FULL_BLEED_RATIO = 0.9;

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

type BackgroundSample = { color: string; isLight: boolean; isMedia: boolean };

/**
 * De eerste (vrijwel) dekkende achtergrond op dit punt van het scherm. Foto's
 * en video's tellen als zwart. Met `ignore` sla je bv. het element zelf over
 * dat over de achtergrond zweeft. `null` als er geen dekkende laag is.
 * `sectionOnly`: kijk door losse kaarten en foto's heen naar de sectiekleur.
 */
export function backgroundAt(
    x: number,
    y: number,
    ignore: (element: Element) => boolean = () => false,
    { sectionOnly = false }: { sectionOnly?: boolean } = {},
): BackgroundSample | null {
    const dark: BackgroundSample = {
        color: 'rgb(0, 0, 0)',
        isLight: false,
        isMedia: true,
    };
    let sawMedia = false;

    for (const element of document.elementsFromPoint(x, y)) {
        if (ignore(element)) {
            continue;
        }

        // Met sectionOnly tellen losse kaarten en foto's (smaller dan ~90% van
        // het scherm) niet mee en nemen we de kleur van de sectie eronder.
        const isNarrow =
            sectionOnly &&
            element.getBoundingClientRect().width <
                window.innerWidth * FULL_BLEED_RATIO;
        const isMedia =
            element instanceof HTMLImageElement ||
            element instanceof HTMLVideoElement;

        if (isMedia) {
            if (!isNarrow) {
                return dark;
            }

            sawMedia = true;
            continue;
        }

        if (isNarrow) {
            continue;
        }

        // elementsFromPoint geeft alle lagen van boven naar onder (incl. ouders),
        // dus we nemen de eerste laag met een (vrijwel) dekkende achtergrond.
        const color = window.getComputedStyle(element).backgroundColor;
        const background = parseBackground(color);

        if (background && background.alpha >= MIN_OPACITY) {
            return {
                color,
                isLight: background.lightness > LIGHT_THRESHOLD,
                isMedia: false,
            };
        }
    }

    return sawMedia ? dark : null;
}

/** Is de achtergrond op dit punt licht (geel, wit)? Zie backgroundAt(). */
export function isLightBackgroundAt(
    x: number,
    y: number,
    ignore: (element: Element) => boolean = () => false,
): boolean | null {
    return backgroundAt(x, y, ignore)?.isLight ?? null;
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
        const light = isLightBackgroundAt(
            window.innerWidth - SAMPLE_OFFSET_RIGHT,
            SAMPLE_Y,
            (element) => element.closest('header') !== null,
        );

        if (light !== null) {
            isLight.value = light;
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
