<script setup lang="ts">
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogClose,
} from '@/components/ui/dialog';
import Input from '../ui/input/Input.vue';
import Button from '../ui/button/Button.vue';
import { AlertTriangle } from 'lucide-vue-next';

interface Bku {
    id: number;
    nama: string;
}

const props = defineProps<{
    open: boolean;
    bku: Bku | null;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    deleted: [];
}>();

const deleteConfirmText = ref('');
const processing = ref(false);

watch(
    () => props.open,
    () => {
        deleteConfirmText.value = '';
    }
);

const expectedText = () => {
    return `delete BKU ${props.bku?.nama ?? ''}`;
};

const confirmDelete = () => {
    if (!props.bku || processing.value) return;

    if (deleteConfirmText.value.trim() !== expectedText()) return;

    processing.value = true;

    router.delete(route('bku.destroy', props.bku.id), {
        preserveScroll: true,

        onSuccess: () => {
            emit('update:open', false);
            emit('deleted');
            deleteConfirmText.value = '';
        },

        onFinish: () => {
            processing.value = false;
        },
    });
};

const cancelDelete = () => {
    if (processing.value) return;

    emit('update:open', false);
    deleteConfirmText.value = '';
};
</script>

<template>
    <Dialog :open="props.open" @update:open="cancelDelete">
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle class="text-red-600 text-center mb-1">
                    Hapus BKU? <br class="mb-3">
                    {{ props.bku?.nama }}
                </DialogTitle>

                <DialogDescription>
                    Tindakan ini <strong>tidak dapat dibatalkan</strong>.
                    Data BKU akan dihapus secara permanen.
                </DialogDescription>
            </DialogHeader>

            <!-- Warning Box -->
            <div class="my-3 flex gap-3 rounded-lg border-l-4 border-red-500 bg-red-100 px-4 py-4">
                <div class="shrink-0 pt-0.5">
                    <AlertTriangle class="h-6 w-6 text-orange-600" />
                </div>

                <div class="space-y-1">
                    <p class="font-semibold text-orange-900">
                        Warning
                    </p>

                    <p class="text-sm leading-relaxed text-orange-800">
                        Dengan menghapus BKU ini,
                        <strong>seluruh data Kontrak yang terhubung</strong>
                        dan <strong>Laporan Harian</strong> di dalamnya
                        juga akan ikut terhapus secara permanen.
                    </p>
                </div>
            </div>

            <DialogDescription class="space-y-3">
                <strong>
                    <p class="text-sm text-gray-700">
                        Untuk mengonfirmasi, ketik:
                        <code class="rounded bg-gray-100 px-1 font-mono font-semibold text-red-600">
                            delete BKU {{ props.bku?.nama }}
                        </code>
                    </p>
                </strong>

                <Input v-model="deleteConfirmText" type="text" placeholder="Ketik konfirmasi di sini..."
                    :disabled="processing"
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-red-400 focus:outline-none focus:ring-1 focus:ring-red-400"
                    @keydown.enter="confirmDelete" />
            </DialogDescription>

            <DialogFooter>
                <DialogClose as-child>
                    <Button @click="cancelDelete" :disabled="processing" variant="outline">
                        Batal
                    </Button>
                </DialogClose>

                <Button @click="confirmDelete" :disabled="deleteConfirmText.trim() !== expectedText()
                    || processing
                    "
                    class="rounded-md bg-red-600 px-4 py-2 text-sm text-white hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-50">
                    {{ processing ? 'Menghapus...' : 'Ya, Hapus' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
