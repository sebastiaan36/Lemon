export type SiteNavItem = {
    label: string;
    href: string;
};

/** Main navigation, shared by the header menu, the footer and the case pages. */
export const SITE_NAV_ITEMS: SiteNavItem[] = [
    // Tijdelijk voor de interne review (28-11): wisselen tussen beide homepages.
    { label: 'Home V2', href: '/home-v2' },
    { label: 'About', href: '/about' },
    { label: 'Cases', href: '/cases' },
    { label: 'CxByEx', href: '/cxbyex' },
    { label: 'Industries', href: '/industries' },
    { label: 'Blog', href: '/blog' },
    { label: 'Jobs', href: '/jobs' },
    { label: 'Contact', href: '/contact' },
];

/**
 * Turn a human-formatted phone number into a dialable tel: href.
 * "+31 (0) 20 606 3580" → "tel:+31206063580", "0031 6 55" → "tel:+31655".
 */
export function telHref(phone: string): string {
    const digits = phone
        .replace(/\(0\)/g, '')
        .replace(/[^\d+]/g, '')
        .replace(/^00/, '+');

    return `tel:${digits}`;
}
