<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem, SharedData } from '@/types';
import { Head, router, Link, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import { route } from 'ziggy-js';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog'
import { Pencil, Plus, RotateCcw, Search, Trash2 } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Pagination, PaginationContent, PaginationEllipsis, PaginationItem, PaginationNext, PaginationPrevious } from '@/components/ui/pagination';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Input } from '@/components/ui/input';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'BKU Kontrak',
        href: route('bku-kontrak.index'),
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
interface Filters {
    search?: string;
    kontrak_id?: string | number;
}
const props = defineProps<{
    bkuKontraks: PaginationData<BkuKontrak>;
    kontraks: Kontrak[];
    filters: Filters;
}>();

//filter dan search
const search = ref(props.filters.search ?? '');
const kontrakId = ref(props.filters.kontrak_id ? String(props.filters.kontrak_id) : 'all');

const currentQuery = () => ({
    search: search.value || undefined,
    kontrak_id: kontrakId.value !== 'all' ? kontrakId.value : undefined,
});
const applyFilter = () => {
    router.get(route('bku-kontrak.index'), currentQuery(), {
        preserveState: false,
        preserveScroll: true,
        replace: true,
    });
};
const resetFilter = () => {
    search.value = '';
    kontrakId.value = 'all';
    router.get(route('bku-kontrak.index'), {}, {
        preserveState: false,
        preserveScroll: true,
        replace: true,
    });
};

const goToPage = (page: number) => {
    router.get(
        route('bku-kontrak.index'),
        { ...currentQuery(), page },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};

const page = usePage<SharedData>();
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

//delete dialog
const showDeleteDialog = ref(false);
const bkuKontrakToDelete = ref<BkuKontrak | null>(null);
const deleteConfirmText = ref('');

const openDeleteDialog = (bkuKontrak: BkuKontrak) => {
    bkuKontrakToDelete.value = bkuKontrak;
    deleteConfirmText.value = '';
    showDeleteDialog.value = true;
}

const confirmDelete = () => {
    if (!bkuKontrakToDelete.value) return;
    const expected = `delete BKU Kontrak ${bkuKontrakToDelete.value.bku.nama} ${bkuKontrakToDelete.value.kontrak.nama}`;
    if (deleteConfirmText.value.trim() !== expected) return;
    router.delete(route('bku-kontrak.destroy', bkuKontrakToDelete.value.id), {
        onSuccess: () => {
            showDeleteDialog.value = false;
            bkuKontrakToDelete.value = null;
            deleteConfirmText.value = '';
        }
    });
}

const cancelDelete = () => {
    showDeleteDialog.value = false;
    bkuKontrakToDelete.value = null;
    deleteConfirmText.value = '';
}
</script>

<template>

    <Head title="BKU Kontrak" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">
                        BKU dengan Kontrak
                    </h1>
                    <p class="mt-1 text-sm text-gray-600">
                        Kelola data BKU yang berkontrak.
                    </p>
                </div>
                <Link :href="route('bku-kontrak.create')" prefetch v-if="isStafEsdm"
                    class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    <Plus class="h-4 w-4" :stroke-width="2.5" />
                    Tambah BKU Kontrak
                </Link>
            </div>

            <!-- FLASH SUCCESS -->
            <div v-if="showFlash && flash.success"
                class="mb-4 flex items-center justify-between rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-700">
                <span>{{ flash.success }}</span>
                <button type="button" @click="closeFlash"
                    class="ml-4 text-lg font-bold text-green-700 hover:text-green-900">
                    ×
                </button>
            </div>

            <!-- FLASH ERROR -->
            <div v-if="showFlash && flash.error"
                class="mb-4 flex items-center justify-between rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700">
                <span>{{ flash.error }}</span>
                <button type="button" @click="closeFlash"
                    class="ml-4 text-lg font-bold text-red-700 hover:text-red-900">
                    ×
                </button>
            </div>

            <!-- FILTER DATA BKU KONTRAK -->
            <div class="mb-4 rounded-lg border bg-card p-4 shadow-sm">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <!-- Search Nama BKU -->
                    <div>
                        <Label for="search" class="mb-2 block text-sm font-medium">
                            Nama BKU
                        </Label>
                        <Input id="search" v-model="search" type="text" placeholder="Cari nama BKU..."
                            @keyup.enter="applyFilter" />
                    </div>

                    <!-- Filter Kontrak -->
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
                                    <SelectItem value="all">Semua Kontrak</SelectItem>
                                    <SelectItem v-for="kontrak in props.kontraks" :key="kontrak.id"
                                        :value="String(kontrak.id)">
                                        {{ kontrak.nama }}
                                    </SelectItem>
                                </SelectGroup>
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                <!-- Tombol Action Filter -->
                <div class="mt-4 flex justify-end gap-2">
                    <Button type="button" variant="outline" @click="resetFilter">
                        <RotateCcw />
                        Reset
                    </Button>
                    <Button type="button" @click="applyFilter" class="hover:bg-yellow-600">
                        <Search class="mr-1 h-4 w-4" />
                        Terapkan Filter
                    </Button>
                </div>
            </div>

            <!-- TABEL DATA -->
            <div class="overflow-hidden rounded-lg border bg-white shadow-sm">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">No</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Nama BKU</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Kontrak</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Jumlah Sumur</th>
                            <th class="px-6 py-3 text-center text-sm font-semibold text-gray-700">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y">
                        <tr v-for="(bkuKontrak, index) in bkuKontraks.data" :key="bkuKontrak.id"
                            class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ (bkuKontraks.current_page - 1) * bkuKontraks.per_page + index + 1 }}
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                {{ bkuKontrak.bku?.nama }}
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                {{ bkuKontrak.kontrak?.nama }}
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                {{ bkuKontrak.jumlah_sumur }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex justify-center gap-2">
                                    <Link :href="route('bku-kontrak.edit', { bku_kontrak: bkuKontrak.id })"
                                        v-if="isStafEsdm"
                                        class="rounded-md bg-yellow-500 px-3 py-1.5 text-sm text-white hover:bg-yellow-600">
                                        <Pencil :size="16" :stroke-width="3" />
                                    </Link>

                                    <Button @click="openDeleteDialog(bkuKontrak)" v-if="isStafEsdm"
                                        class="rounded-md bg-red-600 px-3 py-2 text-sm text-white hover:bg-red-700">
                                        <Trash2 :size="16" :stroke-width="3" />
                                    </Button>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="bkuKontraks.data.length === 0">
                            <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">
                                Belum ada data BKU Kontrak.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION SHADCN VUE -->
            <div v-if="bkuKontraks.last_page > 1"
                class="flex flex-col items-center justify-between gap-4 border-t px-4 py-4 sm:flex-row">
                <Pagination v-slot="{ page: activePage }" :items-per-page="bkuKontraks.per_page"
                    :total="bkuKontraks.total" :page="bkuKontraks.current_page" @update:page="goToPage">
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

        <!-- DIALOG KONFIRMASI HAPUS BKU KONTRAK -->
        <Dialog :open="showDeleteDialog" @update:open="cancelDelete">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle class="text-red-600">Hapus Kontrak</DialogTitle>
                    <DialogDescription>
                        Tindakan ini <strong>tidak dapat dibatalkan</strong>. Data Kontrak akan dihapus secara permanen.
                    </DialogDescription>
                </DialogHeader>

                <DialogDescription class="my-2 space-y-3">
                    <strong>
                        <p class="text-sm text-gray-700">
                            Untuk mengonfirmasi, ketik:
                            <code class="rounded bg-gray-100 px-1 font-mono font-semibold text-red-600">
                        delete BKU Kontrak {{ bkuKontrakToDelete?.bku.nama }} {{ bkuKontrakToDelete?.kontrak.nama }}
                    </code>
                        </p>
                    </strong>
                    <input v-model="deleteConfirmText" type="text" placeholder="Ketik konfirmasi di sini..."
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-red-400 focus:outline-none focus:ring-1 focus:ring-red-400" />
                </DialogDescription>

                <DialogFooter>
                    <DialogClose as-child>
                        <button type="button" @click="cancelDelete"
                            class="rounded-md border px-4 py-2 text-sm hover:bg-gray-50">
                            Batal
                        </button>
                    </DialogClose>
                    <button type="button" @click="confirmDelete"
                        :disabled="deleteConfirmText.trim() !== `delete BKU Kontrak ${bkuKontrakToDelete?.bku.nama} ${bkuKontrakToDelete?.kontrak.nama}`"
                        class="rounded-md bg-red-600 px-4 py-2 text-sm text-white hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-50">
                        Ya, Hapus
                    </button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>