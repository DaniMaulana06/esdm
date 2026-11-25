<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import type { MissingReport } from './types';

defineProps<{
    items: MissingReport[];
}>();
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>BKU Belum melapor hari ini</CardTitle>
        </CardHeader>

        <CardContent>
            <div v-if="items.length === 0" class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                Semua BKU sudah melaporkan seluruh kontraknya hari ini.
            </div>

            <div v-else class="space-y-3">
                <div v-for="item in items" :key="item.id" class="rounded-lg border p-4">
                    <div class="flex items-center justify-between">
                        <span class="font-medium">
                            {{ item.nama }}
                        </span>

                        <span class="text-sm text-red-600">{{ item.missing_count }} kontrak belum melapor</span>
                    </div>

                    <div class="mt-3 flex flex-wrap gap-2">
                        <span v-for="contract in item.contracts" :key="contract.id" class="rounded-md bg-gray-100 px-2 py-1 text-xs text-gray-700">
                            {{ contract.nama }}
                        </span>
                    </div>
                </div>
            </div>
        </CardContent>
    </Card>
</template>
