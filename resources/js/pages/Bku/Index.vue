<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem, SharedData } from '@/types';
import { Head, router, Link, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import { route } from 'ziggy-js';
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { Pagination, PaginationContent, PaginationEllipsis, PaginationItem, PaginationNext, PaginationPrevious } from '@/components/ui/pagination';
import DeleteBkuDialog from '@/components/bku/DeleteBkuDialog.vue';
import AddBkuDialog from '@/components/bku/AddBkuDialog.vue';
import { Button } from '@/components/ui/button';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Badan Kerjasama Usaha',
        href: route('bku.index'),
    }
];

interface Bku {
    id: number;
    nama: string;
    kota: string;
    kategori: string | null;
    penetapan: number;
    created_at: string;
    updated_at: string;
}

interface PaginationData<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: {
        url: string | null;
        label: string;
        active: boolean;
    }[];
}

const props = defineProps<{
    bkus: PaginationData<Bku>;
    kategoris: string[];
}>();
const kategoris = props.kategoris;

const currentQuery = () => {
    const query = new URLSearchParams(window.location.search);
    const result: Record<string, string> = {};
    query.forEach((value, key) => {
        result[key] = value;
    });
    return result;
};

const page = usePage<SharedData>();

const goToPage = (page: number) => {
    router.get(
        route('bku.index'),
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

const user = computed(() => page.props.auth.user);

const isStafEsdm = computed(() => {
    return user.value?.role === 'staf_dinas';
});


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

const showDeleteDialog = ref(false);
const showAddDialog = ref(false);
const bkuToDelete = ref<Bku | null>(null);

const openDeleteDialog = (bku: Bku) => {
    bkuToDelete.value = bku;
    showDeleteDialog.value = true;
};
</script>

<template>

    <Head title="BKU" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">
                        Badan Kerjasama Usaha
                    </h1>

                    <p class="mt-1 text-sm text-gray-600">
                        Kelola data BKU.
                    </p>
                </div>
                <!-- <Link :href="route('bku.create')" prefetch v-if="isStafEsdm"
                    class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    <Plus class="h-4 w-4" :stroke-width="2.5" />
                    Tambah BKU
                </Link> -->
                <Button
                    class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                    v-if="isStafEsdm" @click="showAddDialog = true" >
                    <Plus :size="18" />
                    Tambah BKU
                </Button>
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
                                Kategori
                            </th>

                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">
                                Kabupaten / Kota
                            </th>

                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">
                                Penetapan
                            </th>

                            <th class="px-6 py-3 text-center text-sm font-semibold text-gray-700">
                                Aksi
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y">
                        <tr v-for="(bku, index) in (props.bkus?.data ?? [])" :key="bku.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ (props.bkus.current_page - 1) * props.bkus.per_page + index + 1 }}
                            </td>

                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                {{ bku.nama }}
                            </td>

                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                <span v-if="bku.kategori"
                                    class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800">
                                    {{ bku.kategori }}
                                </span>
                                <span v-else class="text-gray-400">-</span>
                            </td>

                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                {{ bku.kota }}
                            </td>

                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                {{ bku.penetapan }}
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex justify-center gap-2">
                                    <Link :href="route('bku.edit', { bku: bku.id })" v-if="isStafEsdm"
                                        class="inline-flex items-center gap-2 rounded-md bg-yellow-500 px-3 py-1.5 text-sm text-white hover:bg-yellow-600">
                                        <Pencil :size="16" :stroke-width="3" />
                                    </Link>

                                    <Button @click="openDeleteDialog(bku)" v-if="isStafEsdm"
                                        class="rounded-md bg-red-600 px-3 py-1.5 text-sm text-white hover:bg-red-700">
                                        <Trash2 :size="16" :stroke-width="3" />
                                    </Button>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="!props.bkus?.data?.length">
                            <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">
                                Belum ada data BKU.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="bkus.last_page > 1"
            class="flex flex-col items-center justify-between gap-4 border-t px-4 py-4 sm:flex-row">

            <!-- Navigasi halaman -->
            <Pagination v-slot="{ page: activePage }" :items-per-page="bkus.per_page" :total="bkus.total"
                :page="bkus.current_page" @update:page="goToPage">
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

        <DeleteBkuDialog :open="showDeleteDialog" :bku="bkuToDelete" @update:open="showDeleteDialog = $event"
            @deleted="bkuToDelete = null" />
        <AddBkuDialog v-model:open="showAddDialog" :kategoris="kategoris"
            @created="router.reload({ only: ['bkus'] })" />
    </AppLayout>
</template>