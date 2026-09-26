<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem } from '@/types';
import { type SharedData } from '@/types';
import { Head, router, Link, usePage } from '@inertiajs/vue3';
import { ArrowDown, ArrowUp, ArrowUpDown } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
import { route } from 'ziggy-js';

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
    alasan?: string;
    status?: string;
}

interface LaporanHarian {
    id: number;
    bku_kontrak_id: number;
    tanggal: string;
    total_produksi: string;
    total_lifting: string;

    bku_kontrak: BkuKontrak;
    justifikasis: Justifikasi[];
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


const props = defineProps<{
    laporanHarians: Pagination<LaporanHarian>;
    bkuKontraks: BkuKontrak[];
    filters: Filters;
}>();

const search = ref(props.filters.search ?? '');
const bkuId = ref(props.filters.bku_id ?? '');
const tanggal = ref(props.filters.tanggal ?? '');
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

const applyFilter = () => {
    router.get(
        route('laporan-harian.index'),
        {
            search: search.value || undefined,
            bku_id: bkuId.value || undefined,
            tanggal: tanggal.value || undefined,
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
    tanggal.value = '';

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
            tanggal: tanggal.value || undefined,
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

// const paginationLabel = (label: string) => {
//     return label
//         .replace('&laquo;', '«')
//         .replace('&raquo;', '»');
// };

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
    return (user.value?.role === 'staf_dinas' || (user.value?.role === 'admin' && user.value?.bku_id != null));
});

const showFlash = ref(true);

const formatTanggal = (tanggal: string) => {
    return new Date(tanggal).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    })
}

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
                    + Tambah Laporan Harian
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

                        <Input id="tanggal" v-model="tanggal" type="date" />
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

            <div class="overflow-hidden rounded-lg border bg-white shadow-sm">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">
                                No
                            </th>

                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">
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
                                Keterangan
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y">
                        <tr v-for="(laporanHarian, index) in laporanHarians.data" :key="laporanHarian.id"
                            class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ index + 1 }}
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
                                <div class="flex justify-center gap-2">
                                    <Button v-if="page.props.auth.user.role === 'staf_dinas'" variant="outline" as-child
                                        class="rounded-md bg-yellow-500 px-3 py-1.5 text-sm text-white hover:bg-yellow-600">
                                        <Link :href="route('laporan-harian.edit', {
                                            laporan_harian: laporanHarian.id,
                                        })
                                            ">
                                            Edit
                                        </Link>
                                    </Button>

                                    <Button @click="deleteItem(laporanHarian.id)" v-if="page.props.auth.user.role === 'staf_dinas'"
                                        class="rounded-md bg-red-600 px-3 py-1.5 text-sm text-white hover:bg-red-700">
                                        Hapus
                                    </Button>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="laporanHarians.data.length === 0">
                            <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">
                                Belum ada data Laporan Harian.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>