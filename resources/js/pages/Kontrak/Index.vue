<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
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
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';

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

defineProps<{
    kontraks: Kontrak[];
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
                <Link :href="route('kontrak.create')" prefetch v-if="isStafEsdm"
                    class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    <Plus class="h-4 w-4" :stroke-width="2.5"/> 
                    Tambah Kontrak
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
                                Keterangan
                            </th>

                            <th class="px-6 py-3 text-center text-sm font-semibold text-gray-700">
                                Aksi
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y">
                        <tr v-for="(kontrak, index) in kontraks" :key="kontrak.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ index + 1 }}
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

                        <tr v-if="kontraks.length === 0">
                            <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">
                                Belum ada data Kontrak.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- DIALOG KONFIRMASI HAPUS KOntrak -->
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
                                delete Kontrak {{ kontrakToDelete?.nama }}
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
                        :disabled="deleteConfirmText.trim() !== `delete Kontrak ${kontrakToDelete?.nama}`"
                        class="rounded-md bg-red-600 px-4 py-2 text-sm text-white hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-50">
                        Ya, Hapus
                    </button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>