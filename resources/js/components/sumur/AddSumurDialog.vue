<script setup lang="ts">
import { watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { Link, Save } from 'lucide-vue-next';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import daftarKota from '@/data/kota_sumsel.json';
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
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

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
const props = defineProps<{
    open: boolean;
    bkuKontraks: BkuKontrak[];
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    created: [];
}>();

const form = useForm({
    bku_kontrak_id: '',
    nama_sumur: '',
    desa: '',
    kecamatan: '',
    kabupaten: '',
    latitude: '',
    longitude: '',
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
    form.post(route('sumur.store'), {
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

            <form @submit.prevent="submit" class="grid grid-cols-1 gap-6 md:grid-cols-2">

                <!-- Kontrak -->
                <div class="space-y-2">
                    <Label for="bku_kontrak_id">
                        Kontrak
                    </Label>

                    <Select v-model="form.bku_kontrak_id">
                        <SelectTrigger class="w-[280px]">
                            <SelectValue placeholder="Pilih Kontrak" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectGroup>
                                <SelectItem value="all">Semua Kontrak</SelectItem>
                                <SelectItem v-for="item in props.bkuKontraks" :key="item.id" :value="String(item.id)">
                                    {{ item.bku.nama }}
                                    -
                                    {{ item.kontrak.nama }}
                                </SelectItem>
                            </SelectGroup>
                        </SelectContent>
                    </Select>

                    <p v-if="form.errors.bku_kontrak_id" class="text-sm text-destructive">
                        {{ form.errors.bku_kontrak_id }}
                    </p>
                </div>

                <!-- Nama Sumur -->
                <div class="space-y-2">
                    <Label for="nama_sumur">
                        Nama Sumur
                    </Label>

                    <Input id="nama_sumur" v-model="form.nama_sumur" type="text" placeholder="Masukkan nama sumur" />

                    <p v-if="form.errors.nama_sumur" class="text-sm text-destructive">
                        {{ form.errors.nama_sumur }}
                    </p>
                </div>

                <!-- Kabupaten / Kota -->
                <div class="space-y-2">
                    <Label for="kota">Kabupaten / Kota</Label>
                    <Select id="kota" v-model="form.kabupaten">
                        <SelectTrigger class="w-full">
                            <SelectValue placeholder="Pilih Kabupaten/Kota" />
                        </SelectTrigger>

                        <SelectContent>
                            <SelectGroup class="h-[280px]">
                                <SelectItem v-for="item in daftarKota" :key="item.id" :value="item.nama">
                                    {{ item.nama_resmi }}
                                </SelectItem>
                            </SelectGroup>
                        </SelectContent>
                    </Select>
                    <p v-if="form.errors.kabupaten" class="text-sm text-destructive">
                        {{ form.errors.kabupaten }}
                    </p>
                </div>

                <!-- Kecamatan -->
                <div class="space-y-2">
                    <Label for="kecamatan">
                        Kecamatan
                    </Label>

                    <Input id="kecamatan" v-model="form.kecamatan" type="text" placeholder="Masukkan kecamatan" />

                    <p v-if="form.errors.kecamatan" class="text-sm text-destructive">
                        {{ form.errors.kecamatan }}
                    </p>
                </div>

                <!-- Desa -->
                <div class="space-y-2">
                    <Label for="desa">
                        Desa
                    </Label>

                    <Input id="desa" v-model="form.desa" type="text" placeholder="Masukkan desa" />

                    <p v-if="form.errors.desa" class="text-sm text-destructive">
                        {{ form.errors.desa }}
                    </p>
                </div>


                <!-- Latitude -->
                <div class="space-y-2">
                    <Label for="latitude">
                        Latitude
                    </Label>

                    <Input id="latitude" v-model="form.latitude" type="text" inputmode="decimal" step="any"
                        placeholder="Contoh: -2.9761" @input="form.latitude = form.latitude.replace(/,/g, '.')"
                        class="[appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none" />

                    <p v-if="form.errors.latitude" class="text-sm text-destructive">
                        {{ form.errors.latitude }}
                    </p>
                </div>

                <!-- Longitude -->
                <div class="space-y-2">
                    <Label for="longitude">
                        Longitude
                    </Label>

                    <Input id="longitude" v-model="form.longitude" type="text" step="any" placeholder="Contoh: 104.7754"
                        @input="form.longitude = form.longitude.replace(/,/g, '.')"
                        class="[appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none" />

                    <p v-if="form.errors.longitude" class="text-sm text-destructive">
                        {{ form.errors.longitude }}
                    </p>
                </div>

                <!-- Button -->
                <div class="flex justify-end gap-3 md:col-span-2">
                    <Button variant="outline" as-child>
                        <Link :href="route('sumur.index')">
                        Batal
                        </Link>
                    </Button>

                    <Button type="submit" :disabled="form.processing">
                        <Save color="#ffffff" :stroke-width="2.5" v-if="!form.processing" />
                        {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                    </Button>
                </div>

            </form>
        </DialogContent>
    </Dialog>
</template>
