<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

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
    bkuKontraks: BkuKontrak[];
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

const submit = () => {
    form.post(route('sumur.store'), {
        preserveScroll: true,
    });
};
</script>

<template>

    <Head title="Tambah Sumur" />

    <AppLayout>
        <div class="max-w-2xl p-6">
            <Card>
                <CardHeader>
                    <CardTitle>Tambah Sumur</CardTitle>

                    <CardDescription>
                        Tambahkan data sumur minyak rakyat.
                    </CardDescription>
                </CardHeader>

                <CardContent>
                    <form @submit.prevent="submit" class="grid grid-cols-1 gap-6 md:grid-cols-2">

                        <!-- BKU Kontrak -->
                        <div class="space-y-2">
                            <Label for="bku_kontrak_id">
                                BKU Kontrak
                            </Label>

                            <select id="bku_kontrak_id" v-model="form.bku_kontrak_id"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                                <option value="" disabled>
                                    Pilih BKU Kontrak
                                </option>

                                <option v-for="item in props.bkuKontraks" :key="item.id" :value="item.id">
                                    {{ item.bku.nama }}
                                    -
                                    {{ item.kontrak.nama }}
                                </option>
                            </select>

                            <p v-if="form.errors.bku_kontrak_id" class="text-sm text-destructive">
                                {{ form.errors.bku_kontrak_id }}
                            </p>
                        </div>

                        <!-- Nama Sumur -->
                        <div class="space-y-2">
                            <Label for="nama_sumur">
                                Nama Sumur
                            </Label>

                            <Input id="nama_sumur" v-model="form.nama_sumur" type="text"
                                placeholder="Masukkan nama sumur" />

                            <p v-if="form.errors.nama_sumur" class="text-sm text-destructive">
                                {{ form.errors.nama_sumur }}
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

                        <!-- Kecamatan -->
                        <div class="space-y-2">
                            <Label for="kecamatan">
                                Kecamatan
                            </Label>

                            <Input id="kecamatan" v-model="form.kecamatan" type="text"
                                placeholder="Masukkan kecamatan" />

                            <p v-if="form.errors.kecamatan" class="text-sm text-destructive">
                                {{ form.errors.kecamatan }}
                            </p>
                        </div>

                        <!-- Kabupaten -->
                        <div class="space-y-2">
                            <Label for="kabupaten">
                                Kabupaten
                            </Label>

                            <Input id="kabupaten" v-model="form.kabupaten" type="text"
                                placeholder="Masukkan kabupaten" />

                            <p v-if="form.errors.kabupaten" class="text-sm text-destructive">
                                {{ form.errors.kabupaten }}
                            </p>
                        </div>

                        <!-- Latitude -->
                        <div class="space-y-2">
                            <Label for="latitude">
                                Latitude
                            </Label>

                            <Input id="latitude" v-model="form.latitude" type="text" inputmode="decimal" step="any"
                                placeholder="Contoh: -2.9761"
                                @input="form.latitude = form.latitude.replace(/,/g, '.')"
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

                            <Input id="longitude" v-model="form.longitude" type="text" step="any"
                                placeholder="Contoh: 104.7754"
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
                                {{
                                    form.processing
                                        ? 'Menyimpan...'
                                        : 'Simpan'
                                }}
                            </Button>
                        </div>

                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>