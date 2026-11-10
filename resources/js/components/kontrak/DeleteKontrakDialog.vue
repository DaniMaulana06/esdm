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

interface Kontrak {
    id: number;
    nama: string;
    keterangan: string;
}

const props = defineProps<{
    open: boolean;
    kontrak: Kontrak | null;
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
    return `delete Kontrak ${props.kontrak?.nama ?? ''}`;
};

const confirmDelete = () => {
    if (!props.kontrak || processing.value) return;

    if (deleteConfirmText.value.trim() !== expectedText()) return;

    processing.value = true;

    router.delete(route('kontrak.destroy', props.kontrak.id), {
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
    <Dialog
        :open="props.open"
        @update:open="cancelDelete"
    >
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle class="text-red-600">
                    Hapus Kontrak
                </DialogTitle>

                <DialogDescription>
                    Tindakan ini <strong>tidak dapat dibatalkan</strong>.
                    Data Kontrak akan dihapus secara permanen.
                </DialogDescription>
            </DialogHeader>

            <DialogDescription class="my-2 space-y-3">
                <strong>
                    <p class="text-sm text-gray-700">
                        Untuk mengonfirmasi, ketik:
                        <code class="rounded bg-gray-100 px-1 font-mono font-semibold text-red-600">
                            delete Kontrak {{ props.kontrak?.nama }}
                        </code>
                    </p>
                </strong>

                <input
                    v-model="deleteConfirmText"
                    type="text"
                    placeholder="Ketik konfirmasi di sini..."
                    :disabled="processing"
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-red-400 focus:outline-none focus:ring-1 focus:ring-red-400"
                    @keydown.enter="confirmDelete"
                />
            </DialogDescription>

            <DialogFooter>
                <DialogClose as-child>
                    <button
                        @click="cancelDelete"
                        :disabled="processing"
                        class="rounded-md border px-4 py-2 text-sm hover:bg-gray-50"
                    >
                        Batal
                    </button>
                </DialogClose>

                <button
                    @click="confirmDelete"
                    :disabled="
                        deleteConfirmText.trim() !== expectedText()
                        || processing
                    "
                    class="rounded-md bg-red-600 px-4 py-2 text-sm text-white hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    {{ processing ? 'Menghapus...' : 'Ya, Hapus' }}
                </button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
