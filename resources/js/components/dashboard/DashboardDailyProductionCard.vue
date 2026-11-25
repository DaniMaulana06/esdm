<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { formatDate, formatNumber } from '@/lib/formatters';
import { computed } from 'vue';
import { CHART_COLORS } from './constants';
import type { ProductionChartItem } from './types';

const props = defineProps<{
    items: ProductionChartItem[];
}>();

const maxProduction = computed(() => {
    return Math.max(...props.items.map((item) => item.total_produksi), 1);
});
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>Produksi Harian</CardTitle>
        </CardHeader>

        <CardContent>
            <div v-if="items.length === 0" class="flex h-64 items-center justify-center text-sm text-gray-500">
                Belum ada data produksi pada periode ini.
            </div>

            <div v-else class="space-y-3">
                <div v-for="(item, index) in items" :key="item.tanggal" class="grid grid-cols-[90px_1fr_100px] items-center gap-3">
                    <span class="text-xs text-gray-500">
                        {{ formatDate(item.tanggal) }}
                    </span>

                    <div
                        class="h-full rounded transition-all duration-500"
                        :style="{
                            width: `${(item.total_produksi / maxProduction) * 100}%`,
                            backgroundColor: CHART_COLORS[index % CHART_COLORS.length],
                        }"
                    />

                    <span class="text-right text-sm font-medium">
                        {{ formatNumber(item.total_produksi) }}
                    </span>
                </div>
            </div>
        </CardContent>
    </Card>
</template>
