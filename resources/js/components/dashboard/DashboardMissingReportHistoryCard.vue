<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { formatDate } from '@/lib/formatters';
import type { BkuBelumLapor } from './types';

defineProps<{
    items: BkuBelumLapor[];
}>();
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>Riwayat BKU Belum Melaporkan</CardTitle>
        </CardHeader>

        <CardContent>
            <div v-if="items.length === 0" class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                Semua BKU sudah melaporkan seluruh kontraknya pada hari kerja sebelumnya.
            </div>

            <div v-else class="overflow-x-auto">
                <table class="w-full min-w-[700px]">
                    <thead>
                        <tr class="border-b text-left text-sm">
                            <th class="px-3 py-3 font-medium">No</th>

                            <th class="px-3 py-3 font-medium">Tanggal</th>

                            <th class="px-3 py-3 font-medium">BKU</th>

                            <th class="px-3 py-3 font-medium">Kontrak Belum Lapor</th>

                            <th class="px-3 py-3 text-center font-medium">Jumlah</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-for="(item, index) in items" :key="`${item.tanggal}-${item.bku_id}`" class="border-b last:border-0">
                            <td class="px-3 py-3 text-sm">
                                {{ index + 1 }}
                            </td>

                            <td class="px-3 py-3 text-sm">
                                {{ formatDate(item.tanggal) }}
                            </td>

                            <td class="px-3 py-3 text-sm font-medium">
                                {{ item.bku }}
                            </td>

                            <td class="px-3 py-3">
                                <div class="flex flex-wrap gap-2">
                                    <span
                                        v-for="contract in item.contracts"
                                        :key="contract.id"
                                        class="rounded-md bg-red-50 px-2 py-1 text-xs text-red-700"
                                    >
                                        {{ contract.nama }}
                                    </span>
                                </div>
                            </td>

                            <td class="px-3 py-3 text-center text-sm font-medium text-red-600">
                                {{ item.missing_count }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </CardContent>
    </Card>
</template>
