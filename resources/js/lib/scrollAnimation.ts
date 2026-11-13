export const FIGMA_DESKTOP_WIDTH = 1640;
export const FIGMA_DESKTOP_HEIGHT = 900;

/** Relatieve voortgang van `value` binnen [from, to], geklemd op 0–1. */
export function rangeProgress(value: number, from: number, to: number): number {
    return Math.max(0, Math.min((value - from) / (to - from), 1));
}

/** Ease-in-out (quadratisch). */
export function ease(t: number): number {
    return t < 0.5 ? 2 * t * t : -1 + (4 - 2 * t) * t;
}

export function lerp(from: number, to: number, t: number): number {
    return from + (to - from) * t;
}

/** Schaalt een Figma-x-waarde (op 1640 breed) naar de huidige viewportbreedte. */
export function scaleX(value: number, viewportWidth: number): string {
    return `${(value / FIGMA_DESKTOP_WIDTH) * viewportWidth}px`;
}

/** Schaalt een Figma-y-waarde (op 900 hoog) naar de huidige viewporthoogte. */
export function scaleY(value: number, viewportHeight: number): string {
    return `${(value / FIGMA_DESKTOP_HEIGHT) * viewportHeight}px`;
}

/** Detecteert video op extensie (inclusief HLS .m3u8 van Cloudflare Stream). */
export function isVideoUrl(url: string | null | undefined): boolean {
    return !!url && /\.(mp4|webm|ogg|mov|m3u8)(\?.*)?$/i.test(url);
}

/** Modulo die altijd een positieve waarde teruggeeft. */
export function wrap(value: number, total: number): number {
    return ((value % total) + total) % total;
}
