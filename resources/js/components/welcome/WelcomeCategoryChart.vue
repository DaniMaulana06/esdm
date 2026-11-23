<script setup lang="ts">
import { PieChart as PieChartIcon } from 'lucide-vue-next';
interface CategorySegment {
    name: string; count: number; percentage: number; color: string; bgClass: string;
    textClass: string; badge: string; strokeDash: string; strokeOffset: number;
}
defineProps<{
    categoryStats: CategorySegment[];
    hoveredCategory: string | null;
    activeHoveredData: CategorySegment | null;
    selectedKabupaten: string | null;
    totalBku: number;
}>();
const emit = defineEmits<{ (e: 'update:hoveredCategory', value: string | null): void }>();
</script>

<template>
    <section class="rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div
            class="flex items-center justify-between border-b border-slate-100 bg-slate-50/70 px-5 py-3 dark:border-slate-800 dark:bg-slate-900/50">
            <h3 class="flex items-center gap-2 text-sm font-semibold text-slate-800 dark:text-slate-200">
                <PieChartIcon class="h-4 w-4 text-blue-500" />Proporsi Kategori BKU
            </h3>
            <span v-if="selectedKabupaten"
                class="rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-semibold text-amber-800 dark:bg-amber-950/80 dark:text-amber-300">
                {{ selectedKabupaten }}
            </span>
            <span v-else class="text-[11px] text-slate-500">Semua Wilayah</span>
        </div>
        <div class="p-5">
            <div v-if="categoryStats.length > 0">
                <div class="relative flex items-center justify-center py-1">
                    <div class="relative h-36 w-36">
                        <svg viewBox="0 0 42 42" class="h-full w-full">
                            <circle cx="21" cy="21" r="15.91549430918954" fill="transparent" stroke="currentColor"
                                stroke-width="5" class="text-slate-100 dark:text-slate-800" />
                            <circle v-for="slice in categoryStats" :key="slice.name" cx="21" cy="21"
                                r="15.91549430918954" fill="transparent" :stroke="slice.color"
                                :stroke-width="hoveredCategory === slice.name ? '6.5' : '5'"
                                :stroke-dasharray="slice.strokeDash" :stroke-dashoffset="slice.strokeOffset"
                                transform="rotate(-90 21 21)" class="cursor-pointer transition-all duration-300"
                                :class="{ 'opacity-40': hoveredCategory && hoveredCategory !== slice.name }"
                                @mouseenter="emit('update:hoveredCategory', slice.name)"
                                @mouseleave="emit('update:hoveredCategory', null)" />
                        </svg>
                        <div
                            class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center text-center">
                            <template v-if="activeHoveredData">
                                <span class="text-2xl font-bold tracking-tight text-slate-800 dark:text-white">{{
                                    activeHoveredData.percentage }}%</span>
                                <span
                                    class="max-w-[90px] truncate text-[10px] font-semibold text-slate-500 dark:text-slate-400">{{
                                        activeHoveredData.name }}</span>
                            </template>
                            <template v-else>
                                <span class="text-2xl font-bold tracking-tight text-slate-800 dark:text-white">{{
                                    totalBku }}</span>
                                <span class="text-[10px] font-medium uppercase tracking-wider text-slate-400">Total
                                    BKU</span>
                            </template>
                        </div>
                    </div>
                </div>
                <div class="mt-3 grid grid-cols-2 gap-2">
                    <div v-for="cat in categoryStats" :key="cat.name"
                        @mouseenter="emit('update:hoveredCategory', cat.name)"
                        @mouseleave="emit('update:hoveredCategory', null)"
                        class="flex cursor-pointer items-center justify-between rounded-lg border p-2 transition-all"
                        :class="hoveredCategory === cat.name ? 'border-slate-300 bg-slate-50 shadow-sm dark:border-slate-700 dark:bg-slate-800' : 'border-slate-100 bg-slate-50/50 hover:bg-slate-50 dark:border-slate-800/80 dark:bg-slate-900/40 dark:hover:bg-slate-800/60'">
                        <div class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 flex-shrink-0 rounded-full"
                                :style="{ backgroundColor: cat.color }" /><span
                                class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ cat.name }}</span>
                        </div>
                        <div class="flex items-center gap-1 text-xs"><span
                                class="font-bold text-slate-900 dark:text-white">{{ cat.count
                                }}</span><span
                                class="rounded bg-slate-200/70 px-1 py-0.5 text-[10px] font-bold text-slate-700 dark:bg-slate-800 dark:text-slate-300">{{
                                    cat.percentage }}%</span></div>
                    </div>
                </div>
            </div>
            <div v-else class="py-6 text-center text-sm text-slate-500">Belum ada data kategori untuk wilayah ini.</div>
        </div>
    </section>
</template>
