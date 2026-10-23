<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import CardDescription from '@/components/ui/card/CardDescription.vue';
import CardTitle from '@/components/ui/card/CardTitle.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/Components/ui/label/Label.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Save } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Kontrak',
        href: route('kontrak.index'),
    },
    {
        title: 'Tambah Kontrak',
        href: route('kontrak.create'),
    },
];

interface Kontrak {
    id: number;
    nama: string;
    keterangan: number;
}

defineProps<{
    kontraks: Kontrak[];
}>();

const form = useForm({
    nama: '',
    keterangan: '',
});

const submit = () => {
    form.post(route('kontrak.store'), {
        preserveScroll: true,
    });
}
</script>


<template>

    <Head title="Tambah Kontrak" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex w-full justify-center p-8">
            <div class="w-full max-w-xl">
                <Card>
                    <CardHeader>
                        <CardTitle>Tambah Kontrak</CardTitle>
                        <CardDescription>Tambahkan Kontrak baru ke dalam sistem.</CardDescription>
                    </CardHeader>

                    <CardContent>
                        <form @submit.prevent="submit" class="space-y-5">

                            <!-- Nama Kontrak -->
                            <div class="space-y-2">
                                <Label for="name">Nama Kontrak</Label>
                                <Input id="name" v-model="form.nama" type="text" placeholder="Masukkan nama Kontrak" />
                                <p v-if="form.errors.nama" class="text-sm text-destructive">
                                    {{ form.errors.nama }}
                                </p>
                            </div>

                            <!-- Penetapan Kontrak -->
                            <div class="space-y-2">
                                <Label for="keterangan">Keterangan Kontrak</Label>
                                <Input id="keterangan" v-model="form.keterangan" placeholder="Masukkan penetapan BKU" />
                                <p v-if="form.errors.keterangan" class="text-sm text-destructive">
                                    {{ form.errors.keterangan }}
                                </p>
                            </div>

                            <!-- Tombol Action -->
                            <div class="flex gap-3 pt-2 justify-end">
                                <Button variant="outline" as-child>
                                    <Link :href="route('bku.index')" preserve-scroll prefetch>
                                        Batal
                                    </Link>
                                </Button>

                                <Button type="submit" :disabled="form.processing">
                                    <Save color="#ffffff" :stroke-width="2.5" v-if="!form.processing"/>
                                    {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                                </Button>
                            </div>

                        </form>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>