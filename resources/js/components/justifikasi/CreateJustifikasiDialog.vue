<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';

import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import InputError from '@/components/InputError.vue';
import Label from '../ui/label/Label.vue';
import Textarea from '../ui/textarea/Textarea.vue';

interface LaporanHarian {
    id: number;
    tanggal: string;
    total_produksi: number | string;
    total_lifting: number | string;

    bku_kontrak: {
        bku: {
            nama: string;
        };

        kontrak: {
            nama: string;
        };
    };
}

const props = defineProps<{
    open: boolean;
    laporan: LaporanHarian | null;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

const form = useForm({
    laporan_harian_id: 0,
    alasan_revisi: '',
    produksi_usulan: '',
    lifting_usulan: '',
});

watch(
    () => [props.open, props.laporan] as const,
    ([isOpen, laporan]) => {
        if (!isOpen || !laporan) {
            return;
        }

        form.laporan_harian_id = laporan.id;
        form.alasan_revisi = '';
        form.produksi_usulan = String(laporan.total_produksi);
        form.lifting_usulan = String(laporan.total_lifting);

        form.clearErrors();
    },
);

const formatTanggal = (tanggal: string) => {
    return new Date(tanggal).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    });
};

const closeDialog = () => {
    if (form.processing) {
        return;
    }

    emit('update:open', false);
};

const submit = () => {
    form.post(route('justifikasi.store'), {
        preserveScroll: true,

        onSuccess: () => {
            emit('update:open', false);
            form.reset();
        },
    });
};
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="sm:max-w-lg">
            <DialogHeader>
                <DialogTitle>
                    Ajukan Justifikasi Revisi
                </DialogTitle>

                <DialogDescription>
                    Ajukan perubahan terhadap laporan harian
                    yang sudah tersimpan.
                </DialogDescription>
            </DialogHeader>

            <div v-if="laporan" class="space-y-5">
                <!-- Informasi laporan -->
                <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <p class="text-xs font-medium text-gray-500">
                                BKU
                            </p>

                            <p class="mt-1 text-sm font-medium text-gray-900">
                                {{ laporan.bku_kontrak.bku.nama }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-medium text-gray-500">
                                Kontrak
                            </p>

                            <p class="mt-1 text-sm font-medium text-gray-900">
                                {{ laporan.bku_kontrak.kontrak.nama }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-medium text-gray-500">
                                Tanggal
                            </p>

                            <p class="mt-1 text-sm font-medium text-gray-900">
                                {{ formatTanggal(laporan.tanggal) }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Nilai saat ini -->
                <div>
                    <h4 class="mb-3 text-sm font-semibold text-gray-800">
                        Data Saat Ini
                    </h4>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <Label class="mb-2 block text-sm font-medium text-gray-700">
                                Total Produksi
                            </Label>

                            <Input :model-value="laporan.total_produksi
                                " type="number" readonly class="bg-gray-50" />
                        </div>

                        <div>
                            <Label class="mb-2 block text-sm font-medium text-gray-700">
                                Total Lifting
                            </Label>

                            <Input :model-value="laporan.total_lifting
                                " type="number" readonly class="bg-gray-50" />
                        </div>
                    </div>
                </div>

                <form class="space-y-5" @submit.prevent="submit">
                    <!-- Alasan -->
                    <div>
                        <Label for="alasan_revisi" class="mb-2 block text-sm font-medium text-gray-700">
                            Alasan Revisi
                        </Label>

                        <Textarea id="alasan_revisi" v-model="form.alasan_revisi" rows="4" maxlength="1000"
                            placeholder="Jelaskan alasan perubahan data..."
                            class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm outline-none transition focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" />

                        <InputError :message="form.errors.alasan_revisi" class="mt-2" />
                    </div>

                    <!-- Usulan -->
                    <div>
                        <h4 class="mb-3 text-sm font-semibold text-gray-800">
                            Data Usulan Revisi
                        </h4>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <Label for="produksi_usulan" class="mb-2 block text-sm font-medium text-gray-700">
                                    Produksi Usulan
                                </Label>

                                <Input id="produksi_usulan" v-model="form.produksi_usulan" type="number" min="0"
                                    step="0.01" placeholder="0.00" />

                                <InputError :message="form.errors.produksi_usulan
                                    " class="mt-2" />
                            </div>

                            <div>
                                <Label for="lifting_usulan" class="mb-2 block text-sm font-medium text-gray-700">
                                    Lifting Usulan
                                </Label>

                                <Input id="lifting_usulan" v-model="form.lifting_usulan" type="number" min="0"
                                    step="0.01" placeholder="0.00" />

                                <InputError :message="form.errors.lifting_usulan
                                    " class="mt-2" />
                            </div>
                        </div>
                    </div>

                    <DialogFooter>
                        <Button type="button" variant="outline" :disabled="form.processing" @click="closeDialog">
                            Batal
                        </Button>

                        <Button type="submit" :disabled="form.processing">
                            {{
                                form.processing
                                    ? 'Mengirim...'
                                    : 'Ajukan Justifikasi'
                            }}
                        </Button>
                    </DialogFooter>
                </form>
            </div>
        </DialogContent>
    </Dialog>
</template>