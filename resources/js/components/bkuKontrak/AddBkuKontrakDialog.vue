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
import Label from '../ui/label/Label.vue';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '../ui/select';
import { Input } from '../ui/input';
import Button from '../ui/button/Button.vue';
import { Save } from 'lucide-vue-next';

interface Bku {
    id: number;
    nama: string;
}

interface Kontrak {
    id: number;
    nama: string;
}
const props = defineProps<{
    open: boolean;
    bkus: Bku[];
    kontraks: Kontrak[];
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    created: [];
}>();

const form = useForm({
    bku_id: '',
    kontrak_id: '',
    jumlah_sumur: '',
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
}

const submit = () => {
    form.post(route('bku-kontrak.store'), {
        preserveScroll: true,

        onSuccess: () => {
            form.reset();
            form.clearErrors();

            emit('update:open', false);
            emit('created');
        },
    });
}
</script>
<template>
    <Dialog :open="props.open" @update:open="(value) => {
        if (!value) closeDialog();
    }">

        <DialogContent class="sm:max-w-[525px]">
            <DialogHeader>
                <DialogTitle>
                    Tambah BKU Kontrak
                </DialogTitle>
                <DialogDescription>
                    Tambah BKU terikat Kontrak.
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submit" class="space-y-5">

                <!-- Select BKU -->
                <div class="space-y-2">
                    <Label for="nama">
                        Nama BKU
                    </Label>

                    <Select v-model="form.bku_id">
                        <SelectTrigger class="w-full">
                            <SelectValue placeholder="Pilih BKU" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectGroup>
                                <SelectItem v-for="bku in props.bkus" :key="bku.id" :value="String(bku.id)">
                                    {{ bku.nama }}
                                </SelectItem>
                            </SelectGroup>
                        </SelectContent>
                    </Select>

                    <p v-if="form.errors.bku_id" class="text-sm text-destructive">
                        {{ form.errors.bku_id }}
                    </p>
                </div>

                <!-- Kontrak -->
                <div class="space-y-2">
                    <Label for="kontrak_id">
                        Kontrak
                    </Label>

                    <Select v-model="form.kontrak_id">
                        <SelectTrigger class="w-full">
                            <SelectValue placeholder="Pilih Kontrak" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectGroup>
                                <SelectItem v-for="kontrak in props.kontraks" :key="kontrak.id" :value="kontrak.id">
                                    {{ kontrak.nama }}
                                </SelectItem>
                            </SelectGroup>
                        </SelectContent>
                    </Select>

                    <p v-if="form.errors.kontrak_id" class="text-sm text-destructive">
                        {{ form.errors.kontrak_id }}
                    </p>
                </div>

                <!-- Jumlah Sumur -->
                <div class="space-y-2">
                    <Label for="jumlah_sumur">
                        Jumlah Sumur
                    </Label>

                    <Input id="jumlah_sumur" v-model="form.jumlah_sumur" type="number" min="1"
                        placeholder="Masukkan jumlah sumur" />

                    <p v-if="form.errors.jumlah_sumur" class="text-sm text-destructive">
                        {{ form.errors.jumlah_sumur }}
                    </p>
                </div>

                <!-- Tombol -->
                <DialogFooter>
                    <Button type="button" variant="outline" @click="closeDialog" :disabled="form.processing">
                        Batal
                    </Button>

                    <Button type="submit" :disabled="form.processing" variant="submit">
                        <Save v-if="!form.processing" :size="18" :stroke-width="2.5" />
                        {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>