<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { formatDate, formatNumber } from '@/lib/formatters';
import type { LatestReport } from './types';

defineProps<{
    items: LatestReport[];
}>();
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>Laporan Terbaru</CardTitle>
        </CardHeader>

        <CardContent>
            <div v-if="items.length === 0" class="py-10 text-center text-sm text-gray-500">Belum ada laporan.</div>

            <div v-else class="overflow-x-auto">
                <table class="w-full min-w-[800px]">
                    <thead>
                        <tr class="border-b text-left text-sm">
                            <th class="px-3 py-3 font-medium">Tanggal</th>

                            <th class="px-3 py-3 font-medium">BKU</th>

                            <th class="px-3 py-3 font-medium">Kontrak</th>

                            <th class="px-3 py-3 text-right font-medium">Produksi</th>

                            <th class="px-3 py-3 text-right font-medium">Lifting</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-for="item in items" :key="item.id" class="border-b last:border-0">
                            <td class="px-3 py-3 text-sm">
                                {{ formatDate(item.tanggal) }}
                            </td>

                            <td class="px-3 py-3 text-sm">
                                {{ item.bku }}
                            </td>

                            <td class="px-3 py-3 text-sm">
                                {{ item.kontrak }}
                            </td>

                            <td class="px-3 py-3 text-right text-sm">
                                {{ formatNumber(item.total_produksi) }}
                            </td>

                            <td class="px-3 py-3 text-right text-sm">
                                {{ formatNumber(item.total_lifting) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </CardContent>
    </Card>
</template>
