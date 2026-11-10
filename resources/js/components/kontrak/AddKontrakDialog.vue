<script setup lang="ts">
import { watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { Save } from 'lucide-vue-next';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

const props = defineProps<{
    open: boolean;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    created: [];
}>();

const form = useForm({
    nama: '',
    keterangan: '',
});

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            form.clearErrors();
        }
    }
);

const closeDialog = () => {
    if (form.processing) return;

    emit('update:open', false);
    form.reset();
    form.clearErrors();
};

const submit = () => {
    form.post(route('kontrak.store'), {
        preserveScroll: true,

        onSuccess: () => {
            form.reset();
            form.clearErrors();

            emit('update:open', false);
            emit('created');
        },
    });
};
</script>

<template>
    <Dialog :open="props.open" @update:open="(value) => {
        if (!value) closeDialog();
    }">
        <DialogContent class="sm:max-w-[525px]">
            <DialogHeader>
                <DialogTitle>Tambah Kontrak</DialogTitle>
                <DialogDescription>
                    Tambahkan Kontrak baru.
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submit" class="space-y-5">

                <!-- Nama BKU -->
                <div class="space-y-2">
                    <Label for="nama">Nama Kontrak</Label>

                    <Input id="nama" v-model="form.nama" type="text" placeholder="Masukkan nama BKU"
                        :disabled="form.processing" />

                    <p v-if="form.errors.nama" class="text-sm text-destructive">
                        {{ form.errors.nama }}
                    </p>
                </div>

                <!-- Keterangan -->
                <div class="space-y-2">
                    <Label for="keterangan">Keterangan</Label>

                    <Input id="keterangan" v-model="form.keterangan" max="100"
                        placeholder="contoh: 5 Tahun" :disabled="form.processing" />

                    <p v-if="form.errors.keterangan" class="text-sm text-destructive">
                        {{ form.errors.keterangan }}
                    </p>
                </div>

                <!-- Tombol -->
                <DialogFooter>
                    <Button type="button" variant="outline" @click="closeDialog" :disabled="form.processing">
                        Batal
                    </Button>

                    <Button type="submit" :disabled="form.processing">
                        <Save v-if="!form.processing" :size="18" :stroke-width="2.5" />
                        {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                    </Button>
                </DialogFooter>

            </form>
        </DialogContent>
    </Dialog>
</template>
