<script setup lang="ts">
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import DashboardProgressBar from './DashboardProgressBar.vue';
import type { ReportingDisciplineItem } from './types';

defineProps<{
    items: ReportingDisciplineItem[];
}>();

const persentaseColor = (persentase: number): string => {
    if (persentase >= 90) {
        return 'bg-emerald-600';
    }

    if (persentase >= 70) {
        return 'bg-amber-500';
    }

    return 'bg-red-600';
};
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>Rekap Keterlambatan</CardTitle>
            <CardDescription>
                Rekap keterlambatan dan ketidakhadiran laporan harian pada periode filter. Hanya hari kerja yang
                dihitung, hari ini belum dinilai, dan
                laporan susulan tetap dihitung terlambat.
            </CardDescription>
        </CardHeader>

        <CardContent>
            <div v-if="items.length === 0" class="py-10 text-center text-sm text-gray-500">
                Belum ada hari kerja yang dapat dinilai pada periode ini.
            </div>

            <div v-else class="overflow-x-auto">
                <table class="w-full min-w-[760px]">
                    <thead>
                        <tr class="border-b text-left text-sm">
                            <th class="px-3 py-3 font-medium">BKU</th>
                            <th class="px-3 py-3 text-center font-medium">Hari Efektif</th>
                            <th class="px-3 py-3 text-center font-medium">Terlambat</th>
                            <th class="px-3 py-3 text-center font-medium">Tidak Melapor</th>
                            <th class="px-3 py-3 text-center font-medium">Tepat Waktu</th>
                            <th class="px-3 py-3 font-medium">Ketepatan</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-for="item in items" :key="item.bku_id" class="border-b last:border-0">
                            <td class="px-3 py-3 text-sm font-medium">
                                {{ item.nama }}
                            </td>

                            <td class="px-3 py-3 text-center text-sm">
                                {{ item.total_hari_efektif }}
                            </td>

                            <td class="px-3 py-3 text-center">
                                <span
                                    class="inline-flex min-w-9 items-center justify-center rounded-full border px-2 py-0.5 text-xs font-semibold"
                                    :class="item.terlambat > 0
                                            ? 'border-amber-200 bg-amber-50 text-amber-700'
                                            : 'border-gray-200 bg-gray-50 text-gray-500'
                                        ">
                                    {{ item.terlambat }}
                                </span>
                            </td>

                            <td class="px-3 py-3 text-center">
                                <span
                                    class="inline-flex min-w-9 items-center justify-center rounded-full border px-2 py-0.5 text-xs font-semibold"
                                    :class="item.tidak_melapor > 0 ? 'border-red-200 bg-red-50 text-red-700' : 'border-gray-200 bg-gray-50 text-gray-500'
                                        ">
                                    {{ item.tidak_melapor }}
                                </span>
                            </td>

                            <td class="px-3 py-3 text-center">
                                <span
                                    class="inline-flex min-w-9 items-center justify-center rounded-full border px-2 py-0.5 text-xs font-semibold"
                                    :class="item.tepat_waktu > 0
                                            ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                                            : 'border-gray-200 bg-gray-50 text-gray-500'
                                        ">
                                    {{ item.tepat_waktu }}
                                </span>
                            </td>

                            <td class="px-3 py-3">
                                <div class="flex items-center gap-3">
                                    <DashboardProgressBar :value="item.persentase_tepat_waktu" :max="100"
                                        :color-class="persentaseColor(item.persentase_tepat_waktu)" />

                                    <span class="w-14 text-right text-xs font-medium text-gray-600"> {{
                                        item.persentase_tepat_waktu }}% </span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </CardContent>
    </Card>
</template>
