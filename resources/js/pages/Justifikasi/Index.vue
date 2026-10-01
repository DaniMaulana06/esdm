<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';

import AppLayout from '@/layouts/AppLayout.vue';

import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

import { Button } from '@/components/ui/button';

import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Textarea } from '@/components/ui/textarea';

interface Justifikasi {
    id: number;

    status: 'pending' | 'approved' | 'rejected';

    alasan_revisi: string;

    produksi_usulan: number | string;

    lifting_usulan: number | string;

    catatan_dinas: string | null;

    laporan_harian: {
        id: number;
        tanggal: string;

        total_produksi: number | string;

        total_lifting: number | string;

        bku_kontrak: {
            bku: {
                nama: string;
            };

            kontrak: {
                nama: string;
            };
        };
    };

    bku: {
        nama: string;
    };

    peninjau: {
        name: string;
    } | null;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Pagination {
    data: Justifikasi[];
    current_page: number;
    last_page: number;
    links: PaginationLink[];
}

const props = defineProps<{
    justifikasis: Pagination;

    filters: {
        status?: string;
    };
}>();

const selectedStatus = ref(
    props.filters.status ?? 'all'
);

const showProcessDialog = ref(false);

const selectedJustifikasi =
    ref<Justifikasi | null>(null);

const processStatus = ref<
    'approved' | 'rejected'
>('approved');

const catatanDinas = ref('');

const openProcessDialog = (
    justifikasi: Justifikasi,
    status: 'approved' | 'rejected',
) => {
    selectedJustifikasi.value = justifikasi;
    processStatus.value = status;
    catatanDinas.value =
        justifikasi.catatan_dinas ?? '';

    showProcessDialog.value = true;
};

const processJustifikasi = () => {
    if (!selectedJustifikasi.value) {
        return;
    }

    router.put(
        route(
            'justifikasi.process', {
            justifikasi: selectedJustifikasi.value.id,
        }),
        {
            status: processStatus.value,
            catatan_dinas:
                catatanDinas.value,
        },
        {
            preserveScroll: true,

            onSuccess: () => {
                showProcessDialog.value = false;
                selectedJustifikasi.value = null;
                catatanDinas.value = '';
            },
        },
    );
};

const applyFilter = () => {
    router.get(
        route('justifikasi.index'),
        {
            status:
                selectedStatus.value === 'all'
                    ? undefined
                    : selectedStatus.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};

const formatDate = (
    value: string,
): string => {
    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return '-';
    }

    return new Intl.DateTimeFormat(
        'id-ID',
        {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
        },
    ).format(date);
};

const formatNumber = (
    value: number | string,
): string => {
    return new Intl.NumberFormat(
        'id-ID',
        {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        },
    ).format(Number(value));
};

const statusLabel = (
    status: Justifikasi['status'],
): string => {
    const labels = {
        pending: 'Menunggu',
        approved: 'Disetujui',
        rejected: 'Ditolak',
    };

    return labels[status];
};
</script>

<template>

    <Head title="Justifikasi Revisi" />

    <AppLayout :breadcrumbs="[
        {
            title: 'Justifikasi Revisi',
            href: route('justifikasi.index'),
        },
    ]">
        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <h2 class="text-xl font-semibold text-gray-800">
                        Justifikasi Revisi
                    </h2>

                    <p class="mt-1 text-sm text-gray-600">
                        Kelola pengajuan revisi laporan harian.
                    </p>
                </div>

                <!-- Filter -->
                <Card class="mb-6">
                    <CardHeader>
                        <CardTitle>
                            Filter
                        </CardTitle>
                    </CardHeader>

                    <CardContent>
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                            <div class="w-full sm:w-64">
                                <label class="mb-2 block text-sm font-medium text-gray-700">
                                    Status
                                </label>

                                <Select v-model="selectedStatus
                                    ">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Pilih status" />
                                    </SelectTrigger>

                                    <SelectContent>
                                        <SelectItem value="all">
                                            Semua
                                        </SelectItem>

                                        <SelectItem value="pending">
                                            Menunggu
                                        </SelectItem>

                                        <SelectItem value="approved">
                                            Disetujui
                                        </SelectItem>

                                        <SelectItem value="rejected">
                                            Ditolak
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <Button @click="applyFilter">
                                Terapkan
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <!-- Table -->
                <Card>
                    <CardHeader>
                        <CardTitle>
                            Daftar Pengajuan
                        </CardTitle>
                    </CardHeader>

                    <CardContent class="p-3">
                        <div class="overflow-hidden rounded-lg border bg-white shadow-sm">
                            <table class="w-full min-w-[1100px]">
                                <thead class="border-b bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">
                                            No
                                        </th>

                                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">
                                            BKU
                                        </th>

                                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">
                                            Kontrak
                                        </th>

                                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">
                                            Tanggal
                                        </th>

                                        <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">
                                            Produksi
                                        </th>

                                        <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">
                                            Lifting
                                        </th>

                                        <th class="px-6 py-3 text-center text-sm font-semibold text-gray-700">
                                            Status
                                        </th>

                                        <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">
                                            Peninjau
                                        </th>
                                        <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">
                                            Aksi
                                        </th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-gray-200">
                                    <tr v-for="(justifikasi,index
                                        ) in justifikasis.data" :key="justifikasi.id
                                            ">
                                        <td class="px-6 py-4 text-sm text-gray-700">
                                            {{
                                                (justifikasis.current_page -
                                                    1) *
                                                15 +
                                                index +
                                                1
                                            }}
                                        </td>

                                        <td class="px-6 py-4 text-sm text-gray-700">
                                            {{
                                                justifikasi
                                                    .laporan_harian
                                                    .bku_kontrak
                                                    .bku
                                                    .nama
                                            }}
                                        </td>

                                        <td class="px-6 py-4 text-sm text-gray-700">
                                            {{
                                                justifikasi
                                                    .laporan_harian
                                                    .bku_kontrak
                                                    .kontrak
                                                    .nama
                                            }}
                                        </td>

                                        <td class="px-6 py-4 text-sm text-gray-700">
                                            {{
                                                formatDate(
                                                    justifikasi
                                                        .laporan_harian
                                                        .tanggal,
                                                )
                                            }}
                                        </td>

                                        <td class="px-6 py-4 text-right text-sm text-gray-700">
                                            {{
                                                formatNumber(
                                                    justifikasi
                                                        .laporan_harian
                                                        .total_produksi,
                                                )
                                            }}
                                        </td>

                                        <td class="px-6 py-4 text-right text-sm text-gray-700">
                                            {{
                                                formatNumber(
                                                    justifikasi
                                                        .laporan_harian
                                                        .total_lifting,
                                                )
                                            }}
                                        </td>

                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium" :class="{
                                                'bg-yellow-100 text-yellow-700':
                                                    justifikasi.status ===
                                                    'pending',

                                                'bg-green-100 text-green-700':
                                                    justifikasi.status ===
                                                    'approved',

                                                'bg-red-100 text-red-700':
                                                    justifikasi.status ===
                                                    'rejected',
                                            }">
                                                {{
                                                    statusLabel(
                                                        justifikasi.status,
                                                    )
                                                }}
                                            </span>
                                        </td>

                                        <td class="px-6 py-4 text-right">
                                            <div v-if="
                                                justifikasi.status ===
                                                'pending'
                                            " class="flex justify-end gap-2">
                                                <Button size="sm" @click="
                                                    openProcessDialog(
                                                        justifikasi,
                                                        'approved',
                                                    )
                                                    ">
                                                    Setujui
                                                </Button>

                                                <Button size="sm" variant="destructive" @click="
                                                    openProcessDialog(
                                                        justifikasi,
                                                        'rejected',
                                                    )
                                                    ">
                                                    Tolak
                                                </Button>
                                            </div>

                                            <span v-else class="text-sm text-gray-500">
                                                {{
                                                    justifikasi
                                                        .peninjau
                                                        ?.name ??
                                                    '-'
                                                }}
                                            </span>
                                        </td>
                                    </tr>

                                    <tr v-if="
                                        justifikasis.data
                                            .length === 0
                                    ">
                                        <td colspan="8" class="px-6 py-12 text-center text-sm text-gray-500">
                                            Belum ada pengajuan
                                            justifikasi.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>

                <!-- Pagination -->
                <div v-if="
                    justifikasis.last_page > 1
                " class="mt-6 flex flex-wrap justify-center gap-2">
                    <Button v-for="link in justifikasis.links" :key="link.label" variant="outline" size="sm"
                        :disabled="!link.url" :class="{
                            'bg-gray-100':
                                link.active,
                        }" @click="
                            link.url &&
                            router.get(
                                link.url,
                                {},
                                {
                                    preserveState: true,
                                    preserveScroll: true,
                                },
                            )
                            ">
                        <span v-html="link.label" />
                    </Button>
                </div>
            </div>
        </div>

        <!-- Process Dialog -->
        <Dialog v-model:open="showProcessDialog">
            <DialogContent class="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>
                        {{
                            processStatus ===
                                'approved'
                                ? 'Setujui Justifikasi'
                                : 'Tolak Justifikasi'
                        }}
                    </DialogTitle>

                    <DialogDescription>
                        {{
                            processStatus ===
                                'approved'
                                ? 'Data laporan harian akan diperbarui sesuai nilai usulan.'
                                : 'Pengajuan justifikasi akan ditolak.'
                        }}
                    </DialogDescription>
                </DialogHeader>

                <div v-if="selectedJustifikasi" class="space-y-4">
                    <div class="rounded-lg border bg-gray-50 p-4">
                        <p class="text-sm font-medium text-gray-700">
                            Alasan Revisi
                        </p>

                        <p class="mt-1 text-sm text-gray-600">
                            {{
                                selectedJustifikasi.alasan_revisi
                            }}
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-gray-500">
                                Produksi Usulan
                            </p>

                            <p class="mt-1 font-medium">
                                {{
                                    formatNumber(
                                        selectedJustifikasi.produksi_usulan,
                                    )
                                }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-gray-500">
                                Lifting Usulan
                            </p>

                            <p class="mt-1 font-medium">
                                {{
                                    formatNumber(
                                        selectedJustifikasi.lifting_usulan,
                                    )
                                }}
                            </p>
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">
                            Catatan Dinas
                        </label>

                        <Textarea v-model="catatanDinas" rows="4" placeholder="Tambahkan catatan..." />
                    </div>
                </div>

                <DialogFooter>
                    <Button variant="outline" @click="
                        showProcessDialog = false
                        ">
                        Batal
                    </Button>

                    <Button :variant="processStatus ===
                        'rejected'
                        ? 'destructive'
                        : 'default'
                        " @click="processJustifikasi">
                        {{
                            processStatus ===
                                'approved'
                                ? 'Setujui'
                                : 'Tolak'
                        }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>