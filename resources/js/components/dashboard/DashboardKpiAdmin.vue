<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { formatNumber } from '@/lib/formatters';
import type { Stats } from './types';

defineProps<{
    stats: Stats;
}>();
</script>

<template>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <!-- jumlah bku -->
        <Card>
            <CardHeader class="pb-2">
                <CardTitle class="text-sm font-medium text-gray-500">Jumlah BKU</CardTitle>
            </CardHeader>

            <CardContent>
                <div class="text-2xl font-bold">
                    {{ formatNumber(stats.total_bku) }}
                </div>
            </CardContent>
        </Card>

        <!-- jumlah Kontrak -->
        <Card>
            <CardHeader class="pb-2">
                <CardTitle class="text-sm font-medium text-gray-500">Jumlah Kontrak</CardTitle>
            </CardHeader>

            <CardContent>
                <div class="text-2xl font-bold">
                    {{ formatNumber(stats.total_kontrak) }}
                </div>
            </CardContent>
        </Card>

        <!-- Rata2 produksi -->
        <Card>
            <CardHeader class="pb-2">
                <CardTitle class="text-sm font-medium text-gray-500">Rata-rata Produksi</CardTitle>
            </CardHeader>

            <CardContent>
                <div class="text-2xl font-bold">
                    {{ formatNumber(stats.average_produksi) }}
                    <span>Barel</span>
                </div>

                <p class="mt-1 text-xs text-gray-500">per hari</p>
            </CardContent>
        </Card>
    </div>
</template>
