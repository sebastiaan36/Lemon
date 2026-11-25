<script setup lang="ts">
import { computed } from 'vue';

export type DimensionItem = { name: string; subtitle?: string | null };

const props = defineProps<{
    /** Bijv. "Lemon's Total Brand Growth Formula". */
    title?: string | null;
    subtitle?: string | null;
    centerTitle: string;
    centerSubtitle?: string | null;
    /** Vier dimensies, met de klok mee: boven, rechts, onder, links. */
    items: DimensionItem[];
}>();

const top = computed(() => props.items[0] ?? null);
const right = computed(() => props.items[1] ?? null);
const bottom = computed(() => props.items[2] ?? null);
const left = computed(() => props.items[3] ?? null);
const sides = computed(() =>
    [left.value, right.value].filter(
        (item): item is DimensionItem => item !== null,
    ),
);
</script>

<template>
    <!--
        "Lemon's Credible Creativity Formula": een kruis met Brand boven, Brand
        narrative in het midden, Customer en Employee experience links en rechts
        en Proposition onder, met dunne lijnen van het midden naar de vier armen.
        Alles echte tekst, in hoofdletters via CSS. Maten in cqw zodat het schema
        als geheel meeschaalt; op smalle schermen (< 512px breed) komen Customer
        en Employee experience onder het schema naast elkaar.
    -->
    <div class="@container w-full text-center font-avenir text-black uppercase">
        <div v-if="title || subtitle">
            <h3
                v-if="title"
                class="text-[22px] leading-[1.05] font-black tracking-[-0.01em] @lg:text-[4.3cqw]"
            >
                {{ title }}
            </h3>
            <p
                v-if="subtitle"
                class="mt-2 text-[10px] leading-snug tracking-[0.3em] @lg:mt-[0.9cqw] @lg:text-[1.75cqw] @lg:tracking-[0.35em]"
            >
                {{ subtitle }}
            </p>
        </div>

        <div :class="title || subtitle ? 'mt-12 @lg:mt-[8.5cqw]' : ''">
            <div v-if="top">
                <p
                    class="text-[30px] leading-none font-black @lg:text-[4.7cqw]"
                >
                    {{ top.name }}
                </p>
                <p
                    v-if="top.subtitle"
                    class="mt-1.5 text-[12px] leading-snug @lg:mt-[0.8cqw] @lg:text-[1.9cqw]"
                >
                    {{ top.subtitle }}
                </p>
            </div>

            <span
                class="mx-auto mt-3 block h-[36px] w-px bg-black @lg:mt-[2cqw] @lg:h-[4.8cqw]"
                aria-hidden="true"
            />

            <div
                class="mt-3 grid grid-cols-1 items-start @lg:mt-[1.6cqw] @lg:grid-cols-[minmax(0,1fr)_5cqw_auto_5cqw_minmax(0,1fr)] @lg:gap-[1.2cqw]"
            >
                <div v-if="left" class="hidden @lg:block @lg:pt-[0.2cqw]">
                    <p
                        class="text-[2.9cqw] leading-[1.05] font-black whitespace-pre-line"
                    >
                        {{ left.name }}
                    </p>
                    <p
                        v-if="left.subtitle"
                        class="mt-[3.6cqw] text-[max(10px,1.15cqw)] leading-snug"
                    >
                        {{ left.subtitle }}
                    </p>
                </div>

                <span
                    v-if="left"
                    class="hidden h-px bg-black @lg:mt-[2.45cqw] @lg:block"
                    aria-hidden="true"
                />

                <div>
                    <p
                        class="text-[32px] leading-none font-black tracking-[-0.01em] @lg:text-[4.9cqw] @lg:whitespace-nowrap"
                    >
                        {{ centerTitle }}
                    </p>
                    <p
                        v-if="centerSubtitle"
                        class="mt-2 text-[12px] leading-snug @lg:mt-[1.2cqw] @lg:text-[1.75cqw]"
                    >
                        {{ centerSubtitle }}
                    </p>
                </div>

                <span
                    v-if="right"
                    class="hidden h-px bg-black @lg:mt-[2.45cqw] @lg:block"
                    aria-hidden="true"
                />

                <div v-if="right" class="hidden @lg:block @lg:pt-[0.2cqw]">
                    <p
                        class="text-[2.9cqw] leading-[1.05] font-black whitespace-pre-line"
                    >
                        {{ right.name }}
                    </p>
                    <p
                        v-if="right.subtitle"
                        class="mt-[3.6cqw] text-[max(10px,1.15cqw)] leading-snug"
                    >
                        {{ right.subtitle }}
                    </p>
                </div>
            </div>

            <span
                class="mx-auto mt-3 block h-[56px] w-px bg-black @lg:mt-[1.4cqw] @lg:h-[9.5cqw]"
                aria-hidden="true"
            />

            <div v-if="bottom" class="mt-1 @lg:mt-[0.6cqw]">
                <p
                    class="text-[32px] leading-none font-black @lg:text-[4.9cqw]"
                >
                    {{ bottom.name }}
                </p>
                <p
                    v-if="bottom.subtitle"
                    class="mt-1.5 text-[12px] leading-snug @lg:mt-[0.6cqw] @lg:text-[1.9cqw]"
                >
                    {{ bottom.subtitle }}
                </p>
            </div>

            <!-- Smalle schermen: Customer en Employee experience onder het schema -->
            <div
                v-if="sides.length"
                class="mt-10 grid grid-cols-2 gap-6 @lg:hidden"
            >
                <div v-for="item in sides" :key="item.name">
                    <p class="text-[20px] leading-[1.05] font-black">
                        {{ item.name }}
                    </p>
                    <p
                        v-if="item.subtitle"
                        class="mt-2 text-[10px] leading-snug"
                    >
                        {{ item.subtitle }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
