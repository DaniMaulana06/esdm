<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { formatDate, formatNumber } from '@/lib/formatters';
import type { ProductionLiftingItem } from './types';

defineProps<{
    items: ProductionLiftingItem[];
}>();
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>Produksi & Lifting</CardTitle>
        </CardHeader>

        <CardContent>
            <div v-if="items.length === 0" class="flex h-40 items-center justify-center text-sm text-gray-500">Belum ada data.</div>

            <div v-else class="overflow-x-auto">
                <table class="w-full min-w-[600px]">
                    <thead>
                        <tr class="border-b text-left text-sm">
                            <th class="px-3 py-3 font-medium">Tanggal</th>

                            <th class="px-3 py-3 font-medium">Produksi</th>

                            <th class="px-3 py-3 font-medium">Lifting</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-for="item in items" :key="item.tanggal" class="border-b last:border-0">
                            <td class="px-3 py-3 text-sm">
                                {{ formatDate(item.tanggal) }}
                            </td>

                            <td class="px-3 py-3 text-sm">
                                {{ formatNumber(item.total_produksi) }}
                            </td>

                            <td class="px-3 py-3 text-sm">
                                {{ formatNumber(item.total_lifting) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </CardContent>
    </Card>
</template>
