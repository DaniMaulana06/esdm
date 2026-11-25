<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { formatDate } from '@/lib/formatters';
import { Link } from '@inertiajs/vue3';
import type { MissingDateForOperator } from './types';

defineProps<{
    items: MissingDateForOperator[];
}>();
</script>

<template>
    <Card>
        <CardHeader>
            <div class="flex items-center justify-between">
                <div>
                    <CardTitle>Laporan Terlewat</CardTitle>
                    <p class="mt-1 text-sm text-gray-500">Laporan terlewat lebih dari 7 hari. Klik tanggal untuk mengisi.</p>
                </div>
                <span v-if="items.length > 0" class="inline-flex items-center rounded-full bg-red-100 px-3 py-1 text-sm font-medium text-red-700">
                    {{ items.length }} hari terlewat
                </span>
            </div>
        </CardHeader>

        <CardContent>
            <!-- Tidak ada laporan terlewat di luar masa tenggang -->
            <div v-if="items.length === 0" class="rounded-lg border border-green-200 bg-green-50 px-4 py-4 text-sm text-green-700">
                ✅ Tidak ada laporan yang terlewat lebih dari 7 hari.
            </div>

            <!-- Daftar tanggal terlewat -->
            <div v-else class="space-y-3">
                <Link
                    v-for="item in items"
                    :key="item.tanggal"
                    :href="route('laporan-harian.create-backfill', { tanggal: item.tanggal })"
                    class="block"
                >
                    <div
                        class="flex cursor-pointer items-center justify-between rounded-lg border border-red-100 bg-red-50 px-4 py-3 transition hover:border-red-300 hover:bg-red-100"
                    >
                        <div class="flex items-center gap-4">
                            <!-- Ikon kalender merah -->
                            <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-red-200 text-red-700">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-5 w-5"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                    />
                                </svg>
                            </div>

                            <div>
                                <p class="font-semibold text-gray-800">
                                    {{ formatDate(item.tanggal) }}
                                </p>
                                <div class="mt-1 flex flex-wrap gap-1">
                                    <span
                                        v-for="contract in item.contracts"
                                        :key="contract.id"
                                        class="rounded bg-red-200 px-1.5 py-0.5 text-xs text-red-800"
                                    >
                                        {{ contract.nama }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <span class="flex items-center gap-1 rounded-md bg-red-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm">
                            Isi Laporan
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-3.5 w-3.5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="2.5"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </span>
                    </div>
                </Link>
            </div>
        </CardContent>
    </Card>
</template>
