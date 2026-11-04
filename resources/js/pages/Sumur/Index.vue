<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import { Label } from '@/components/ui/label';
import { Pagination, PaginationContent, PaginationEllipsis, PaginationItem, PaginationNext, PaginationPrevious } from '@/components/ui/pagination';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem, SharedData } from '@/types';
import { Head, router, Link, usePage } from '@inertiajs/vue3';
import { MapPin, Pencil, Plus, Search, Trash2 } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
import { route } from 'ziggy-js';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Sumur',
        href: route('sumur.index'),
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

interface Sumur {
    id: number;
    bku_kontrak_id: number;
    nama_sumur: string;
    desa: string;
    kecamatan: string;
    kabupaten: string;
    latitude: number | null;
    longitude: number | null;
    bku_kontrak: BkuKontrak;
}

interface PaginationData<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: {
        url: string | null;
        label: string;
        active: boolean;
    }[];
}

interface Filters {
    search: string;
    bku_id: string | number;
    kontrak_id: string | number;
}


const deleteItem = (id: number) => {
    if (confirm('apakah kamu yakin ingin menghapus sumur ini?')) {
        router.delete(route('sumur.destroy', id));
    }
}

const cekLokasi = (latitude: number | null, longitude: number | null) => {
    if (latitude && longitude) {
        const url = `https://www.google.com/maps?q=${latitude},${longitude}`;
        window.open(url, '_blank');
    } else {
        alert('Lokasi tidak tersedia untuk sumur ini.');
    }
}

const props = defineProps<{
    sumurs: PaginationData<Sumur>;
    bkus: Bku[];
    kontraks: Kontrak[];
    filters: Filters;
}>();

const search = ref(props.filters.search ?? '');
const bkuId = ref(
    props.filters.bku_id ? String(props.filters.bku_id) : 'all'
);
const kontrakId = ref(
    props.filters.kontrak_id ? String(props.filters.kontrak_id) : 'all'
);

const currentQuery = () => ({
    search: search.value || undefined,
    bku_id: isStafEsdmOrAdmin.value && bkuId.value !== 'all'
        ? bkuId.value
        : undefined,
    kontrak_id: kontrakId.value !== 'all'
        ? kontrakId.value
        : undefined,
});

const applyFilter = () => {
    router.get(route('sumur.index'), currentQuery(), {
        preserveState: false,
        preserveScroll: true,
        replace: true,
    });
};

const resetFilter = () => {
    search.value = '';
    bkuId.value = 'all';
    kontrakId.value = 'all';

    router.get(route('sumur.index'), {}, {
        preserveState: false,
        preserveScroll: true,
        replace: true,
    });
};

const goToPage = (page: number) => {
    router.get(
        route('sumur.index'),
        {
            search: search.value || undefined,
            bku_id: isStafEsdmOrAdmin.value && bkuId.value !== 'all'
                ? bkuId.value
                : undefined,
            kontrak_id: kontrakId.value !== 'all'
                ? kontrakId.value
                : undefined,
            page,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

const page = usePage<SharedData>();

const user = computed(() => page.props.auth.user);

const isStafEsdm = computed(() => {
    return user.value?.role === 'staf_dinas';
});

const isStafEsdmOrOperatorBku = computed(() => {
    return (user.value?.role === 'staf_dinas' || (user.value?.role === 'operator_bku' && user.value?.bku_id != null));
});

const flash = computed(() => page.props.flash as {
    success?: string; error?: string
});

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

const isAdmin = computed(() => user.value?.role === 'admin');

const isStafEsdmOrAdmin = computed(() => {
    return isAdmin.value || isStafEsdm.value;
});

</script>

<template>

    <Head title="Sumur" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">
                        Kelola Data Sumur
                    </h1>
                    <!-- 
                    <p class="mt-1 text-sm text-gray-600">
                        Kelola data BKU.
                    </p> -->
                </div>
                <Link :href="route('sumur.create')" v-if="isStafEsdmOrOperatorBku" prefetch
                    class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    <Plus class="h-4 w-4" :stroke-width="2.5" />
                    Tambah Sumur
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


            <!-- FILTER DATA SUMUR -->
            <div class="mb-4 rounded-lg border bg-card p-4 shadow-sm">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">

                    <!-- Pencarian Nama Sumur -->
                    <div :class="isStafEsdmOrAdmin ? 'lg:col-span-2' : 'lg:col-span-2'">
                        <Label for="search" class="mb-2 block text-sm font-medium">
                            Nama Sumur
                        </Label>

                        <Input id="search" v-model="search" type="text" placeholder="Cari nama sumur..."
                            @keyup.enter="applyFilter" />
                    </div>

                    <!-- Filter BKU: Admin dan Staf Dinas -->
                    <div v-if="isStafEsdmOrAdmin">
                        <Label for="bku" class="mb-2 block text-sm font-medium">
                            BKU
                        </Label>

                        <Select v-model="bkuId">
                            <SelectTrigger class="w-full">
                                <SelectValue placeholder="Pilih BKU" />
                            </SelectTrigger>

                            <SelectContent>
                                <SelectGroup>
                                    <SelectItem value="all">
                                        Semua BKU
                                    </SelectItem>

                                    <SelectItem v-for="bku in bkus" :key="bku.id" :value="String(bku.id)">
                                        {{ bku.nama }}
                                    </SelectItem>
                                </SelectGroup>
                            </SelectContent>
                        </Select>
                    </div>

                    <!-- Filter Kontrak: Semua Role -->
                    <div>
                        <Label for="kontrak" class="mb-2 block text-sm font-medium">
                            Kontrak
                        </Label>

                        <Select v-model="kontrakId">
                            <SelectTrigger class="w-full">
                                <SelectValue placeholder="Pilih Kontrak" />
                            </SelectTrigger>

                            <SelectContent>
                                <SelectGroup>
                                    <SelectItem value="all">
                                        Semua Kontrak
                                    </SelectItem>

                                    <SelectItem v-for="kontrak in kontraks" :key="kontrak.id"
                                        :value="String(kontrak.id)">
                                        {{ kontrak.nama }}
                                    </SelectItem>
                                </SelectGroup>
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                <!-- Tombol Filter -->
                <div class="mt-4 flex justify-end gap-2">
                    <Button type="button" variant="outline" @click="resetFilter">
                        Reset
                    </Button>

                    <Button type="button" @click="applyFilter">
                        <Search class="mr-2 h-4 w-4" />
                        Terapkan Filter
                    </Button>
                </div>
            </div>


            <div class="overflow-hidden rounded-lg border bg-white shadow-sm">
                <div class="w-full">
                    <table class="w-full">
                        <thead class="bg-gray-50">
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
                                    Nama Sumur
                                </th>

                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">
                                    Desa
                                </th>

                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">
                                    Kecamatan
                                </th>

                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">
                                    Kabupaten
                                </th>
                                <!-- <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">
                                    Latitude
                                </th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">
                                    Longitude
                                </th> -->

                                <th class="px-6 py-3 text-center text-sm font-semibold text-gray-700">
                                    Aksi
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y">
                            <tr v-for="(smr, index) in props.sumurs.data" :key="smr.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                    {{ (sumurs.current_page - 1) * sumurs.per_page + index + 1 }}
                                </td>

                                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                    {{ smr.bku_kontrak?.bku?.nama }}
                                </td>

                                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                    {{ smr.bku_kontrak?.kontrak?.nama }}
                                </td>

                                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                    {{ smr.nama_sumur }}
                                </td>

                                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                    {{ smr.desa }}
                                </td>

                                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                    {{ smr.kecamatan }}
                                </td>

                                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                    {{ smr.kabupaten }}
                                </td>
                                <!-- <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                    {{ smr.latitude }}
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                    {{ smr.longitude }}
                                </td> -->

                                <td class="px-6 py-4">
                                    <div class="flex justify-center gap-2">

                                        <Button @click="cekLokasi(smr.latitude, smr.longitude)"
                                            class="rounded-md bg-blue-600 px-3 py-1.5 text-sm text-white hover:bg-blue-700">
                                            <MapPin color="#ffffff" :stroke-width="3" />
                                        </Button>

                                        <Link :href="route('sumur.edit', { sumur: smr.id })" v-if="isStafEsdm"
                                            class="rounded-md bg-yellow-500 px-3 py-1.5 text-sm text-white hover:bg-yellow-600">
                                            <Pencil :size="16" :stroke-width="3" />
                                        </Link>

                                        <Button @click="deleteItem(smr.id)" v-if="isStafEsdm"
                                            class="rounded-md bg-red-600 px-3 py-1.5 text-sm text-white hover:bg-red-700">
                                            <Trash2 :size="16" :stroke-width="3" />
                                        </Button>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="sumurs.data.length === 0">
                                <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">
                                    Belum ada data Sumur.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Pagination -->
            <div v-if="sumurs.last_page > 1"
                class="flex flex-col items-center justify-between gap-4 border-t px-4 py-4 sm:flex-row">

                <!-- Navigasi halaman -->
                <Pagination v-slot="{ page: activePage }" :items-per-page="sumurs.per_page" :total="sumurs.total"
                    :page="sumurs.current_page" @update:page="goToPage">
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
    </AppLayout>
</template>