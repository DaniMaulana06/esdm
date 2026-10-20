<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import { CardDescription, CardTitle } from '@/components/ui/card';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import CardHeader from '@/components/ui/card/CardHeader.vue';
import Input from '@/components/ui/input/Input.vue';
import { Label } from '@/Components/ui/label';
import { Select } from '@/components/ui/select';
import SelectContent from '@/components/ui/select/SelectContent.vue';
import SelectGroup from '@/components/ui/select/SelectGroup.vue';
import SelectItem from '@/components/ui/select/SelectItem.vue';
import SelectTrigger from '@/components/ui/select/SelectTrigger.vue';
import SelectValue from '@/components/ui/select/SelectValue.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import daftarKota from '@/data/kota_sumsel.json';
import { BreadcrumbItem } from '@/types';
import { computed } from 'vue';

interface Bku {
    id: number;
    nama: string;
    kota: string;
    penetapan: number;
}

const props = defineProps<{
    bku: Bku;
}>();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { 
        title: 'Badan Kerja Sama Usaha', 
        href: route('bku.index') 
    },
    {
        title: 'Edit Badan Kerjasama Usaha',
        href: route('bku.edit', { bku: props.bku.id }),
    },
]);


const form = useForm({
    nama: props.bku.nama,
    kota: props.bku.kota,
    penetapan: props.bku.penetapan,
});

const submit = () => {
    form.put(route('bku.update', { bku: props.bku.id }));
}
</script>


<template>

    <Head title="Edit BKU" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 max-w-xl">
            <Card>

                <CardHeader>
                    <CardTitle class="text-2xl font-semibold text-gray-900">
                        Edit Badan Kerjasama Usaha
                    </CardTitle>
                    <CardDescription class="mt-1 text-sm text-gray-600">
                        Perbarui data Badan Kerjasama Usaha.
                    </CardDescription>
                </CardHeader>


                <CardContent>
                    <form @submit.prevent="submit" class="space-y-5">

                        <div>
                            <Label for="name" class="mb-2 block text-sm font-medium text-gray-700">
                                Nama BKU
                            </Label>

                            <Input id="nama" v-model="form.nama" type="text"
                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 p-2" />

                            <p v-if="form.errors.nama" class="mt-1 text-sm text-red-600">
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

                        <div>
                            <Label for="penetapan" class="mb-2 block text-sm font-medium text-gray-700">
                                Penetapan
                            </Label>

                            <Input id="penetapan" v-model="form.penetapan" type="number" min="0"
                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 p-2" />

                            <p v-if="form.errors.penetapan" class="mt-1 text-sm text-red-600">
                                {{ form.errors.penetapan }}
                            </p>
                        </div>

                        <!-- Tombol -->
                        <div class="flex gap-3">
                            <Link :href="route('bku.index')"
                                class="rounded-lg border px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                                prefetch>
                                Batal
                            </Link>

                            <Button type="submit" :disabled="form.processing"
                                class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50">
                                {{ form.processing ? 'Memperbarui...' : 'Update' }}
                            </Button>
                        </div>

                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>