<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem } from '@/types';
import { Head, router, Link, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import { route } from 'ziggy-js';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Sumur',
        href: route('sumur.index'),
    }
];

interface Sumur {
    id: number;
    bku_kontrak_id: number;
    nama_sumur: string;
    desa: number;
    kecamatan: number;
    kabupaten: number;
    latitude: number;
    longitude: number;
    created_at: string;
    updated_at: string;
}

const deleteItem = (id: number) => {
    if (confirm('apakah kamu yakin ingin menghapus sumur ini?')) {
        router.delete(route('sumur.destroy', id));
    }
}

defineProps<{
    sumurs: Sumur[];
}>();

const page = usePage();

const flash = computed(() => page.props.flash as {
    success?: string; error?: string
}
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
                <Link :href="route('sumur.create')" prefetch
                    class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    + Tambah Sumur
                </Link>
            </div>

            <!-- FLASH SUCCESS -->
            <div
                v-if="showFlash && flash.success"
                class="mb-4 flex items-center justify-between rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-700"
            >
                <span>
                    {{ flash.success }}
                </span>

                <button
                    type="button"
                    @click="closeFlash"
                    class="ml-4 text-lg font-bold text-green-700 hover:text-green-900"
                >
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
                                Kontrak BKU
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
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">
                                Latitude
                            </th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">
                                Longitude
                            </th>

                            <th class="px-6 py-3 text-center text-sm font-semibold text-gray-700">
                                Aksi
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y">
                        <tr v-for="(smr, index) in sumurs" :key="smr.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ index + 1 }}
                            </td>

                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                {{ smr.bku_kontrak_id }}
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
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                {{ smr.latitude }}
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                {{ smr.longitude }}
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex justify-center gap-2">
                                    <Link :href="route('bku.edit', { sumur: smr.id })"
                                        class="rounded-md bg-yellow-500 px-3 py-1.5 text-sm text-white hover:bg-yellow-600">
                                        Edit
                                    </Link>

                                    <button @click="deleteItem(smr.id)"
                                        class="rounded-md bg-red-600 px-3 py-1.5 text-sm text-white hover:bg-red-700">
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="sumurs.length === 0">
                            <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">
                                Belum ada data Sumur.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>