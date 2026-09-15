<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import CardDescription from '@/components/ui/card/CardDescription.vue';
import CardTitle from '@/components/ui/card/CardTitle.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';


interface BkuKontrak {
    id: number;
    nomor_kontrak: string;
}

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
}
</script>


<template>

    <Head title="Tambah Sumur" />

    <AppLayout>
        <div class="max-w-2xl p-6">
            <Card>
                <CardHeader>
                    <CardTitle>Tambah Sumur</CardTitle>

                    <CardDescription>
                        Tambahkan data sumur minyak rakyat ke dalam sistem.
                    </CardDescription>
                </CardHeader>

                <CardContent>
                    <form @submit.prevent="submit" class="space-y-5">
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

                            <Input id="desa" v-model="form.desa" type="text" placeholder="Masukkan nama desa" />

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
                                placeholder="Masukkan nama kecamatan" />

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
                                placeholder="Masukkan nama kabupaten" />

                            <p v-if="form.errors.kabupaten" class="text-sm text-destructive">
                                {{ form.errors.kabupaten }}
                            </p>
                        </div>

                        <!-- Latitude -->
                        <div class="space-y-2">
                            <Label for="latitude">
                                Latitude
                            </Label>

                            <Input id="latitude" v-model="form.latitude" type="number" step="any"
                                placeholder="Contoh: -2.990934123" />

                            <p v-if="form.errors.latitude" class="text-sm text-destructive">
                                {{ form.errors.latitude }}
                            </p>
                        </div>

                        <!-- Longitude -->
                        <div class="space-y-2">
                            <Label for="longitude">
                                Longitude
                            </Label>

                            <Input id="longitude" v-model="form.longitude" type="number" step="any"
                                placeholder="Contoh: 104.75655412" />

                            <p v-if="form.errors.longitude" class="text-sm text-destructive">
                                {{ form.errors.longitude }}
                            </p>
                        </div>

                        <!-- Tombol -->
                        <div class="flex gap-3 pt-2">
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