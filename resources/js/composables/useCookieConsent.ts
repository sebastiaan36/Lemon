import { readonly, ref } from 'vue';

/**
 * Cookie-toestemming van de bezoeker. Opgeslagen in de cookie `lemon_consent`
 * (12 maanden). Verhoog CONSENT_VERSION als de categorieën veranderen: dan
 * vragen we iedereen opnieuw om toestemming.
 */
export type ConsentChoices = {
    analytics: boolean;
    marketing: boolean;
};

type StoredConsent = ConsentChoices & { version: number; savedAt: string };

const COOKIE_NAME = 'lemon_consent';
const CONSENT_VERSION = 1;
const MAX_AGE_SECONDS = 60 * 60 * 24 * 365;

declare global {
    interface Window {
        dataLayer?: unknown[];
        gtag?: (...args: unknown[]) => void;
    }
}

function readStoredConsent(): StoredConsent | null {
    if (typeof document === 'undefined') {
        return null;
    }

    const raw = document.cookie
        .split('; ')
        .find((cookie) => cookie.startsWith(`${COOKIE_NAME}=`))
        ?.slice(COOKIE_NAME.length + 1);

    if (!raw) {
        return null;
    }

    try {
        const parsed = JSON.parse(decodeURIComponent(raw)) as StoredConsent;

        return parsed.version === CONSENT_VERSION ? parsed : null;
    } catch {
        return null;
    }
}

const stored = readStoredConsent();
const choices = ref<ConsentChoices | null>(
    stored
        ? { analytics: stored.analytics, marketing: stored.marketing }
        : null,
);
const isBannerOpen = ref(stored === null);

/** Geeft de keuze door aan Google Consent Mode (gtag staat klaar in app.blade.php). */
function updateGoogleConsent(consent: ConsentChoices): void {
    const state = (granted: boolean) => (granted ? 'granted' : 'denied');

    window.gtag?.('consent', 'update', {
        analytics_storage: state(consent.analytics),
        ad_storage: state(consent.marketing),
        ad_user_data: state(consent.marketing),
        ad_personalization: state(consent.marketing),
    });
    window.dataLayer?.push({ event: 'cookie_consent_update', ...consent });
}

function save(consent: ConsentChoices): void {
    const value: StoredConsent = {
        ...consent,
        version: CONSENT_VERSION,
        savedAt: new Date().toISOString(),
    };
    const secure = window.location.protocol === 'https:' ? '; Secure' : '';

    document.cookie = `${COOKIE_NAME}=${encodeURIComponent(JSON.stringify(value))}; Max-Age=${MAX_AGE_SECONDS}; Path=/; SameSite=Lax${secure}`;

    choices.value = consent;
    isBannerOpen.value = false;
    updateGoogleConsent(consent);
}

export function useCookieConsent() {
    return {
        choices: readonly(choices),
        isBannerOpen: readonly(isBannerOpen),
        acceptAll: () => save({ analytics: true, marketing: true }),
        acceptNecessaryOnly: () => save({ analytics: false, marketing: false }),
        savePreferences: (consent: ConsentChoices) => save(consent),
        openBanner: () => {
            isBannerOpen.value = true;
        },
    };
}
