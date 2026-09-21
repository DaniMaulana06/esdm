<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem } from '@/types';
import { type SharedData } from '@/types';
import { Head, router, Link, usePage } from '@inertiajs/vue3';
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

const props = defineProps<{
    laporanHarians: Pagination<LaporanHarian>;
    bkuKontraks: BkuKontrak[];
    filters: {
        bku_id?: string;
        tanggal?: string;
    };
}>();


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

const isStafEsdm = computed(() => {
    return user.value?.role === 'staf_esdm';
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
                                Tanggal
                            </th>

                            <th class="px-6 py-3 text-center text-sm font-semibold text-gray-700">
                                Total Produksi
                            </th>
                            <th class="px-6 py-3 text-center text-sm font-semibold text-gray-700">
                                Total Lifting
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
                                    <Button v-if="page.props.auth.user.role === 'staf_dinas'" variant="outline"
                                        as-child class="rounded-md bg-yellow-500 px-3 py-1.5 text-sm text-white hover:bg-yellow-600">
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