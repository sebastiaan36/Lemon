export const SITE_NAME = 'Lemon Scented Tea';

/** Elke tabtitel als "Pagina — Lemon Scented Tea"; titels met de merknaam blijven zoals ze zijn. */
export function formatPageTitle(title: string | null | undefined): string {
    const trimmed = title?.trim();

    if (!trimmed) {
        return SITE_NAME;
    }

    return trimmed.includes(SITE_NAME) ? trimmed : `${trimmed} — ${SITE_NAME}`;
}
