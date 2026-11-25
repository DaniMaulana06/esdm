<script setup lang="ts">
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { formatNumber } from '@/lib/formatters';
import { computed } from 'vue';
import DashboardProgressBar from './DashboardProgressBar.vue';
import type { ProductionByBku } from './types';

const props = defineProps<{
    items: ProductionByBku[];
}>();

const maxProduction = computed(() => {
    return Math.max(...props.items.map((item) => item.total_produksi), 1);
});
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>Produksi Berdasarkan BKU</CardTitle>
            <CardDescription>Total produksi dari tiap BKU pada bulan ini.</CardDescription>
        </CardHeader>

        <CardContent>
            <div v-if="items.length === 0" class="py-10 text-center text-sm text-gray-500">Belum ada data produksi.</div>

            <div v-else class="space-y-5">
                <div v-for="item in items" :key="item.id">
                    <div class="mb-2 flex justify-between text-sm">
                        <span class="font-medium">
                            {{ item.nama }}
                        </span>

                        <span>
                            {{ formatNumber(item.total_produksi) }}
                        </span>
                    </div>

                    <DashboardProgressBar :value="item.total_produksi" :max="maxProduction" color-class="bg-gray-800" />
                </div>
            </div>
        </CardContent>
    </Card>
</template>
