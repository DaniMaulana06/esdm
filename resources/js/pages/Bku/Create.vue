<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import CardDescription from '@/components/ui/card/CardDescription.vue';
import CardTitle from '@/components/ui/card/CardTitle.vue';
import Input from '@/components/ui/input/Input.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import daftarKota from '@/data/kota_sumsel.json';
import Select from '@/components/ui/select/Select.vue';
import Label from '@/Components/ui/label/Label.vue';
import SelectTrigger from '@/components/ui/select/SelectTrigger.vue';
import SelectValue from '@/components/ui/select/SelectValue.vue';
import SelectContent from '@/components/ui/select/SelectContent.vue';
import SelectGroup from '@/components/ui/select/SelectGroup.vue';
import SelectItem from '@/components/ui/select/SelectItem.vue';
import { BreadcrumbItem } from '@/types';
import { Save, Upload } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Badan Kerja Sama usaha',
        href: route('bku.index'),
    },
    {
        title: 'Tambah Badan Kerjasama Usaha',
        href: route('bku.create'),
    },
];

interface Bku {
    id: number;
    nama: string;
    kota: string;
    penetapan: number;
}

defineProps<{
    bkus: Bku[];
}>();

const form = useForm({
    nama: '',
    kota: '',
    penetapan: '',
});

const submit = () => {
    form.post(route('bku.store'), {
        preserveScroll: true,
    });
}
</script>


<template>

    <Head title="Tambah BKU" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex w-full justify-center p-6">
            <div class="w-full max-w-xl">
                <Card>
                    <CardHeader>
                        <CardTitle>Tambah BKU</CardTitle>
                        <CardDescription>Tambahkan BKU baru ke dalam sistem.</CardDescription>
                    </CardHeader>
    
                    <CardContent>
                        <form @submit.prevent="submit" class="space-y-5">
    
                            <!-- Nama BKU -->
                            <div class="space-y-2">
                                <Label for="name">Nama BKU</Label>
                                <Input id="name" v-model="form.nama" type="text" placeholder="Masukkan nama BKU" />
                                <p v-if="form.errors.nama" class="text-sm text-destructive">
                                    {{ form.errors.nama }}
                                </p>
                            </div>
    
                            <!-- Kabupaten / Kota -->
                            <div class="space-y-2">
                                <Label for="kota">Kabupaten / Kota</Label>
                                <Select id="kota" v-model="form.kota">
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
                                <p v-if="form.errors.kota" class="text-sm text-destructive">
                                    {{ form.errors.kota }}
                                </p>
                            </div>
    
                            <!-- Penetapan BKU -->
                            <div class="space-y-2">
                                <Label for="penetapan">Pentapan BKU</Label>
                                <Input id="penetapan" v-model="form.penetapan" type="number" min="0"
                                    placeholder="Masukkan penetapan BKU" />
                                <p v-if="form.errors.penetapan" class="text-sm text-destructive">
                                    {{ form.errors.penetapan }}
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