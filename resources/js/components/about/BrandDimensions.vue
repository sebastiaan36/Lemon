<script setup lang="ts">
import { computed } from 'vue';

export type DimensionItem = { name: string; subtitle?: string | null };

const props = defineProps<{
    centerTitle: string;
    centerSubtitle?: string | null;
    /** Vier dimensies, met de klok mee: boven, rechts, onder, links. */
    items: DimensionItem[];
}>();

/**
 * Figma (762×762): posities van de vier labels ten opzichte van de cirkel.
 * Percentages zodat het diagram met de breedte meeschaalt.
 */
const SLOTS = [
    { left: '50%', top: '13.1%' },
    { left: '84.4%', top: '50%', centerY: true },
    { left: '50%', top: '78.9%' },
    { left: '15.6%', top: '50%', centerY: true },
] as const;

const placedItems = computed(() =>
    props.items.slice(0, 4).map((item, index) => ({
        ...item,
        slot: SLOTS[index],
    })),
);
</script>

<template>
    <div
        class="[container-type:inline-size] relative aspect-square w-full"
        role="img"
        :aria-label="
            [centerTitle, ...items.map((item) => item.name)].join(', ')
        "
    >
        <!--
            Eén vector-SVG in Figma-coördinaten (762×762). non-scaling-stroke houdt
            elke lijn op precies 1,5px, ongeacht hoe groot het diagram wordt getekend.
            Diagonalen: Figma "Line 21" (-140.65°) en "Line 22" (134.92°) door het midden.
        -->
        <svg
            class="absolute inset-0 size-full overflow-visible"
            viewBox="0 0 762 762"
            fill="none"
            aria-hidden="true"
            shape-rendering="geometricPrecision"
        >
            <g
                stroke="#B58D00"
                stroke-width="1.5"
                vector-effect="non-scaling-stroke"
            >
                <circle
                    cx="381"
                    cy="381"
                    r="380.5"
                    vector-effect="non-scaling-stroke"
                />
                <line
                    x1="86.4"
                    y1="139.4"
                    x2="675.6"
                    y2="622.6"
                    vector-effect="non-scaling-stroke"
                />
                <line
                    x1="650"
                    y1="111.2"
                    x2="112"
                    y2="650.8"
                    vector-effect="non-scaling-stroke"
                />
            </g>
            <circle cx="381" cy="381" r="153" fill="#000" />
        </svg>

        <div
            class="absolute top-1/2 left-1/2 size-[40.16%] -translate-x-1/2 -translate-y-1/2"
        >
            <div
                class="absolute inset-0 flex flex-col items-center justify-center px-[2cqw] text-center"
            >
                <p
                    class="font-avenir text-[max(20px,6.3cqw)] leading-[0.875] font-black tracking-[-0.03em] text-[#ffc700] italic"
                >
                    {{ centerTitle }}
                </p>
                <p
                    v-if="centerSubtitle"
                    class="mt-[2.2cqw] font-avenir text-[max(11px,2.62cqw)] leading-tight tracking-[-0.03em] text-white"
                >
                    {{ centerSubtitle }}
                </p>
            </div>
        </div>

        <div
            v-for="item in placedItems"
            :key="item.name"
            class="absolute w-[30%] -translate-x-1/2 text-center"
            :class="'centerY' in item.slot ? '-translate-y-1/2' : ''"
            :style="{ left: item.slot.left, top: item.slot.top }"
        >
            <p
                class="mx-auto max-w-[6em] font-avenir text-[max(13px,4.2cqw)] leading-none font-black tracking-[-0.03em] text-black italic"
            >
                {{ item.name }}
            </p>
            <p
                v-if="item.subtitle"
                class="mt-[0.4cqw] font-avenir text-[max(10px,2.62cqw)] leading-tight tracking-[-0.03em] whitespace-nowrap text-black"
            >
                {{ item.subtitle }}
            </p>
        </div>
    </div>
</template>
