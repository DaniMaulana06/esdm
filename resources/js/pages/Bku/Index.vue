<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem, SharedData } from '@/types';
import { Head, router, Link, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import { route } from 'ziggy-js';

import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog'
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';

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

defineProps<{
    bkus: Bku[];
}>();

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

const showDeleteDialog = ref(false);
const bkuToDelete = ref<Bku | null>(null);
const deleteConfirmText = ref('');

const openDeleteDialog = (bku: Bku) => {
    bkuToDelete.value = bku;
    deleteConfirmText.value = '';
    showDeleteDialog.value = true;
}

const confirmDelete = () => {
    if (!bkuToDelete.value) return;
    const expected = `delete BKU ${bkuToDelete.value.nama}`;
    if (deleteConfirmText.value.trim() !== expected) return;
    router.delete(route('bku.destroy', bkuToDelete.value.id), {
        onSuccess: () => {
            showDeleteDialog.value = false;
            bkuToDelete.value = null;
            deleteConfirmText.value = '';
        }
    });
}

const cancelDelete = () => {
    showDeleteDialog.value = false;
    bkuToDelete.value = null;
    deleteConfirmText.value = '';
}
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
                <Link :href="route('bku.create')" prefetch v-if="isStafEsdm"
                    class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    <Plus class="h-4 w-4" :stroke-width="2.5"/> 
                    Tambah BKU
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
                        <tr v-for="(bku, index) in bkus" :key="bku.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ index + 1 }}
                            </td>

                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                {{ bku.nama }}
                            </td>

                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                <span v-if="bku.kategori" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
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
                                        class="inline-flex items-center gap-2   rounded-md bg-yellow-500 px-3 py-1.5 text-sm text-white hover:bg-yellow-600">
                                        <Pencil :size="16" :stroke-width="3" />
                                    </Link>

                                    <button @click="openDeleteDialog(bku)" v-if="isStafEsdm"
                                        class="rounded-md bg-red-600 px-3 py-1.5 text-sm text-white hover:bg-red-700">
                                       <Trash2 :size="16" :stroke-width="3" />
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="bkus.length === 0">
                            <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">
                                Belum ada data Bku.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- DIALOG KONFIRMASI HAPUS BKU -->
        <Dialog :open="showDeleteDialog" @update:open="cancelDelete">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle class="text-red-600">Hapus BKU</DialogTitle>
                    <DialogDescription>
                        Tindakan ini <strong>tidak dapat dibatalkan</strong>. Data BKU akan dihapus secara permanen.
                    </DialogDescription>
                </DialogHeader>

                <DialogDescription class="my-2 space-y-3">
                    <strong>
                        <p class="text-sm text-gray-700">
                            Untuk mengonfirmasi, ketik:
                            <code class="rounded bg-gray-100 px-1 font-mono font-semibold text-red-600">
                                delete BKU {{ bkuToDelete?.nama }}
                            </code>
                        </p>
                    </strong>
                    <input v-model="deleteConfirmText" type="text" placeholder="Ketik konfirmasi di sini..."
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-red-400 focus:outline-none focus:ring-1 focus:ring-red-400" />
                </DialogDescription>

                <DialogFooter>
                    <DialogClose>
                        <button @click="cancelDelete" class="rounded-md border px-4 py-2 text-sm hover:bg-gray-50">
                            Batal
                        </button>
                    </DialogClose>
                    <button @click="confirmDelete"
                        :disabled="deleteConfirmText.trim() !== `delete BKU ${bkuToDelete?.nama}`"
                        class="rounded-md bg-red-600 px-4 py-2 text-sm text-white hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-50">
                        Ya, Hapus
                    </button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>