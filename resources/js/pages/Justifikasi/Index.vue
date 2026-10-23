<script setup lang="ts">
import { computed, ref } from 'vue';
import { CalendarDate, DateFormatter, DateValue, getLocalTimeZone } from '@internationalized/date';
import { Head, router, usePage } from '@inertiajs/vue3';

import AppLayout from '@/layouts/AppLayout.vue';

import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';

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
import { BreadcrumbItem, SharedData } from '@/types';
import Label from '@/Components/ui/label/Label.vue';
import { CalendarIcon } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Justifikasi Revisi',
        href: route('justifikasi.index'),
    }
];

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
        tanggal?: string;
    };
}>();

const selectedStatus = ref(
    props.filters.status ?? 'all'
);

const df = new DateFormatter('id-ID', { dateStyle: 'long' });

const parseToCalendarDate = (str?: string): DateValue | undefined => {
    if (!str) return undefined;
    const [y, m, d] = str.split('-').map(Number);
    return new CalendarDate(y, m, d);
};

// eslint-disable-next-line @typescript-eslint/no-explicit-any
const selectedDate = ref<any>(
    parseToCalendarDate(props.filters.tanggal)
);

const displayDate = computed(() =>
    selectedDate.value
        ? df.format(selectedDate.value.toDate(getLocalTimeZone()))
        : 'Pilih tanggal'
);

const showProcessDialog = ref(false);

const selectedJustifikasi =
    ref<Justifikasi | null>(null);

const processStatus = ref<
    'approved' | 'rejected'
>('approved');

const catatanDinas = ref('');
const catatanError = ref('');

const openProcessDialog = (
    justifikasi: Justifikasi,
) => {
    selectedJustifikasi.value = justifikasi;
    catatanDinas.value =
        justifikasi.catatan_dinas ?? '';
    catatanError.value = '';

    showProcessDialog.value = true;
};

const handleProcess = (
    status: 'approved' | 'rejected',
) => {
    if (!catatanDinas.value.trim()) {
        catatanError.value = 'Catatan wajib diisi.';
        return;
    }
    catatanError.value = '';
    processStatus.value = status;
    processJustifikasi();
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

const toDateString = (d?: DateValue): string | undefined => {
    if (!d) return undefined;
    const mm = String(d.month).padStart(2, '0');
    const dd = String(d.day).padStart(2, '0');
    return `${d.year}-${mm}-${dd}`;
};

const applyFilter = () => {
    router.get(
        route('justifikasi.index'),
        {
            status:
                selectedStatus.value === 'all'
                    ? undefined
                    : selectedStatus.value,
            tanggal: toDateString(selectedDate.value),
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};

const resetFilter = () => {
    selectedStatus.value = 'all';
    selectedDate.value = undefined;
    router.get(
        route('justifikasi.index'),
        {},
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

const page = usePage<SharedData>();

const user = computed(() => page.props.auth.user);

const isStafEsdm = computed(() => {
    return user.value?.role === 'staf_dinas';
});
</script>

<template>

    <Head title="Justifikasi Revisi" />

    <AppLayout :breadcrumbs="breadcrumbs">
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
                                <Label class="mb-2 block text-sm font-medium text-gray-700">
                                    Status
                                </Label>

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
                            <div class="w-full sm:w-56">
                                <Label class="mb-2 block text-sm font-medium text-gray-700">
                                    Tanggal
                                </Label>
                                <Popover>
                                    <PopoverTrigger as-child>
                                        <Button variant="outline" class="w-full justify-start text-left font-normal"
                                            :class="!selectedDate && 'text-muted-foreground'">
                                            <CalendarIcon class="mr-2 h-4 w-4" />
                                            {{ displayDate }}
                                        </Button>
                                    </PopoverTrigger>
                                    <PopoverContent class="w-auto p-0">
                                        <Calendar v-model="selectedDate" locale="id-ID" initial-focus />
                                    </PopoverContent>
                                </Popover>
                            </div>

                            <!-- Tombol Filter -->
                            <div class="flex gap-2 sm:ml-auto">
                                <Button variant="outline" @click="resetFilter">
                                    Reset
                                </Button>

                                <Button @click="applyFilter">
                                    Terapkan
                                </Button>

                            </div>
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

                                        <th class="px-6 py-3 text-center text-sm font-semibold text-gray-700">
                                            Peninjau
                                        </th>
                                        <th class="px-6 py-3 text-center text-sm font-semibold text-gray-700">
                                            Aksi
                                        </th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-gray-200">
                                    <tr v-for="(justifikasi, index
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

                                        <td class="px-6 py-4 text-center text-sm text-gray-700">
                                            {{
                                                justifikasi
                                                    .peninjau
                                                    ?.name ??
                                                '-'
                                            }}

                                        </td>
                                        <td class="px-6 py-4 text-right" v-if="isStafEsdm">
                                            <div v-if="
                                                justifikasi.status ===
                                                'pending'
                                            " class="flex justify-end gap-2">
                                                <Button size="sm" class="bg-yellow-500 hover:bg-yellow-600"
                                                    @click="openProcessDialog(justifikasi)">
                                                    Konfirmasi
                                                </Button>
                                            </div>

                                            <!-- <span v-else class="text-sm text-gray-500">
                                                {{
                                                    justifikasi
                                                        .peninjau
                                                        ?.name ?? 
                                                    '-'
                                                }}
                                            </span> -->
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
                        Konfirmasi Justifikasi
                    </DialogTitle>

                    <DialogDescription>
                        Tinjau detail pengajuan di bawah ini, lalu pilih tindakan yang sesuai.
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

                    <div dir="ltr ">
                        <!-- Total Produksi dan Lifting awal -->
                        <div class="ps-2 grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-gray-500">
                                    Total Produksi
                                </p>
    
                                <p class="mt-1 font-medium">
                                    {{
                                        formatNumber(
                                            selectedJustifikasi.laporan_harian.total_produksi,
                                        )
                                    }}
                                </p>
                            </div>
    
                            <div>
                                <p class="text-xs text-gray-500">
                                    Total Lifting
                                </p>
    
                                <p class="mt-1 font-medium">
                                    {{
                                        formatNumber(
                                            selectedJustifikasi.laporan_harian.total_lifting,
                                        )
                                    }}
                                </p>
                            </div>
                        </div>
                        <br>
                        <!-- Produksi dan Lifting usulan -->
                        <div class="ps-2 grid grid-cols-2 gap-4">
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
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">
                            Catatan Dinas
                            <span class="text-red-500">*</span>
                        </label>

                        <Textarea v-model="catatanDinas" rows="4" placeholder="Tambahkan catatan..."
                            :class="catatanError ? 'border-red-500 focus-visible:ring-red-500' : ''"
                            @input="catatanError = ''" />

                        <p v-if="catatanError" class="mt-1 text-xs text-red-500">
                            {{ catatanError }}
                        </p>
                    </div>
                </div>

                <DialogFooter class="gap-2 sm:gap-2">
                    <Button variant="outline" @click="showProcessDialog = false">
                        Batal
                    </Button>

                    <Button variant="destructive" @click="handleProcess('rejected')">
                        Tolak
                    </Button>

                    <Button @click="handleProcess('approved')" class="bg-green-600 hover:bg-green-900">
                        Setujui
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>