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
import Label from '@/Components/ui/label/Label.vue';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Save } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Kontrak BKU',
        href: route('bku-kontrak.index'),
    },
    {
        title: 'Tambah Kontrak BKU',
        href: route('bku-kontrak.create'),
    },
];
interface Bku {
    id: number;
    nama: string;
}

interface Kontrak {
    id: number;
    nama: string;
}

const props = defineProps<{
    bkus: Bku[];
    kontraks: Kontrak[];
}>();

const form = useForm({
    bku_id: '',
    kontrak_id: '',
    jumlah_sumur: '',
});

const submit = () => {
    form.post(route('bku-kontrak.store'), {
        preserveScroll: true,
    });
};
</script>

<template>

    <Head title="Tambah BKU Kontrak" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex w-full justify-center p-6">
            <div class="w-full max-w-xl">
                <Card>
                    <CardHeader>
                        <CardTitle>Tambah BKU Kontrak</CardTitle>

                        <CardDescription>
                            Tambahkan hubungan BKU dengan kontrak dan tentukan
                            jumlah sumur yang ditetapkan.
                        </CardDescription>
                    </CardHeader>

                    <CardContent>
                        <form @submit.prevent="submit" class="space-y-5">
                            <!-- BKU -->
                            <div class="space-y-2">
                                <Label for="bku_id">
                                    BKU
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
                                            <SelectItem v-for="kontrak in props.kontraks" :key="kontrak.id"
                                                :value="kontrak.id">
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
                            <div class="flex gap-3 pt-2 justify-end">
                                <Button variant="outline" as-child>
                                    <Link :href="route('bku-kontrak.index')">
                                        Batal
                                    </Link>
                                </Button>

                                <Button type="submit" :disabled="form.processing">
                                    <Save color="#ffffff" :stroke-width="2.5" v-if="!form.processing" />
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