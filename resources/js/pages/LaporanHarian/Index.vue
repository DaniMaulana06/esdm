<script setup lang="ts">
import CreateJustifikasiDialog from '@/components/justifikasi/CreateJustifikasiDialog.vue';
import Button from '@/components/ui/button/Button.vue';
import Calendar from '@/components/ui/calendar/Calendar.vue';
import Input from '@/components/ui/input/Input.vue';
import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationItem,
    PaginationNext,
    PaginationPrevious,
} from '@/components/ui/pagination';
import Popover from '@/components/ui/popover/Popover.vue';
import PopoverContent from '@/components/ui/popover/PopoverContent.vue';
import PopoverTrigger from '@/components/ui/popover/PopoverTrigger.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { cn } from '@/lib/utils';
import { type BreadcrumbItem, type SharedData } from '@/types';
import {
    DateFormatter,
    type DateValue,
    getLocalTimeZone,
    parseDate,
} from '@internationalized/date';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import {
    ArrowDown,
    ArrowUp,
    ArrowUpDown,
    CalendarIcon,
    ChevronDown,
    ChevronRight,
} from 'lucide-vue-next';
import { computed, onMounted, ref, type Ref } from 'vue';
import { route } from 'ziggy-js';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import SelectGroup from '@/components/ui/select/SelectGroup.vue';
import Label from '@/components/ui/label/Label.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Laporan Harian',
        href: route('laporan-harian.index'),
    },
];

interface Bku {
    id: number;
    nama: string;
}

interface Kontrak {
    id: number;
    nama: string;
}

interface BkuKontrak {
    id: number;
    bku_id: number;
    kontrak_id: number;
    jumlah_sumur: number;
    bku: Bku;
    kontrak: Kontrak;
}

interface Justifikasi {
    id: number;
    status: 'pending' | 'approved' | 'rejected';
    alasan_revisi: string;
    produksi_usulan: number | string;
    lifting_usulan: number | string;
    catatan_dinas: string | null;
}

interface LaporanHarian {
    id: number;
    bku_kontrak_id: number;
    tanggal: string;
    total_produksi: string;
    total_lifting: string;
    keterangan: string | null;

    bku_kontrak: BkuKontrak;
    justifikasi_terbaru: Justifikasi | null;
}

// 1 kelompok = 1 BKU pada 1 tanggal, berisi beberapa laporan kontrak
interface LaporanGroup {
    key: string;
    bku: Bku;
    tanggal: string;
    total_produksi: string | number;
    total_lifting: string | number;
    items: LaporanHarian[];
}

interface PaginatedData<T> {
    current_page: number;
    data: T[];
    first_page_url: string;
    from: number | null;
    last_page: number;
    last_page_url: string;
    links: {
        url: string | null;
        label: string;
        active: boolean;
    }[];
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number | null;
    total: number;
}

interface Filters {
    search?: string;
    bku_id?: string;
    tanggal?: string;
    kontrak_id?: string;
    sort?: string;
    direction?: string;
}

/* ------------------------------------------------------------------ */
/* Props & state                                                       */
/* ------------------------------------------------------------------ */

const props = defineProps<{
    // Admin / staf dinas: data terkelompok per BKU + tanggal
    groups?: PaginatedData<LaporanGroup> | null;
    // Operator BKU: data per baris kontrak (tabel biasa)
    laporanHarians?: PaginatedData<LaporanHarian> | null;
    bkuKontraks: BkuKontrak[];
    filters: Filters;
}>();

const search = ref(props.filters.search ?? '');
const bkuId = ref(props.filters.bku_id ?? '');
const kontrakId = ref(props.filters.kontrak_id ?? '');
const tanggal = ref(
    props.filters.tanggal
        ? parseDate(props.filters.tanggal)
        : undefined,
) as Ref<DateValue | undefined>;

const df = new DateFormatter('id-ID', {
    dateStyle: 'long',
});

/* ------------------------------------------------------------------ */
/* Opsi dropdown                                                       */
/* ------------------------------------------------------------------ */

const bkus = computed(() => {
    const uniqueBku = new Map<number, Bku>();

    props.bkuKontraks.forEach((item) => {
        if (item.bku) {
            uniqueBku.set(item.bku.id, item.bku);
        }
    });

    return Array.from(uniqueBku.values());
});

const kontrakOptions = computed(() => {
    const kontraks = props.bkuKontraks
        .map((item) => item.kontrak)
        .filter(Boolean);

    return Array.from(
        new Map(kontraks.map((kontrak) => [kontrak.id, kontrak])).values(),
    );
});

/* ------------------------------------------------------------------ */
/* Filter, sorting, pagination                                         */
/* ------------------------------------------------------------------ */

const currentQuery = () => ({
    search: search.value || undefined,
    bku_id: bkuId.value || undefined,
    kontrak_id: kontrakId.value || undefined,
    tanggal: tanggal.value ? tanggal.value.toString() : undefined,
    sort: props.filters.sort || undefined,
    direction: props.filters.direction || undefined,
});

const applyFilter = () => {
    router.get(route('laporan-harian.index'), currentQuery(), {
        preserveState: false,
        preserveScroll: true,
        replace: true,
    });
};

const resetFilter = () => {
    search.value = '';
    bkuId.value = '';
    kontrakId.value = '';
    tanggal.value = undefined;

    router.get(
        route('laporan-harian.index'),
        {},
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

const sortBy = (column: string) => {
    let direction = 'asc';

    if (props.filters.sort === column) {
        direction = props.filters.direction === 'asc' ? 'desc' : 'asc';
    }

    router.get(
        route('laporan-harian.index'),
        {
            ...currentQuery(),
            sort: column,
            direction,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

const getSortIcon = (column: string) => {
    if (props.filters.sort !== column) {
        return ArrowUpDown;
    }

    return props.filters.direction === 'asc' ? ArrowUp : ArrowDown;
};

const goToPage = (page: number) => {
    router.get(
        route('laporan-harian.index'),
        {
            ...currentQuery(),
            page,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};

/* ------------------------------------------------------------------ */
/* Buka / tutup kelompok                                               */
/* ------------------------------------------------------------------ */

const expandedKeys = ref<string[]>([]);

const isExpanded = (key: string) => expandedKeys.value.includes(key);

const toggleGroup = (key: string) => {
    expandedKeys.value = isExpanded(key)
        ? expandedKeys.value.filter((k) => k !== key)
        : [...expandedKeys.value, key];
};

const allExpanded = computed(
    () =>
        !!props.groups &&
        props.groups.data.length > 0 &&
        props.groups.data.every((group) => isExpanded(group.key)),
);

const toggleAll = () => {
    expandedKeys.value = allExpanded.value
        ? []
        : (props.groups?.data.map((group) => group.key) ?? []);
};

/* ------------------------------------------------------------------ */
/* Helper tampilan                                                     */
/* ------------------------------------------------------------------ */

const formatTanggal = (value: string) => {
    return new Date(value).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    });
};

const formatAngka = (value: number | string) =>
    Number(value).toLocaleString('id-ID');

const statusMeta = (justifikasi: Justifikasi | null) => {
    if (!justifikasi) {
        return {
            label: 'Belum diajukan',
            class: 'bg-gray-100 text-gray-600',
        };
    }

    return {
        pending: {
            label: 'Menunggu Persetujuan',
            class: 'bg-yellow-100 text-yellow-700',
        },
        approved: {
            label: 'Disetujui',
            class: 'bg-green-100 text-green-700',
        },
        rejected: {
            label: 'Ditolak',
            class: 'bg-red-100 text-red-700',
        },
    }[justifikasi.status];
};

/* ------------------------------------------------------------------ */
/* Role, flash, aksi                                                   */
/* ------------------------------------------------------------------ */

const page = usePage<SharedData>();

const user = computed(() => page.props.auth.user);

const isOperatorBku = computed(() => user.value?.role === 'operator_bku');

const isStafEsdmOrAdmin = computed(() =>
    ['admin', 'staf_dinas'].includes(user.value?.role),
);

const isStafEsdm = computed(() => user.value?.role === 'staf_dinas');

const flash = computed(
    () =>
        page.props.flash as {
            success?: string;
            error?: string;
        },
);

const showFlash = ref(true);

onMounted(() => {
    if (flash.value.success || flash.value.error) {
        setTimeout(() => {
            showFlash.value = false;
        }, 3000);
    }
});

const closeFlash = () => {
    showFlash.value = false;
};

const deleteItem = (id: number) => {
    if (confirm('Apakah kamu yakin ingin menghapus Laporan ini?')) {
        router.delete(
            route('laporan-harian.destroy', {
                laporan_harian: id,
            }),
        );
    }
};

// Meta pagination yang dipakai: tergantung tabel yang aktif
const pager = computed(() =>
    isStafEsdmOrAdmin.value ? props.groups : props.laporanHarians,
);

/* ------------------------------------------------------------------ */
/* Dialog justifikasi                                                  */
/* ------------------------------------------------------------------ */

const showJustifikasi = ref(false);
const selectedLaporan = ref<LaporanHarian | null>(null);

const openJustifikasi = (laporan: LaporanHarian) => {
    selectedLaporan.value = laporan;
    showJustifikasi.value = true;
};
</script>

<template>

    <Head title="Laporan Harian" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <!-- HEADER -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">
                        Laporan Harian
                    </h1>

                    <p class="mt-1 text-sm text-gray-600">
                        Lapor produksi dan lifting harian.
                    </p>
                </div>

                <Link v-if="isOperatorBku" :href="route('laporan-harian.create')" prefetch
                    class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    + Buat Laporan Harian
                </Link>
            </div>

            <!-- FLASH SUCCESS -->
            <div v-if="showFlash && flash.success"
                class="mb-4 flex items-center justify-between rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-700">
                <span>{{ flash.success }}</span>

                <button type="button" class="ml-4 text-lg font-bold text-green-700 hover:text-green-900"
                    @click="closeFlash">
                    ×
                </button>
            </div>

            <!-- FLASH ERROR -->
            <div v-if="showFlash && flash.error"
                class="mb-4 flex items-center justify-between rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700">
                <span>{{ flash.error }}</span>

                <button type="button" class="ml-4 text-lg font-bold text-red-700 hover:text-red-900"
                    @click="closeFlash">
                    ×
                </button>
            </div>

            <!-- FILTER -->
            <div class="mb-4 rounded-lg border bg-card p-4 shadow-sm">
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <!-- Search -->
                    <div v-if="isStafEsdmOrAdmin" class="lg:col-span-2">
                        <Label for="search" class="mb-2 block text-sm font-medium">
                            Cari
                        </Label>

                        <Input id="search" v-model="search" type="text" placeholder="Cari BKU atau kontrak..."
                            @keyup.enter="applyFilter" />
                    </div>

                    <!-- BKU -->
                    <div v-if="isStafEsdmOrAdmin">
                        <Label for="bku" class="mb-2 block text-sm font-medium">
                            BKU
                        </Label>
                        
                        <Select v-model="bkuId">
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

                    <!-- Kontrak (semua role) -->
                    <div>
                        <Label for="kontrak" class="mb-2 block text-sm font-medium">
                            Kontrak
                        </Label>
                        <Select v-model="kontrakId">
                            <SelectTrigger class="w-[280px]">
                                <SelectValue placeholder="Pilih Kontrak" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectGroup>
                                    <SelectItem value="all">Semua Kontrak</SelectItem>
                                    <SelectItem v-for="kontrak in kontrakOptions" :key="kontrak.id"
                                        :value="String(kontrak.id)">
                                        {{ kontrak.nama }}
                                    </SelectItem>
                                </SelectGroup>
                            </SelectContent>
                        </Select>
                    </div>

                    <!-- Tanggal -->
                    <div>
                        <label class="mb-2 block text-sm font-medium">
                            Tanggal
                        </label>

                        <Popover>
                            <PopoverTrigger as-child>
                                <Button variant="outline" :class="cn(
                                    'w-[280px] justify-start text-left font-normal',
                                    !tanggal && 'text-muted-foreground',
                                )
                                    ">
                                    <CalendarIcon class="mr-2 h-4 w-4" />
                                    {{
                                        tanggal
                                            ? df.format(
                                                tanggal.toDate(
                                                    getLocalTimeZone(),
                                                ),
                                            )
                                            : 'Pilih tanggal'
                                    }}
                                </Button>
                            </PopoverTrigger>

                            <PopoverContent class="w-auto p-0">
                                <Calendar v-model="tanggal" :initial-focus="true" layout="month-and-year" />
                            </PopoverContent>
                        </Popover>
                    </div>
                </div>

                <div class="mt-4 flex justify-end gap-2 items-end">
                    <Button type="button" variant="outline" @click="resetFilter">
                        Reset
                    </Button>
                    <Button type="button" @click="applyFilter">Cari</Button>
                </div>
            </div>

            <!-- TOMBOL BUKA/TUTUP SEMUA -->
            <div v-if="isStafEsdmOrAdmin && groups && groups.data.length > 0" class="mb-2 flex justify-end">
                <Button type="button" variant="outline" size="sm" @click="toggleAll">
                    {{ allExpanded ? 'Tutup semua' : 'Buka semua' }}
                </Button>
            </div>

            <!-- TABEL TERKELOMPOK (admin & staf dinas) -->
            <div v-if="isStafEsdmOrAdmin && groups" class="overflow-hidden rounded-lg border bg-white shadow-sm">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="w-12 px-6 py-3 text-left text-sm font-semibold text-gray-700">
                                No
                            </th>

                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">
                                BKU / Kontrak
                            </th>

                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">
                                <button type="button" class="flex items-center gap-2 font-semibold hover:text-gray-900"
                                    @click="sortBy('tanggal')">
                                    Tanggal
                                    <component :is="getSortIcon('tanggal')" class="h-4 w-4" />
                                </button>
                            </th>

                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">
                                <button type="button" class="flex items-center gap-2 font-semibold hover:text-gray-900"
                                    @click="sortBy('total_produksi')">
                                    Produksi
                                    <component :is="getSortIcon('total_produksi')" class="h-4 w-4" />
                                </button>
                            </th>

                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">
                                <button type="button" class="flex items-center gap-2 font-semibold hover:text-gray-900"
                                    @click="sortBy('total_lifting')">
                                    Lifting
                                    <component :is="getSortIcon('total_lifting')" class="h-4 w-4" />
                                </button>
                            </th>

                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">
                                Status
                            </th>

                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">
                                Keterangan
                            </th>

                            <th class="px-6 py-3 text-center text-sm font-semibold text-gray-700">
                                Aksi
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y">
                        <template v-for="(group, index) in groups.data" :key="group.key">
                            <!-- BARIS INDUK: 1 BKU + tanggal -->
                            <tr class="cursor-pointer bg-white hover:bg-gray-50" @click="toggleGroup(group.key)">
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ (groups.from ?? 1) + index }}
                                </td>

                                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                    <div class="flex items-center gap-2">
                                        <component :is="isExpanded(group.key)
                                            ? ChevronDown
                                            : ChevronRight
                                            " class="h-4 w-4 text-gray-500" />
                                        {{ group.bku?.nama }}
                                        <span
                                            class="rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700">
                                            {{ group.items.length }} kontrak
                                        </span>
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                    {{ formatTanggal(group.tanggal) }}
                                </td>

                                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                    {{ formatAngka(group.total_produksi) }}
                                </td>

                                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                    {{ formatAngka(group.total_lifting) }}
                                </td>

                                <td colspan="3"></td>
                            </tr>

                            <!-- baris kontrak tiap bku -->
                            <template v-if="isExpanded(group.key)">
                                <tr v-for="laporanHarian in group.items" :key="laporanHarian.id" class="bg-gray-50/60">
                                    <td></td>

                                    <td class="py-3 pr-6 pl-16 text-sm text-gray-800">
                                        {{ laporanHarian.bku_kontrak.kontrak.nama }}
                                    </td>

                                    <td></td>

                                    <td class="px-6 py-3 text-sm text-gray-800">
                                        {{ formatAngka(laporanHarian.total_produksi) }}
                                    </td>

                                    <td class="px-6 py-3 text-sm text-gray-800">
                                        {{ formatAngka(laporanHarian.total_lifting) }}
                                    </td>

                                    <td class="px-6 py-3">
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium" :class="statusMeta(
                                            laporanHarian.justifikasi_terbaru,
                                        ).class
                                            ">
                                            {{
                                                statusMeta(
                                                    laporanHarian.justifikasi_terbaru,
                                                ).label
                                            }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-3 text-sm text-gray-800">
                                        {{ laporanHarian.keterangan }}
                                    </td>

                                    <td class="px-6 py-3">
                                        <div class="flex justify-center gap-2">
                                            <Button v-if="isStafEsdm" variant="outline" as-child
                                                class="rounded-md bg-yellow-500 px-3 py-1.5 text-sm text-white hover:bg-yellow-600">
                                                <Link :href="route(
                                                    'laporan-harian.edit',
                                                    {
                                                        laporan_harian:
                                                            laporanHarian.id,
                                                    },
                                                )
                                                    ">
                                                    Edit
                                                </Link>
                                            </Button>

                                            <Button v-if="isStafEsdm"
                                                class="rounded-md bg-red-600 px-3 py-1.5 text-sm text-white hover:bg-red-700"
                                                @click="deleteItem(laporanHarian.id)">
                                                Hapus
                                            </Button>

                                            <Button v-if="isOperatorBku" variant="outline" size="sm"
                                                @click="openJustifikasi(laporanHarian)">
                                                Ajukan Justifikasi
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </template>

                        <tr v-if="groups.data.length === 0">
                            <td colspan="8" class="px-6 py-8 text-center text-sm text-gray-500">
                                Belum ada data Laporan Harian.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- TABEL BIASA (operator BKU) -->
            <div v-if="!isStafEsdmOrAdmin && laporanHarians"
                class="overflow-hidden rounded-lg border bg-white shadow-sm">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">
                                No
                            </th>

                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">
                                Nama Kontrak
                            </th>

                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">
                                <button type="button" class="flex items-center gap-2 font-semibold hover:text-gray-900"
                                    @click="sortBy('tanggal')">
                                    Tanggal
                                    <component :is="getSortIcon('tanggal')" class="h-4 w-4" />
                                </button>
                            </th>

                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">
                                <button type="button" class="flex items-center gap-2 font-semibold hover:text-gray-900"
                                    @click="sortBy('total_produksi')">
                                    Total Produksi
                                    <component :is="getSortIcon('total_produksi')" class="h-4 w-4" />
                                </button>
                            </th>

                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">
                                <button type="button" class="flex items-center gap-2 font-semibold hover:text-gray-900"
                                    @click="sortBy('total_lifting')">
                                    Total Lifting
                                    <component :is="getSortIcon('total_lifting')" class="h-4 w-4" />
                                </button>
                            </th>

                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">
                                Status
                            </th>

                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">
                                Keterangan
                            </th>

                            <th class="px-6 py-3 text-center text-sm font-semibold text-gray-700">
                                Aksi
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y">
                        <tr v-for="(laporanHarian, index) in laporanHarians.data" :key="laporanHarian.id"
                            class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ (laporanHarians.from ?? 1) + index }}
                            </td>

                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                {{ laporanHarian.bku_kontrak.kontrak.nama }}
                            </td>

                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                {{ formatTanggal(laporanHarian.tanggal) }}
                            </td>

                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                {{ laporanHarian.total_produksi }}
                            </td>

                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                {{ laporanHarian.total_lifting }}
                            </td>

                            <td class="px-6 py-4">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium"
                                    :class="statusMeta(laporanHarian.justifikasi_terbaru).class">
                                    {{ statusMeta(laporanHarian.justifikasi_terbaru).label }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                {{ laporanHarian.keterangan }}
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex justify-center gap-2">
                                    <Button v-if="isOperatorBku" variant="outline" size="sm"
                                        @click="openJustifikasi(laporanHarian)">
                                        Ajukan Justifikasi
                                    </Button>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="laporanHarians.data.length === 0">
                            <td colspan="8" class="px-6 py-8 text-center text-sm text-gray-500">
                                Belum ada data Laporan Harian.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION -->
            <div v-if="pager && pager.last_page > 1" class="flex justify-center border-t border-gray-200 px-6 py-4">
                <Pagination v-slot="{ page: activePage }" :items-per-page="pager.per_page" :total="pager.total"
                    :page="pager.current_page" @update:page="goToPage">
                    <PaginationContent v-slot="{ items }">
                        <PaginationPrevious />

                        <template v-for="(item, index) in items" :key="index">
                            <PaginationItem v-if="item.type === 'page'" :value="item.value"
                                :is-active="item.value === activePage">
                                {{ item.value }}
                            </PaginationItem>

                            <PaginationEllipsis v-else :index="index" />
                        </template>

                        <PaginationNext />
                    </PaginationContent>
                </Pagination>
            </div>
        </div>

        <CreateJustifikasiDialog v-model:open="showJustifikasi" :laporan="selectedLaporan" />
    </AppLayout>
</template>