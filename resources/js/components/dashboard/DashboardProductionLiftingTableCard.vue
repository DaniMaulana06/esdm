<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { formatDate, formatNumber } from '@/lib/formatters';
import { computed } from 'vue';
import type { ProductionLiftingItem } from './types';

const props = defineProps<{
    items: ProductionLiftingItem[];
}>();

interface ContractColumn {
    id: number;
    nama: string;
}

interface PivotedRow {
    tanggal: string;
    byContract: Record<number, { produksi: number; lifting: number }>;
}

const contracts = computed<ContractColumn[]>(() => {
    const map = new Map<number, string>();

    for (const item of props.items) {
        if (!map.has(item.kontrak_id)) {
            map.set(item.kontrak_id, item.kontrak);
        }
    }

    return Array.from(map.entries())
        .map(([id, nama]) => ({ id, nama }))
        .sort((a, b) => a.nama.localeCompare(b.nama, 'id'));
});

const rows = computed<PivotedRow[]>(() => {
    const map = new Map<string, PivotedRow>();

    for (const item of props.items) {
        let row = map.get(item.tanggal);

        if (!row) {
            row = {
                tanggal: item.tanggal,
                byContract: {},
            };
            map.set(item.tanggal, row);
        }

        row.byContract[item.kontrak_id] = {
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
            <CardTitle>Produksi & Lifting</CardTitle>
        </CardHeader>

        <CardContent>
            <div v-if="rows.length === 0" class="flex h-40 items-center justify-center text-sm text-gray-500">Belum ada
                data.</div>

            <div v-else class="overflow-x-auto">
                <table class="w-full min-w-[600px]">
                    <thead>
                        <tr class="border-b text-left text-sm">
                            <th rowspan="2" class="px-3 py-3 text-left align-bottom font-medium whitespace-nowrap">Tanggal</th>

                            <th v-for="contract in contracts" :key="contract.id" colspan="2"
                                class="border-l px-3 py-2 text-center font-medium whitespace-nowrap">
                                {{ contract.nama }}
                            </th>
                        </tr>

                        <tr class="border-b text-sm">
                            <template v-for="contract in contracts" :key="`${contract.id}-sub`">
                                <th class="border-l px-3 py-2 text-center font-medium whitespace-nowrap">Produksi</th>
                                <th class="px-3 py-2 text-center font-medium whitespace-nowrap">Lifting</th>
                            </template>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-for="row in rows" :key="row.tanggal" class="border-b last:border-0">
                            <td class="px-3 py-3 text-left text-sm whitespace-nowrap">
                                {{ formatDate(row.tanggal) }}
                            </td>

                            <template v-for="contract in contracts" :key="`${row.tanggal}-${contract.id}`">
                                <td class="border-l px-3 py-3 text-center text-sm whitespace-nowrap">
                                    {{
                                        row.byContract[contract.id] !== undefined
                                            ? formatNumber(row.byContract[contract.id].produksi)
                                            : '-'
                                    }}
                                </td>

                                <td class="px-3 py-3 text-center text-sm whitespace-nowrap">
                                    {{
                                        row.byContract[contract.id] !== undefined
                                            ? formatNumber(row.byContract[contract.id].lifting)
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
