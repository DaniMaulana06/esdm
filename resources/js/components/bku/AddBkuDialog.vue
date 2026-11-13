
<script setup lang="ts">
import { watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { Save } from 'lucide-vue-next';

import daftarKota from '@/data/kota_sumsel.json';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

import {
    Select,
    SelectTrigger,
    SelectValue,
    SelectContent,
    SelectGroup,
    SelectItem,
} from '@/components/ui/select';

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
    kategoris: string[];
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    created: [];
}>();

const form = useForm({
    nama: '',
    kota: '',
    kategori: '',
    penetapan: '',
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
    form.post(route('bku.store'), {
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
    <Dialog
        :open="props.open"
        @update:open="(value) => {
            if (!value) closeDialog();
        }"
    >
        <DialogContent class="sm:max-w-[525px]">
            <DialogHeader>
                <DialogTitle>Tambah BKU</DialogTitle>
                <DialogDescription>
                    Tambahkan BKU baru ke dalam sistem.
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submit" class="space-y-5">

                <!-- Nama BKU -->
                <div class="space-y-2">
                    <Label for="nama">Nama BKU</Label>

                    <Input
                        id="nama"
                        v-model="form.nama"
                        type="text"
                        placeholder="Masukkan nama BKU"
                        :disabled="form.processing"
                    />

                    <p v-if="form.errors.nama" class="text-sm text-destructive">
                        {{ form.errors.nama }}
                    </p>
                </div>

                <!-- Kategori -->
                <div class="space-y-2">
                    <Label for="kategori">Kategori</Label>

                    <Select
                        v-model="form.kategori"
                        :disabled="form.processing"
                    >
                        <SelectTrigger class="w-full">
                            <SelectValue placeholder="Pilih Kategori" />
                        </SelectTrigger>

                        <SelectContent>
                            <SelectGroup>
                                <SelectItem
                                    v-for="item in props.kategoris"
                                    :key="item"
                                    :value="item"
                                >
                                    {{ item }}
                                </SelectItem>
                            </SelectGroup>
                        </SelectContent>
                    </Select>

                    <p v-if="form.errors.kategori" class="text-sm text-destructive">
                        {{ form.errors.kategori }}
                    </p>
                </div>

                <!-- Kabupaten / Kota -->
                <div class="space-y-2">
                    <Label for="kota">Kabupaten / Kota</Label>

                    <Select
                        v-model="form.kota"
                        :disabled="form.processing"
                    >
                        <SelectTrigger class="w-full">
                            <SelectValue placeholder="Pilih Kabupaten/Kota" />
                        </SelectTrigger>

                        <SelectContent>
                            <SelectGroup class="h-[280px]">
                                <SelectItem
                                    v-for="item in daftarKota"
                                    :key="item.id"
                                    :value="item.nama"
                                >
                                    {{ item.nama_resmi }}
                                </SelectItem>
                            </SelectGroup>
                        </SelectContent>
                    </Select>

                    <p v-if="form.errors.kota" class="text-sm text-destructive">
                        {{ form.errors.kota }}
                    </p>
                </div>

                <!-- Penetapan BKU -->
                <div class="space-y-2">
                    <Label for="penetapan">Penetapan BKU</Label>

                    <Input
                        id="penetapan"
                        v-model="form.penetapan"
                        type="number"
                        min="0"
                        placeholder="Masukkan penetapan BKU"
                        :disabled="form.processing"
                    />

                    <p v-if="form.errors.penetapan" class="text-sm text-destructive">
                        {{ form.errors.penetapan }}
                    </p>
                </div>

                <!-- Tombol -->
                <DialogFooter>
                    <Button
                        type="button"
                        variant="outline"
                        @click="closeDialog"
                        :disabled="form.processing"
                    >
                        Batal
                    </Button>

                    <Button type="submit" :disabled="form.processing" variant="submit">
                        <Save
                            v-if="!form.processing"
                            :size="18"
                            :stroke-width="2.5"
                        />
                        {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                    </Button>
                </DialogFooter>

            </form>
        </DialogContent>
    </Dialog>
</template>
