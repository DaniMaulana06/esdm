<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { formatNumber } from '@/lib/formatters';
import { GroupedBar } from '@unovis/ts';
import { VisAxis, VisGroupedBar, VisTooltip, VisXYContainer } from '@unovis/vue';
import { computed } from 'vue';
import { CHART_COLORS } from './constants';
import type { ChartData, ProductionMonthlyItem } from './types';

const props = defineProps<{
    items: ProductionMonthlyItem[];
}>();

const chartData = computed<ChartData[]>(() =>
    props.items.map((item, index) => ({
        index,
        label: new Intl.DateTimeFormat('id-ID', {
            month: 'short',
            year: 'numeric',
            timeZone: 'UTC',
        }).format(new Date(`${item.bulan}-01T00:00:00Z`)),
        total_produksi: Number(item.total_produksi),
        total_lifting: Number(item.total_lifting),
    })),
);

const isChartEmpty = computed(() => chartData.value.every((d) => d.total_produksi === 0 && d.total_lifting === 0));

const tooltipTriggers = {
    [GroupedBar.selectors.bar]: (d: ChartData): string => `
        <div style="min-width:170px">
            <div style="font-weight:600;margin-bottom:6px">${d.label}</div>
            <div style="display:flex;align-items:center;justify-content:space-between;gap:12px">
                <span style="display:flex;align-items:center;gap:6px">
                    <span style="width:10px;height:10px;border-radius:2px;background:${CHART_COLORS[0]}"></span>
                    Produksi
                </span>
                <strong>${formatNumber(d.total_produksi)}</strong>
            </div>
            <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:4px">
                <span style="display:flex;align-items:center;gap:6px">
                    <span style="width:10px;height:10px;border-radius:2px;background:${CHART_COLORS[1]}"></span>
                    Lifting
                </span>
                <strong>${formatNumber(d.total_lifting)}</strong>
            </div>
        </div>
    `,
};

const xTickValues = computed(() => chartData.value.map((d) => d.index));
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>Perbandingan Produksi & Lifting</CardTitle>
            <p class="text-sm text-muted-foreground">Perbandingan produksi dan lifting berdasarkan tanggal.</p>
        </CardHeader>

        <CardContent>
            <div v-if="isChartEmpty" class="flex h-80 items-center justify-center text-sm text-muted-foreground">Belum ada data produksi.</div>

            <div v-else class="h-[350px] w-full">
                <VisXYContainer :data="chartData" :height="320" :margin="{ top: 20, right: 20, bottom: 40, left: 60 }">
                    <VisGroupedBar
                        :x="(d: ChartData) => d.index"
                        :y="[(d: ChartData) => d.total_produksi, (d: ChartData) => d.total_lifting]"
                        :color="CHART_COLORS"
                        :rounded-corners="4"
                        :group-padding="0.2"
                        :bar-padding="0.1"
                    />

                    <VisAxis type="x" :tick-values="xTickValues" :tick-format="(i: number) => chartData[i]?.label ?? ''" />

                    <VisAxis type="y" :tick-format="(v: number) => new Intl.NumberFormat('id-ID', { notation: 'compact' }).format(v)" />

                    <VisTooltip :triggers="tooltipTriggers" />
                </VisXYContainer>
            </div>

            <!-- Legend -->
            <div class="mt-4 flex justify-center gap-6">
                <div class="flex items-center gap-2">
                    <span class="h-3 w-3 rounded-sm" :style="{ backgroundColor: CHART_COLORS[0] }"></span>
                    <span class="text-sm text-muted-foreground">Produksi</span>
                </div>

                <div class="flex items-center gap-2">
                    <span class="h-3 w-3 rounded-sm" :style="{ backgroundColor: CHART_COLORS[1] }"></span>
                    <span class="text-sm text-muted-foreground">Lifting</span>
                </div>
            </div>
        </CardContent>
    </Card>
</template>
