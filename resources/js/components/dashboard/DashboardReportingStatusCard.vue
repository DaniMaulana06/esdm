<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import DashboardProgressBar from './DashboardProgressBar.vue';
import type { ReportingStatus } from './types';

defineProps<{
    status: ReportingStatus;
}>();
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>Status Pelaporan Hari Ini</CardTitle>
        </CardHeader>

        <CardContent>
            <div class="grid grid-cols-3 gap-3 text-center">
                <div class="rounded-lg border p-4">
                    <p class="text-2xl font-bold">
                        {{ status.total_bku }}
                    </p>

                    <p class="mt-1 text-xs text-gray-500">Total BKU</p>
                </div>

                <div class="rounded-lg border p-4">
                    <p class="text-2xl font-bold text-green-600">
                        {{ status.sudah_melapor }}
                    </p>

                    <p class="mt-1 text-xs text-gray-500">Lengkap</p>
                </div>

                <div class="rounded-lg border p-4">
                    <p class="text-2xl font-bold text-red-600">
                        {{ status.belum_melapor }}
                    </p>

                    <p class="mt-1 text-xs text-gray-500">Belum Lengkap</p>
                </div>
            </div>

            <div class="mt-5">
                <div class="mb-2 flex justify-between text-sm">
                    <span>Kelengkapan laporan</span>

                    <span class="font-medium">{{ status.percentage }}%</span>
                </div>

                <DashboardProgressBar :value="status.percentage" :max="100" color-class="bg-green-600" />
            </div>
        </CardContent>
    </Card>
</template>
