<script setup lang="ts">
import { computed, reactive, ref, Ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';

import AppLayout from '@/layouts/AppLayout.vue';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { DateValue } from 'reka-ui';
import { CalendarIcon } from '@lucide/vue'
import { cn } from '@/lib/utils'
import { Calendar } from '@/components/ui/calendar'
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover'
import { DateFormatter, getLocalTimeZone, parseDate, today } from '@internationalized/date';
import Label from '@/components/ui/label/Label.vue';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import SelectGroup from '@/components/ui/select/SelectGroup.vue';
import SelectLabel from '@/components/ui/select/SelectLabel.vue';

interface Bku {
    id: number;
    nama: string;
}

interface Kontrak {
    id: number;
    nama: string;
}

interface Stats {
    total_bku: number;
    total_kontrak: number;
    average_produksi: number;
    average_lifting: number;
}

interface ProductionChartItem {
    tanggal: string;
    total_produksi: number;
}

interface ProductionLiftingItem {
    tanggal: string;
    total_produksi: number;
    total_lifting: number;
}

interface ProductionByBku {
    id: number;
    nama: string;
    total_produksi: number;
}

interface ReportingStatus {
    total_bku: number;
    sudah_melapor: number;
    belum_melapor: number;
    percentage: number;
}

interface MissingReport {
    id: number;
    nama: string;
    missing_count: number;
    contracts: {
        id: number;
        nama: string;
    }[];
}

interface LatestReport {
    id: number;
    tanggal: string;
    bku: string;
    kontrak: string;
    total_produksi: number;
    total_lifting: number;
}

const props = defineProps<{
    stats: Stats;
    filters: {
        start_date: string;
        end_date: string;
        bku_id: number | null;
        kontrak_id: number | null;
    };
    bkus: Bku[];
    kontraks: Kontrak[];
    productionChart: ProductionChartItem[];
    productionLiftingChart: ProductionLiftingItem[];
    productionByBku: ProductionByBku[];
    reportingStatus: ReportingStatus;
    missingReports: MissingReport[];
    latestReports: LatestReport[];
}>();

const defaultPlaceholder = today(getLocalTimeZone())

const start_date = ref<DateValue>(
    props.filters.start_date
        ? parseDate(props.filters.start_date)
        : today(getLocalTimeZone()),
) as Ref<DateValue>;

const end_date = ref<DateValue>(
    props.filters.end_date
        ? parseDate(props.filters.end_date)
        : today(getLocalTimeZone()),
) as Ref<DateValue>;

const updateStartDate = (value: DateValue | undefined) => {
    if (!value) {
        return;
    }

    start_date.value = value;
    form.start_date = value.toString();
};

const updateEndDate = (value: DateValue | undefined) => {
    if (!value) {
        return;
    }

    end_date.value = value;
    form.end_date = value.toString();
};

const startDateLabel = computed(() => {
    if (!start_date.value) {
        return 'Pilih tanggal';
    }

    return start_date.value.toString();
});

const endDateLabel = computed(() => {
    if (!end_date.value) {
        return 'Pilih tanggal';
    }

    return end_date.value.toString();
});

const formatter = new DateFormatter('id-ID', {
    dateStyle: 'long',
});

const startDatePlaceholder = computed(() => start_date.value);

const endDatePlaceholder = computed(() => end_date.value);

const form = reactive({
    start_date: props.filters.start_date,
    end_date: props.filters.end_date,
    bku_id: props.filters.bku_id as number | null,
    kontrak_id: props.filters.kontrak_id as number | null,
});

const kontrakModel = computed({
    get: () => (form.kontrak_id !== null ? String(form.kontrak_id) : 'all'),
    set: (value: string) => {
        form.kontrak_id = value === 'all' ? null : Number(value);
    },
});

const bkuModel = computed({
    get: () => (form.bku_id !== null ? String(form.bku_id) : 'all'),
    set: (value: string) => {
        form.bku_id = value === 'all' ? null : Number(value);
    },
});

const formatNumber = (value: number): string => {
    return new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    }).format(value);
};

const formatDate = (value: string | null | undefined): string => {
    if (!value) {
        return '-';
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return '-';
    }

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(date);
};

const applyFilter = () => {
    router.get(
        route('dashboard'),
        {
            start_date: form.start_date,
            end_date: form.end_date,
            bku_id: form.bku_id || undefined,
            kontrak_id: form.kontrak_id || undefined,
        },
        {
            preserveScroll: true,
            preserveState: true,
        },
    );
};

const resetFilter = () => {
    form.start_date = props.filters.start_date;
    form.end_date = props.filters.end_date;
    form.bku_id = null;
    form.kontrak_id = null;

    router.get(route('dashboard'), {}, {
        preserveScroll: true,
    });
};

const maxProduction = computed(() => {
    return Math.max(
        ...props.productionChart.map((item) => item.total_produksi),
        1,
    );
});

const maxBkuProduction = computed(() => {
    return Math.max(
        ...props.productionByBku.map((item) => item.total_produksi),
        1,
    );
});
</script>

<template>

    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="[
        {
            title: 'Dashboard',
            href: route('dashboard'),
        },
    ]">
        <div class="py-6">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">

                <!-- Header -->
                <div>
                    <h2 class="text-xl font-semibold text-gray-800">
                        Dashboard
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        Ringkasan produksi dan pelaporan harian minyak bumi.
                    </p>
                </div>

                <!-- KPI -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">

                    <Card>
                        <CardHeader class="pb-2">
                            <CardTitle class="text-sm font-medium text-gray-500">
                                Jumlah BKU
                            </CardTitle>
                        </CardHeader>

                        <CardContent>
                            <div class="text-2xl font-bold">
                                {{ formatNumber(stats.total_bku) }}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader class="pb-2">
                            <CardTitle class="text-sm font-medium text-gray-500">
                                Jumlah Kontrak
                            </CardTitle>
                        </CardHeader>

                        <CardContent>
                            <div class="text-2xl font-bold">
                                {{ formatNumber(stats.total_kontrak) }}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader class="pb-2">
                            <CardTitle class="text-sm font-medium text-gray-500">
                                Rata-rata Produksi
                            </CardTitle>
                        </CardHeader>

                        <CardContent>
                            <div class="text-2xl font-bold">
                                {{ formatNumber(stats.average_produksi) }}
                            </div>

                            <p class="mt-1 text-xs text-gray-500">
                                per hari
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader class="pb-2">
                            <CardTitle class="text-sm font-medium text-gray-500">
                                Rata-rata Lifting
                            </CardTitle>
                        </CardHeader>

                        <CardContent>
                            <div class="text-2xl font-bold">
                                {{ formatNumber(stats.average_lifting) }}
                            </div>

                            <p class="mt-1 text-xs text-gray-500">
                                per hari
                            </p>
                        </CardContent>
                    </Card>

                </div>

                <!-- Filter -->
                <Card>
                    <CardHeader>
                        <CardTitle>Filter Dashboard</CardTitle>
                    </CardHeader>

                    <CardContent>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">

                            <div>
                                <Label class="mb-2 block text-sm font-medium">
                                    Dari Tanggal
                                </Label>

                                <Popover>
                                    <PopoverTrigger as-child>
                                        <Button variant="outline" :class="cn(
                                            'w-[280px] justify-start text-left font-normal',
                                            !start_date && 'text-muted-foreground',
                                        )">
                                            <CalendarIcon class="mr-2 h-4 w-4" />
                                            {{ startDateLabel }}
                                        </Button>
                                    </PopoverTrigger>
                                    <PopoverContent class="w-auto p-0">
                                        <Calendar v-model="start_date" :initial-focus="true"
                                            :default-placeholder="defaultPlaceholder" layout="month-and-year"
                                            @update:model-value="updateStartDate" />
                                    </PopoverContent>
                                </Popover>
                            </div>

                            <div>
                                <Label class="mb-2 block text-sm font-medium">
                                    Sampai Tanggal
                                </Label>
                                <Popover>
                                    <PopoverTrigger as-child>
                                        <Button variant="outline" :class="cn(
                                            'w-[280px] justify-start text-left font-normal',
                                            !end_date && 'text-muted-foreground',
                                        )">
                                            <CalendarIcon class="mr-2 h-4 w-4" />
                                            {{ endDateLabel }}
                                        </Button>
                                    </PopoverTrigger>
                                    <PopoverContent class="w-auto p-0">
                                        <Calendar v-model="end_date" :initial-focus="true"
                                            :default-placeholder="defaultPlaceholder" layout="month-and-year"
                                            @update:model-value="updateEndDate" />
                                    </PopoverContent>
                                </Popover>
                            </div>

                            <div>
                                <Label class="mb-2 block text-sm font-medium">
                                    BKU
                                </Label>

                                <Select v-model="bkuModel">
                                    <SelectTrigger class="w-[280px]">
                                        <SelectValue placeholder="Pilih BKU" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            <SelectItem value="all">Semua BKU</SelectItem>
                                            <SelectItem v-for="bku in bkus" :key="bku.id" :value="String(bku.id)">
                                                {{ bku.nama }}
                                            </SelectItem>
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div>
                                <Label class="mb-2 block text-sm font-medium">
                                    Kontrak
                                </Label>

                                <Select v-model="kontrakModel">
                                    <SelectTrigger class="w-[280px]">
                                        <SelectValue placeholder="Pilih Kontrak" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            <SelectItem value="all">Semua Kontrak</SelectItem>
                                            <SelectItem v-for="kontrak in kontraks" :key="kontrak.id" :value="String(kontrak.id)">
                                                {{ kontrak.nama }}
                                            </SelectItem>
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <div class="mt-4 flex justify-end gap-2">
                            <Button type="button" variant="outline" @click="resetFilter">
                                Reset
                            </Button>

                            <Button type="button" @click="applyFilter">
                                Terapkan Filter
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <!-- Grafik Produksi -->
                <Card>
                    <CardHeader>
                        <CardTitle>Produksi Harian</CardTitle>
                    </CardHeader>

                    <CardContent>
                        <div v-if="productionChart.length === 0"
                            class="flex h-64 items-center justify-center text-sm text-gray-500">
                            Belum ada data produksi pada periode ini.
                        </div>

                        <div v-else class="space-y-3">
                            <div v-for="item in productionChart" :key="item.tanggal"
                                class="grid grid-cols-[90px_1fr_100px] items-center gap-3">
                                <span class="text-xs text-gray-500">
                                    {{ formatDate(item.tanggal) }}
                                </span>

                                <div class="h-7 overflow-hidden rounded bg-gray-100">
                                    <div class="h-full rounded bg-gray-800 transition-all" :style="{
                                        width: `${(item.total_produksi / maxProduction) * 100}%`,
                                    }" />
                                </div>

                                <span class="text-right text-sm font-medium">
                                    {{ formatNumber(item.total_produksi) }}
                                </span>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Produksi vs Lifting -->
                <Card>
                    <CardHeader>
                        <CardTitle>Produksi vs Lifting</CardTitle>
                    </CardHeader>

                    <CardContent>
                        <div v-if="productionLiftingChart.length === 0"
                            class="flex h-40 items-center justify-center text-sm text-gray-500">
                            Belum ada data.
                        </div>

                        <div v-else class="overflow-x-auto">
                            <table class="w-full min-w-[600px]">
                                <thead>
                                    <tr class="border-b text-left text-sm">
                                        <th class="px-3 py-3 font-medium">
                                            Tanggal
                                        </th>

                                        <th class="px-3 py-3 font-medium">
                                            Produksi
                                        </th>

                                        <th class="px-3 py-3 font-medium">
                                            Lifting
                                        </th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <tr v-for="item in productionLiftingChart" :key="item.tanggal"
                                        class="border-b last:border-0">
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

                <!-- Produksi per BKU + Status -->
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

                    <Card>
                        <CardHeader>
                            <CardTitle>Produksi Berdasarkan BKU</CardTitle>
                        </CardHeader>

                        <CardContent>
                            <div v-if="productionByBku.length === 0" class="py-10 text-center text-sm text-gray-500">
                                Belum ada data produksi.
                            </div>

                            <div v-else class="space-y-5">
                                <div v-for="item in productionByBku" :key="item.id">
                                    <div class="mb-2 flex justify-between text-sm">
                                        <span class="font-medium">
                                            {{ item.nama }}
                                        </span>

                                        <span>
                                            {{ formatNumber(item.total_produksi) }}
                                        </span>
                                    </div>

                                    <div class="h-2 rounded-full bg-gray-100">
                                        <div class="h-2 rounded-full bg-gray-800" :style="{
                                            width: `${(item.total_produksi / maxBkuProduction) * 100}%`,
                                        }" />
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>
                                Status Pelaporan Hari Ini
                            </CardTitle>
                        </CardHeader>

                        <CardContent>
                            <div class="grid grid-cols-3 gap-3 text-center">

                                <div class="rounded-lg border p-4">
                                    <p class="text-2xl font-bold">
                                        {{ reportingStatus.total_bku }}
                                    </p>

                                    <p class="mt-1 text-xs text-gray-500">
                                        Total BKU
                                    </p>
                                </div>

                                <div class="rounded-lg border p-4">
                                    <p class="text-2xl font-bold text-green-600">
                                        {{ reportingStatus.sudah_melapor }}
                                    </p>

                                    <p class="mt-1 text-xs text-gray-500">
                                        Lengkap
                                    </p>
                                </div>

                                <div class="rounded-lg border p-4">
                                    <p class="text-2xl font-bold text-red-600">
                                        {{ reportingStatus.belum_melapor }}
                                    </p>

                                    <p class="mt-1 text-xs text-gray-500">
                                        Belum Lengkap
                                    </p>
                                </div>

                            </div>

                            <div class="mt-5">
                                <div class="mb-2 flex justify-between text-sm">
                                    <span>
                                        Kelengkapan laporan
                                    </span>

                                    <span class="font-medium">
                                        {{ reportingStatus.percentage }}%
                                    </span>
                                </div>

                                <div class="h-2 rounded-full bg-gray-100">
                                    <div class="h-2 rounded-full bg-green-600" :style="{
                                        width: `${reportingStatus.percentage}%`,
                                    }" />
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                </div>

                <!-- BKU Belum Lengkap -->
                <Card>
                    <CardHeader>
                        <CardTitle>
                            BKU Belum Lengkap Melapor
                        </CardTitle>
                    </CardHeader>

                    <CardContent>
                        <div v-if="missingReports.length === 0"
                            class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                            Semua BKU sudah melaporkan seluruh kontraknya hari ini.
                        </div>

                        <div v-else class="space-y-3">
                            <div v-for="item in missingReports" :key="item.id" class="rounded-lg border p-4">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium">
                                        {{ item.nama }}
                                    </span>

                                    <span class="text-sm text-red-600">
                                        {{ item.missing_count }} kontrak belum melapor
                                    </span>
                                </div>

                                <div class="mt-3 flex flex-wrap gap-2">
                                    <span v-for="contract in item.contracts" :key="contract.id"
                                        class="rounded-md bg-gray-100 px-2 py-1 text-xs text-gray-700">
                                        {{ contract.nama }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Laporan Terbaru -->
                <Card>
                    <CardHeader>
                        <CardTitle>Laporan Terbaru</CardTitle>
                    </CardHeader>

                    <CardContent>
                        <div v-if="latestReports.length === 0" class="py-10 text-center text-sm text-gray-500">
                            Belum ada laporan.
                        </div>

                        <div v-else class="overflow-x-auto">
                            <table class="w-full min-w-[800px]">
                                <thead>
                                    <tr class="border-b text-left text-sm">
                                        <th class="px-3 py-3 font-medium">
                                            Tanggal
                                        </th>

                                        <th class="px-3 py-3 font-medium">
                                            BKU
                                        </th>

                                        <th class="px-3 py-3 font-medium">
                                            Kontrak
                                        </th>

                                        <th class="px-3 py-3 text-right font-medium">
                                            Produksi
                                        </th>

                                        <th class="px-3 py-3 text-right font-medium">
                                            Lifting
                                        </th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <tr v-for="item in latestReports" :key="item.id" class="border-b last:border-0">
                                        <td class="px-3 py-3 text-sm">
                                            {{ formatDate(item.tanggal) }}
                                        </td>

                                        <td class="px-3 py-3 text-sm">
                                            {{ item.bku }}
                                        </td>

                                        <td class="px-3 py-3 text-sm">
                                            {{ item.kontrak }}
                                        </td>

                                        <td class="px-3 py-3 text-right text-sm">
                                            {{ formatNumber(item.total_produksi) }}
                                        </td>

                                        <td class="px-3 py-3 text-right text-sm">
                                            {{ formatNumber(item.total_lifting) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>

            </div>
        </div>
    </AppLayout>
</template>
