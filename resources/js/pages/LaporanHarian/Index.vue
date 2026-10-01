<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import Calendar from '@/components/ui/calendar/Calendar.vue';
import Input from '@/components/ui/input/Input.vue';
import Popover from '@/components/ui/popover/Popover.vue';
import PopoverContent from '@/components/ui/popover/PopoverContent.vue';
import PopoverTrigger from '@/components/ui/popover/PopoverTrigger.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { cn } from '@/lib/utils';
import { BreadcrumbItem } from '@/types';
import { type SharedData } from '@/types';
import { Head, router, Link, usePage } from '@inertiajs/vue3';
import { ArrowDown, ArrowUp, ArrowUpDown } from 'lucide-vue-next';
import { computed, onMounted, reactive, Ref, ref } from 'vue';
import { route } from 'ziggy-js';
import { CalendarIcon } from 'lucide-vue-next';
import { DateFormatter, DateValue, getLocalTimeZone, parseDate, today } from '@internationalized/date'
import CreateJustifikasiDialog from '@/components/justifikasi/CreateJustifikasiDialog.vue';
import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationItem,
    PaginationNext,
    PaginationPrevious,
} from '@/components/ui/pagination'

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Laporan Harian',
        href: route('laporan-harian.index'),
    }
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

interface Pagination<T> {
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
    kontrakId?: string;
    sort?: string;
    direction?: string;
}

const showJustifikasi = ref(false);

const selectedLaporan = ref<LaporanHarian | null>(null);

const openJustifikasi = (laporan: LaporanHarian) => {
    selectedLaporan.value = laporan;
    showJustifikasi.value = true;
};

const props = defineProps<{
    laporanHarians: Pagination<LaporanHarian>;
    bkuKontraks: BkuKontrak[];
    filters: Filters;
}>();

const search = ref(props.filters.search ?? '');
const bkuId = ref(props.filters.bku_id ?? '');
const tanggal = ref(
    props.filters.tanggal
        ? parseDate(props.filters.tanggal)
        : today(getLocalTimeZone()),
) as Ref<DateValue>;
const kontrakId = ref(props.filters.kontrakId ?? '');

const bkus = computed(() => {
    const uniqueBku = new Map<number, Bku>();

    props.bkuKontraks.forEach((item) => {
        if (item.bku) {
            uniqueBku.set(item.bku.id, item.bku);
        }
    });

    return Array.from(uniqueBku.values());
});

const df = new DateFormatter('id-ID', {
    dateStyle: 'long',
});

const applyFilter = () => {
    router.get(
        route('laporan-harian.index'),
        {
            search: search.value || undefined,
            bku_id: bkuId.value || undefined,
            tanggal: tanggal.value ? tanggal.value.toString() : undefined,
            kontrak_id: kontrakId.value || undefined,

            // Pertahankan sorting yang sedang aktif
            sort: props.filters.sort || undefined,
            direction: props.filters.direction || undefined,
        },
        {
            preserveState: false,
            preserveScroll: true,
            replace: true,
        },
    );
};

const resetFilter = () => {
    search.value = '';
    bkuId.value = '';
    tanggal.value = today(getLocalTimeZone());

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

const kontrakOptions = computed(() => {
    const kontraks = props.bkuKontraks
        .map((item) => item.kontrak)
        .filter(Boolean);

    return Array.from(
        new Map(
            kontraks.map((kontrak) => [
                kontrak.id,
                kontrak,
            ])
        ).values()
    );
});

const sortBy = (column: string) => {
    let direction = 'asc';

    if (props.filters.sort === column) {
        direction =
            props.filters.direction === 'asc'
                ? 'desc'
                : 'asc';
    }

    router.get(
        route('laporan-harian.index'),
        {
            search: search.value || undefined,
            bku_id: bkuId.value || undefined,
            kontrak_id: kontrakId.value || undefined,
            tanggal: tanggal.value ? tanggal.value.toString() : undefined,
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

    return props.filters.direction === 'asc'
        ? ArrowUp
        : ArrowDown;
};

const deleteItem = (id: number) => {
    if (confirm('Apakah kamu yakin ingin menghapus Laporan ini?')) {
        router.delete(route('laporan-harian.destroy', {
            laporan_harian: id,
        })
        );
    }
}


const page = usePage<SharedData>();

const flash = computed(() => page.props.flash as {
    success?: string; error?: string
}
);

const user = computed(() => page.props.auth.user);

const isOperatorBku = computed(() => {
    return user.value?.role === 'operator_bku';
});

const isStafEsdmOrAdmin = computed(() => {
    return ['admin', 'staf_dinas'].includes(user.value?.role);
});

const isStafEsdm = computed(() => {
    return user.value?.role === 'staf_dinas';
});

const formatTanggal = (tanggal: string) => {
    return new Date(tanggal).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    })
}

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

const filters = reactive({
    search: '',
    bku_id: '',
    kontrak_id: '',
    tanggal: '',
    sort: 'tanggal',
    direction: 'desc',
});

const goToPage = (page: number) => {
    router.get(
        route('laporan-harian.index'),
        {
            ...filters,
            page,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};
</script>

<template>

    <Head title="Laporan Harian" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">
                        Laporan Harian
                    </h1>

                    <p class="mt-1 text-sm text-gray-600">
                        Lapor produksi dan lifting harian.
                    </p>
                </div>
                <Link :href="route('laporan-harian.create')" prefetch v-if="isOperatorBku"
                    class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    + Buat Laporan Harian
                </Link>
            </div>

            <!-- FLASH SUCCESS -->
            <div v-if="showFlash && flash.success"
                class="mb-4 flex items-center justify-between rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-700">
                <span>
                    {{ flash.success }}
                </span>

                <button type="button" @click="closeFlash"
                    class="ml-4 text-lg font-bold text-green-700 hover:text-green-900">
                    ×
                </button>
            </div>

            <!-- FLASH ERROR -->
            <div v-if="showFlash && flash.error"
                class="mb-4 flex items-center justify-between rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700">
                <span>
                    {{ flash.error }}
                </span>

                <button type="button" @click="closeFlash"
                    class="ml-4 text-lg font-bold text-red-700 hover:text-red-900">
                    ×
                </button>
            </div>

            <div class="rounded-lg border bg-card p-4 shadow-sm p-2 mb-4">
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <!-- Search -->
                    <div class="lg:col-span-2" v-if="isStafEsdmOrAdmin">
                        <label for="search" class="mb-2 block text-sm font-medium">
                            Cari
                        </label>

                        <Input id="search" v-model="search" type="text" placeholder="Cari BKU atau kontrak..."
                            @keyup.enter="applyFilter" />
                    </div>

                    <!-- BKU -->
                    <div v-if="isStafEsdmOrAdmin">
                        <label for="bku" class="mb-2 block text-sm font-medium">
                            BKU
                        </label>

                        <select id="bku" v-model="bkuId"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring">
                            <option value="">
                                Semua BKU
                            </option>

                            <option v-for="bku in bkus" :key="bku.id" :value="String(bku.id)">
                                {{ bku.nama }}
                            </option>
                        </select>
                    </div>

                    <!-- Kontrak - semua role -->
                    <div>
                        <label for="kontrak" class="mb-2 block text-sm font-medium">
                            Kontrak
                        </label>

                        <select id="kontrak" v-model="kontrakId"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ring">
                            <option value="">
                                Semua Kontrak
                            </option>

                            <option v-for="item in kontrakOptions" :key="item.id" :value="String(item.id)">
                                {{ item.nama }}
                            </option>
                        </select>
                    </div>

                    <!-- Tanggal -->
                    <div>
                        <label for="tanggal" class="mb-2 block text-sm font-medium">
                            Tanggal
                        </label>

                        <!-- <Input id="tanggal" v-model="tanggal" type="date" /> -->
                        <Popover>
                            <PopoverTrigger as-child>
                                <Button variant="outline" :class="cn(
                                    'w-[280px] justify-start text-left font-normal',
                                    !tanggal && 'text-muted-foreground',
                                )">
                                    <CalendarIcon class="mr-2 h-4 w-4" />
                                    {{ tanggal ? df.format(tanggal.toDate(getLocalTimeZone())) : "Pilih tanggal" }}
                                </Button>
                            </PopoverTrigger>
                            <PopoverContent class="w-auto p-0">
                                <Calendar v-model="tanggal" :initial-focus="true" layout="month-and-year" />
                            </PopoverContent>
                        </Popover>
                    </div>
                </div>

                <div class="mt-4 flex gap-2">
                    <Button type="button" @click="applyFilter">
                        Cari
                    </Button>

                    <Button type="button" variant="outline" @click="resetFilter">
                        Reset
                    </Button>
                </div>
            </div>

            <!-- tabel di role operator bku -->
            <div class="overflow-hidden rounded-lg border bg-white shadow-sm" v-if="!isStafEsdmOrAdmin">
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

                            <th class="px-6 py-3 text-center text-sm font-semibold text-gray-700">
                                Status
                            </th>

                            <th class="px-6 py-3 text-center text-sm font-semibold text-gray-700">
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
                                {{ (laporanHarians.current_page - 1) * laporanHarians.per_page + index + 1 }}
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
                                <span v-if="!laporanHarian.justifikasi_terbaru"
                                    class="inline-flex rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-600">
                                    Belum diajukan
                                </span>

                                <span v-else-if="laporanHarian.justifikasi_terbaru.status === 'pending'"
                                    class="inline-flex rounded-full bg-yellow-100 px-2 py-1 text-xs font-medium text-yellow-700">
                                    Menunggu Persetujuan
                                </span>

                                <span v-else-if="laporanHarian.justifikasi_terbaru.status === 'approved'"
                                    class="inline-flex rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-700">
                                    Disetujui
                                </span>

                                <span v-else-if="laporanHarian.justifikasi_terbaru.status === 'rejected'"
                                    class="inline-flex rounded-full bg-red-100 px-2.5 py-1 text-xs font-medium text-red-700">
                                    Ditolak
                                </span>
                            </td>

                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                {{ laporanHarian.keterangan }}
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex justify-center gap-2">
                                    <Button v-if="isStafEsdm" variant="outline" as-child
                                        class="rounded-md bg-yellow-500 px-3 py-1.5 text-sm text-white hover:bg-yellow-600">
                                        <Link :href="route('laporan-harian.edit', {
                                            laporan_harian: laporanHarian.id,
                                        })
                                            ">
                                            Edit
                                        </Link>
                                    </Button>

                                    <Button @click="deleteItem(laporanHarian.id)" v-if="isStafEsdm"
                                        class="rounded-md bg-red-600 px-3 py-1.5 text-sm text-white hover:bg-red-700">
                                        Hapus
                                    </Button>

                                    <Button v-if="isOperatorBku" variant="outline" size="sm"
                                        @click="openJustifikasi(laporanHarian)">
                                        Ajukan Justifikasi
                                    </Button>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="laporanHarians.data.length === 0">
                            <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500">
                                Belum ada data Laporan Harian.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- tabel di role admin or staf -->
            <div class="overflow-hidden rounded-lg border bg-white shadow-sm" v-if="isStafEsdmOrAdmin">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">
                                No
                            </th>
                            
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700" >
                                Nama BKU
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

                            <th class="px-6 py-3 text-center text-sm font-semibold text-gray-700">
                                Status
                            </th>

                            <th class="px-6 py-3 text-center text-sm font-semibold text-gray-700">
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
                                {{ (laporanHarians.current_page - 1) * laporanHarians.per_page + index + 1 }}
                            </td>

                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                {{ laporanHarian.bku_kontrak.bku.nama }}
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
                                <span v-if="!laporanHarian.justifikasi_terbaru"
                                    class="inline-flex rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-600">
                                    Belum diajukan
                                </span>

                                <span v-else-if="laporanHarian.justifikasi_terbaru.status === 'pending'"
                                    class="inline-flex rounded-full bg-yellow-100 px-2 py-1 text-xs font-medium text-yellow-700">
                                    Menunggu Persetujuan
                                </span>

                                <span v-else-if="laporanHarian.justifikasi_terbaru.status === 'approved'"
                                    class="inline-flex rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-700">
                                    Disetujui
                                </span>

                                <span v-else-if="laporanHarian.justifikasi_terbaru.status === 'rejected'"
                                    class="inline-flex rounded-full bg-red-100 px-2.5 py-1 text-xs font-medium text-red-700">
                                    Ditolak
                                </span>
                            </td>

                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                {{ laporanHarian.keterangan }}
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex justify-center gap-2">
                                    <Button v-if="isStafEsdm" variant="outline" as-child
                                        class="rounded-md bg-yellow-500 px-3 py-1.5 text-sm text-white hover:bg-yellow-600">
                                        <Link :href="route('laporan-harian.edit', {
                                            laporan_harian: laporanHarian.id,
                                        })
                                            ">
                                            Edit
                                        </Link>
                                    </Button>

                                    <Button @click="deleteItem(laporanHarian.id)" v-if="isStafEsdm"
                                        class="rounded-md bg-red-600 px-3 py-1.5 text-sm text-white hover:bg-red-700">
                                        Hapus
                                    </Button>

                                    <Button v-if="isOperatorBku" variant="outline" size="sm"
                                        @click="openJustifikasi(laporanHarian)">
                                        Ajukan Justifikasi
                                    </Button>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="laporanHarians.data.length === 0">
                            <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500">
                                Belum ada data Laporan Harian.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="laporanHarians.last_page > 1" class="flex justify-center border-t border-gray-200 px-6 py-4">
                <Pagination v-slot="{ page }" :items-per-page="laporanHarians.per_page" :total="laporanHarians.total"
                    :default-page="laporanHarians.current_page" @update:page="goToPage">
                    <PaginationContent v-slot="{ items }">
                        <PaginationPrevious />

                        <template v-for="(item, index) in items" :key="index">
                            <PaginationItem v-if="item.type === 'page'" :value="item.value"
                                :is-active="item.value === page">
                                {{ item.value }}
                            </PaginationItem>

                            <PaginationEllipsis v-else :index="index" />
                        </template>

                        <PaginationNext />
                    </PaginationContent>
                </Pagination>
            </div>
        </div>
    </AppLayout>

    <CreateJustifikasiDialog v-model:open="showJustifikasi" :laporan="selectedLaporan" />
</template>