<script setup lang="ts">
import { onMounted, ref, watch } from 'vue';
import { useCookieConsent } from '@/composables/useCookieConsent';

/**
 * Cookiebanner linksonder (in de stijl van de branche-pill: donker glas met blur).
 * Noodzakelijke cookies staan altijd aan; analytics en marketing pas na toestemming.
 */
const {
    choices,
    isBannerOpen,
    acceptAll,
    acceptNecessaryOnly,
    savePreferences,
} = useCookieConsent();

// Alleen in de browser renderen: de keuze staat in een cookie die SSR niet ziet.
const isMounted = ref(false);
onMounted(() => {
    isMounted.value = true;
});

const showPreferences = ref(false);
const analytics = ref(choices.value?.analytics ?? false);
const marketing = ref(choices.value?.marketing ?? false);

// Bij heropenen via "Cookie settings" de eerder gemaakte keuze tonen.
watch(isBannerOpen, (open) => {
    if (open) {
        analytics.value = choices.value?.analytics ?? false;
        marketing.value = choices.value?.marketing ?? false;
        showPreferences.value = choices.value !== null;
    }
});

const categories = [
    {
        key: 'necessary',
        label: 'Necessary',
        description: 'Keep the site secure and working. Always on.',
    },
    {
        key: 'analytics',
        label: 'Analytics',
        description: 'Help us understand how visitors use the site.',
    },
    {
        key: 'marketing',
        label: 'Marketing',
        description: 'Used to show relevant ads on other platforms.',
    },
] as const;

function isChecked(key: (typeof categories)[number]['key']): boolean {
    if (key === 'necessary') {
        return true;
    }

    return key === 'analytics' ? analytics.value : marketing.value;
}

function toggle(key: (typeof categories)[number]['key']): void {
    if (key === 'analytics') {
        analytics.value = !analytics.value;
    } else if (key === 'marketing') {
        marketing.value = !marketing.value;
    }
}
</script>

<template>
    <Teleport to="body">
        <Transition name="lst-panel">
            <section
                v-if="isMounted && isBannerOpen"
                role="dialog"
                aria-labelledby="lst-privacy-title"
                aria-describedby="lst-privacy-text"
                class="fixed right-4 bottom-4 left-4 z-[70] max-h-[calc(100svh-2rem)] overflow-y-auto rounded-[24px] border-[0.6px] border-white/10 bg-black/70 p-6 font-avenir text-white shadow-[0_20px_60px_rgba(0,0,0,0.35)] backdrop-blur-[12px] md:right-auto md:bottom-6 md:left-6 md:w-[420px] md:p-7"
            >
                <h2
                    id="lst-privacy-title"
                    class="text-[24px] leading-none font-black tracking-[-0.03em] text-[#ffc700] italic"
                >
                    Cookies
                </h2>
                <p
                    id="lst-privacy-text"
                    class="mt-3 text-[15px] leading-[1.45] tracking-[-0.01em] text-white/80"
                >
                    We use cookies to keep this site running smoothly and, with
                    your permission, to see how it's used and improve it.
                </p>

                <ul v-if="showPreferences" class="mt-5 space-y-3">
                    <li
                        v-for="category in categories"
                        :key="category.key"
                        class="flex items-start justify-between gap-4 rounded-[14px] border border-white/10 px-4 py-3"
                    >
                        <div>
                            <p class="text-[15px] font-extrabold">
                                {{ category.label }}
                            </p>
                            <p
                                class="mt-0.5 text-[13px] leading-[1.4] text-white/60"
                            >
                                {{ category.description }}
                            </p>
                        </div>
                        <button
                            type="button"
                            role="switch"
                            :aria-checked="isChecked(category.key)"
                            :aria-label="category.label"
                            :disabled="category.key === 'necessary'"
                            class="relative mt-0.5 h-[26px] w-[46px] shrink-0 rounded-full transition-colors duration-200 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#ffc700] disabled:cursor-not-allowed disabled:opacity-60"
                            :class="
                                isChecked(category.key)
                                    ? 'bg-[#ffc700]'
                                    : 'bg-white/20'
                            "
                            @click="toggle(category.key)"
                        >
                            <span
                                class="absolute top-[3px] left-[3px] size-5 rounded-full bg-black transition-transform duration-200"
                                :class="
                                    isChecked(category.key)
                                        ? 'translate-x-5'
                                        : 'translate-x-0'
                                "
                            />
                        </button>
                    </li>
                </ul>

                <div class="mt-6 flex flex-wrap gap-3">
                    <button
                        v-if="showPreferences"
                        type="button"
                        class="flex h-[46px] items-center rounded-[30px] bg-[#ffc700] px-6 text-[15px] font-extrabold tracking-[-0.02em] text-black transition-opacity hover:opacity-85"
                        @click="savePreferences({ analytics, marketing })"
                    >
                        Save preferences
                    </button>
                    <button
                        v-else
                        type="button"
                        class="flex h-[46px] items-center rounded-[30px] bg-[#ffc700] px-6 text-[15px] font-extrabold tracking-[-0.02em] text-black transition-opacity hover:opacity-85"
                        @click="acceptAll"
                    >
                        Accept all
                    </button>
                    <button
                        type="button"
                        class="flex h-[46px] items-center rounded-[30px] border border-white/40 px-6 text-[15px] tracking-[-0.02em] transition-colors hover:border-white hover:bg-white hover:text-black"
                        @click="acceptNecessaryOnly"
                    >
                        Only necessary
                    </button>
                </div>

                <button
                    v-if="!showPreferences"
                    type="button"
                    class="mt-4 text-[14px] text-white/70 underline underline-offset-4 transition-colors hover:text-white"
                    @click="showPreferences = true"
                >
                    Preferences
                </button>
            </section>
        </Transition>
    </Teleport>
</template>

<style scoped>
.lst-panel-enter-active,
.lst-panel-leave-active {
    transition:
        opacity 0.3s ease,
        transform 0.3s ease;
}

.lst-panel-enter-from,
.lst-panel-leave-to {
    opacity: 0;
    transform: translateY(16px);
}

@media (prefers-reduced-motion: reduce) {
    .lst-panel-enter-active,
    .lst-panel-leave-active {
        transition: opacity 0.2s ease;
    }

    .lst-panel-enter-from,
    .lst-panel-leave-to {
        transform: none;
    }
}
</style>
