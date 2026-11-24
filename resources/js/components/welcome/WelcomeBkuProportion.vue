<script setup lang="ts">
import { ChartPie } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface BkuItem {
    id: number;
    nama: string;
    kota: string | null;
    kategori: string | null;
    penetapan: number;
    total_produksi: number | string;
    total_lifting: number | string;
}

interface ProportionSegment {
    key: string;
    name: string;
    value: number;
    percentage: number;
    color: string;
    strokeDash: string;
    strokeOffset: number;
    isOther: boolean;
}

const props = withDefaults(
    defineProps<{
        bkuList: BkuItem[];
        selectedKabupaten?: string | null;
        labelPeriode?: string;
        maxSlices?: number;
    }>(),
    {
        selectedKabupaten: null,
        labelPeriode: '',
        maxSlices: 6,
    },
);

const PALETTE = ['#3B82F6', '#10B981', '#F59E0B', '#8B5CF6', '#F43F5E', '#06B6D4', '#64748B'];

const hoveredKey = ref<string | null>(null);

const totalProduksi = computed(() => props.bkuList.reduce((sum, bku) => sum + (Number(bku.total_produksi) || 0), 0));

const segments = computed<ProportionSegment[]>(() => {
    const total = totalProduksi.value;

    if (!total) return [];

    const sorted = [...props.bkuList]
        .map((bku) => ({ id: bku.id, name: bku.nama, value: Number(bku.total_produksi) || 0 }))
        .filter((bku) => bku.value > 0)
        .sort((a, b) => b.value - a.value);

    const top = sorted.slice(0, props.maxSlices);
    const rest = sorted.slice(props.maxSlices);

    const grouped = [...top];

    if (rest.length > 0) {
        grouped.push({
            id: -1,
            name: `BKU Lainnya (${rest.length})`,
            value: rest.reduce((sum, bku) => sum + bku.value, 0),
        });
    }

    let accumulatedOffset = 0;

    return grouped.map((item, index) => {
        const percentage = (item.value / total) * 100;
        const segment: ProportionSegment = {
            key: item.id === -1 ? 'lainnya' : String(item.id),
            name: item.name,
            value: item.value,
            percentage,
            color: PALETTE[index % PALETTE.length],
            strokeDash: `${percentage} ${100 - percentage}`,
            strokeOffset: -accumulatedOffset,
            isOther: item.id === -1,
        };

        accumulatedOffset += percentage;

        return segment;
    });
});

const activeSegment = computed<ProportionSegment | null>(() =>
    hoveredKey.value ? segments.value.find((segment) => segment.key === hoveredKey.value) || null : null,
);

const formatCompact = (value: number) => new Intl.NumberFormat('id-ID', { notation: 'compact', maximumFractionDigits: 1 }).format(value);

const formatPercent = (value: number) => value.toLocaleString('id-ID', { maximumFractionDigits: 1 });
</script>

<template>
    <section class="dark:border-slate-800 dark:bg-slate-900 rounded-2xl border border-slate-200/80 bg-white shadow-sm">
        <div class="dark:border-slate-800 dark:bg-slate-900/50 flex items-center justify-between border-b border-slate-100 bg-slate-50/70 px-5 py-3">
            <h3 class="dark:text-slate-200 flex items-center gap-2 text-sm font-semibold text-slate-800">
                <ChartPie class="h-4 w-4 text-emerald-500" />Proporsi Produksi per BKU
            </h3>
            <span
                v-if="selectedKabupaten"
                class="dark:bg-amber-950/80 dark:text-amber-300 rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-semibold text-amber-800"
            >
                {{ selectedKabupaten }}
            </span>
            <span
                v-else-if="labelPeriode"
                class="dark:bg-slate-800 dark:text-slate-300 rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-600"
            >
                {{ labelPeriode }}
            </span>
            <span v-else class="text-[11px] text-slate-500">Semua Wilayah</span>
        </div>
        <div class="p-5">
            <div v-if="segments.length > 0">
                <div class="relative flex items-center justify-center py-1">
                    <div class="relative h-36 w-36">
                        <svg viewBox="0 0 42 42" class="h-full w-full">
                            <circle
                                cx="21"
                                cy="21"
                                r="15.91549430918954"
                                fill="transparent"
                                stroke="currentColor"
                                stroke-width="5"
                                class="dark:text-slate-800 text-slate-100"
                            />
                            <circle
                                v-for="slice in segments"
                                :key="slice.key"
                                cx="21"
                                cy="21"
                                r="15.91549430918954"
                                fill="transparent"
                                :stroke="slice.color"
                                :stroke-width="hoveredKey === slice.key ? '6.5' : '5'"
                                :stroke-dasharray="slice.strokeDash"
                                :stroke-dashoffset="slice.strokeOffset"
                                transform="rotate(-90 21 21)"
                                class="cursor-pointer transition-all duration-300"
                                :class="{ 'opacity-40': hoveredKey && hoveredKey !== slice.key }"
                                @mouseenter="hoveredKey = slice.key"
                                @mouseleave="hoveredKey = null"
                            />
                        </svg>
                        <div class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center text-center">
                            <template v-if="activeSegment">
                                <span class="dark:text-white text-2xl font-bold tracking-tight text-slate-800"
                                    >{{ formatPercent(activeSegment.percentage) }}%</span
                                >
                                <span class="dark:text-slate-400 max-w-[90px] truncate text-[10px] font-semibold text-slate-500">{{
                                    activeSegment.name
                                }}</span>
                            </template>
                            <template v-else>
                                <span class="dark:text-white text-2xl font-bold tracking-tight text-slate-800">{{
                                    formatCompact(totalProduksi)
                                }}</span>
                                <span class="text-[10px] font-medium uppercase tracking-wider text-slate-400">Barel</span>
                            </template>
                        </div>
                    </div>
                </div>
                <div class="mt-3 grid grid-cols-2 gap-2">
                    <div
                        v-for="slice in segments"
                        :key="slice.key"
                        @mouseenter="hoveredKey = slice.key"
                        @mouseleave="hoveredKey = null"
                        class="flex cursor-pointer items-center justify-between gap-2 rounded-lg border p-2 transition-all"
                        :class="
                            hoveredKey === slice.key
                                ? 'dark:border-slate-700 dark:bg-slate-800 border-slate-300 bg-slate-50 shadow-sm'
                                : 'dark:border-slate-800/80 dark:bg-slate-900/40 dark:hover:bg-slate-800/60 border-slate-100 bg-slate-50/50 hover:bg-slate-50'
                        "
                    >
                        <div class="flex min-w-0 items-center gap-1.5">
                            <span class="h-2.5 w-2.5 flex-shrink-0 rounded-full" :style="{ backgroundColor: slice.color }" />
                            <span class="dark:text-slate-300 truncate text-xs font-semibold text-slate-700" :title="slice.name">{{
                                slice.name
                            }}</span>
                        </div>
                        <div class="flex flex-shrink-0 items-center gap-1 text-xs">
                            <span class="dark:text-white font-bold text-slate-900"> {{ formatCompact(slice.value) }} (Barel)</span>
                            <span
                                class="dark:bg-slate-800 dark:text-slate-300 rounded bg-slate-200/70 px-1 py-0.5 text-[10px] font-bold text-slate-700"
                            >
                                {{ formatPercent(slice.percentage) }}%
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else class="py-6 text-center text-sm text-slate-500">Belum ada data produksi BKU untuk filter ini.</div>
        </div>
    </section>
</template>
