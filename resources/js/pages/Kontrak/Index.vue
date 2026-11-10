<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem, SharedData } from '@/types';
import { Head, router, Link, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import { route } from 'ziggy-js';
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { Pagination, PaginationContent, PaginationEllipsis, PaginationItem, PaginationNext, PaginationPrevious } from '@/components/ui/pagination';
import AddKontrakDialog from '@/components/kontrak/AddKontrakDialog.vue';
import DeleteKontrakDialog from '@/components/kontrak/DeleteKontrakDialog.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Kontrak',
        href: route('kontrak.index'),
    }
];

interface Kontrak {
    id: number;
    nama: string;
    keterangan: string;
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
    kontraks: PaginationData<Kontrak>;
}>();

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
        route('kontrak.index'),
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
const kontrakToDelete = ref<Kontrak | null>(null);
const deleteConfirmText = ref('');

const openDeleteDialog = (kontrak: Kontrak) => {
    kontrakToDelete.value = kontrak;
    deleteConfirmText.value = '';
    showDeleteDialog.value = true;
}

const confirmDelete = () => {
    if (!kontrakToDelete.value) return;
    const expected = `delete Kontrak ${kontrakToDelete.value.nama}`;
    if (deleteConfirmText.value.trim() !== expected) return;
    router.delete(route('kontrak.destroy', kontrakToDelete.value.id), {
        onSuccess: () => {
            showDeleteDialog.value = false;
            kontrakToDelete.value = null;
            deleteConfirmText.value = '';
        }
    });
}

const cancelDelete = () => {
    showDeleteDialog.value = false;
    kontrakToDelete.value = null;
    deleteConfirmText.value = '';
}
</script>

<template>

    <Head title="Kontrak" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">
                        Kontrak
                    </h1>

                    <p class="mt-1 text-sm text-gray-600">
                        Kelola data Kontrak.
                    </p>
                </div>
                <Button
                    class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                    v-if="isStafEsdm" @click="showAddDialog = true">
                    <Plus :size="18" />
                    Tambah Kontrak
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
                                Keterangan
                            </th>

                            <th class="px-6 py-3 text-center text-sm font-semibold text-gray-700">
                                Aksi
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y">
                        <tr v-for="(kontrak, index) in props.kontraks.data" :key="kontrak.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ (props.kontraks.current_page - 1) * props.kontraks.per_page + index + 1 }}
                            </td>

                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                {{ kontrak.nama }}
                            </td>

                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                {{ kontrak.keterangan }}
                                <span v-if="kontrak.keterangan === null" class="text-center"> - </span>
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex justify-center gap-2">
                                    <Link :href="route('kontrak.edit', { kontrak: kontrak.id })" v-if="isStafEsdm"
                                        class="rounded-md bg-yellow-500 px-3 py-1.5 text-sm text-white hover:bg-yellow-600">
                                        <Pencil :size="16" :stroke-width="3" />
                                    </Link>

                                    <Button @click="openDeleteDialog(kontrak)" v-if="isStafEsdm"
                                        class="rounded-md bg-red-600 px-3 py-1.5 text-sm text-white hover:bg-red-700">
                                        <Trash2 :size="16" :stroke-width="3" />
                                    </Button>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="!props.kontraks.data.length">
                            <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">
                                Belum ada data Kontrak.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            <div v-if="kontraks.last_page > 1"
                class="flex flex-col items-center justify-between gap-4 border-t px-4 py-4 sm:flex-row">

                <!-- Navigasi halaman -->
                <Pagination v-slot="{ page: activePage }" :items-per-page="kontraks.per_page" :total="kontraks.total"
                    :page="kontraks.current_page" @update:page="goToPage">
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


        <!-- DIALOG KONFIRMASI HAPUS KOntrak -->
        <DeleteKontrakDialog v-model:open="showDeleteDialog" :kontrak="kontrakToDelete" :key="kontrakToDelete?.id"
            @confirm="confirmDelete" @cancel="cancelDelete" />

        <!-- Dialog Tambah Kontrak -->
        <AddKontrakDialog v-model:open="showAddDialog" @created="router.reload({ only: ['kontraks'] })" />
    </AppLayout>
</template>