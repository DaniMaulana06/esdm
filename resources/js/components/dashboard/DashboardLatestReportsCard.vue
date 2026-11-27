<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { formatDate, formatNumber } from '@/lib/formatters';
import { computed } from 'vue';
import type { LatestReport } from './types';

const props = defineProps<{
    items: LatestReport[];
}>();

interface PivotedLatestReportRow {
    key: string;
    tanggal: string;
    bku: string;
    byContract: Record<string, { produksi: number; lifting: number }>;
}

const contracts = computed<string[]>(() => {
    const set = new Set<string>();

    for (const item of props.items) {
        set.add(item.kontrak);
    }

    return Array.from(set).sort((a, b) => a.localeCompare(b, 'id'));
});

const rows = computed<PivotedLatestReportRow[]>(() => {
    const map = new Map<string, PivotedLatestReportRow>();

    for (const item of props.items) {
        const key = `${item.tanggal}::${item.bku}`;
        let row = map.get(key);

        if (!row) {
            row = {
                key,
                tanggal: item.tanggal,
                bku: item.bku,
                byContract: {},
            };
            map.set(key, row);
        }

        row.byContract[item.kontrak] = {
            produksi: item.total_produksi,
            lifting: item.total_lifting,
        };
    }

    return Array.from(map.values());
});
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>Laporan Terbaru</CardTitle>
        </CardHeader>

        <CardContent>
            <div v-if="rows.length === 0" class="py-10 text-center text-sm text-gray-500">Belum ada laporan.</div>

            <div v-else class="overflow-x-auto">
                <table class="w-full min-w-[800px]">
                    <thead>
                        <tr class="border-b text-left text-sm">
                            <th rowspan="2" class="px-3 py-3 align-bottom font-medium whitespace-nowrap">Tanggal</th>

                            <th rowspan="2" class="px-3 py-3 align-bottom font-medium whitespace-nowrap">BKU</th>

                            <th
                                v-for="contract in contracts"
                                :key="contract"
                                colspan="2"
                                class="border-l px-3 py-2 text-center font-medium whitespace-nowrap"
                            >
                                {{ contract }}
                            </th>
                        </tr>

                        <tr class="border-b text-sm">
                            <template v-for="contract in contracts" :key="`${contract}-sub`">
                                <th class="border-l px-3 py-2 text-center font-medium whitespace-nowrap">Produksi</th>
                                <th class="px-3 py-2 text-center font-medium whitespace-nowrap">Lifting</th>
                            </template>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-for="row in rows" :key="row.key" class="border-b last:border-0">
                            <td class="px-3 py-3 text-sm whitespace-nowrap">
                                {{ formatDate(row.tanggal) }}
                            </td>

                            <td class="px-3 py-3 text-sm whitespace-nowrap">
                                {{ row.bku }}
                            </td>

                            <template v-for="contract in contracts" :key="`${row.key}-${contract}`">
                                <td class="border-l px-3 py-3 text-center text-sm whitespace-nowrap">
                                    {{
                                        row.byContract[contract] !== undefined
                                            ? formatNumber(row.byContract[contract].produksi)
                                            : '-'
                                    }}
                                </td>

                                <td class="px-3 py-3 text-center text-sm whitespace-nowrap">
                                    {{
                                        row.byContract[contract] !== undefined
                                            ? formatNumber(row.byContract[contract].lifting)
                                            : '-'
                                    }}
                                </td>
                            </template>
                        </tr>
                    </tbody>
                </table>
            </div>
        </CardContent>
    </Card>
</template>
