<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

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

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Laporan Harian',
        href: route('laporan-harian.index'),
    },
    {
        title: 'Tambah Laporan Harian',
        href: route('laporan-harian.create'),
    },
];

const form = useForm({
    bku_kontrak_id: '',
    tanggal: new Date().toISOString().split('T')[0], 
    total_produksi: '',
    total_lifting: '',
});

const submit = () => {
    form.post(route('laporan-harian.store'));
};
</script>

<template>
    <Head title="Tambah Laporan Harian" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="mb-6">
                <h1 class="text-2xl font-semibold text-gray-900">
                    Tambah Laporan Harian
                </h1>

                <p class="mt-1 text-sm text-gray-600">
                    Masukkan laporan produksi dan lifting harian berdasarkan kontrak.
                </p>
            </div>

            <div class="max-w-2xl overflow-hidden rounded-lg border bg-white shadow-sm">
                <form
                    @submit.prevent="submit"
                    class="space-y-6 p-6"
                >
                    <!-- BKU & KONTRAK -->
                    <div class="space-y-2">
                        <label
                            for="bku_kontrak_id"
                            class="text-sm font-medium text-gray-700"
                        >
                            BKU / Kontrak
                        </label>

                        <select
                            id="bku_kontrak_id"
                            v-model="form.bku_kontrak_id"
                            class="h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                        >
                            <option value="" disabled>
                                Pilih BKU / Kontrak
                            </option>

                            <option
                                v-for="item in props.bkuKontraks"
                                :key="item.id"
                                :value="item.id"
                            >
                                {{ item.bku.nama }} - {{ item.kontrak.nama }}
                            </option>
                        </select>

                        <p
                            v-if="form.errors.bku_kontrak_id"
                            class="text-sm text-red-600"
                        >
                            {{ form.errors.bku_kontrak_id }}
                        </p>
                    </div>

                    <!-- TANGGAL -->
                    <div class="space-y-2">
                        <label
                            for="tanggal"
                            class="text-sm font-medium text-gray-700"
                        >
                            Tanggal Laporan
                        </label>

                        <Input
                            id="tanggal"
                            v-model="form.tanggal"
                            type="date"
                            readonly

                            class="h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                        />

                        <p
                            v-if="form.errors.tanggal"
                            class="text-sm text-red-600"
                        >
                            {{ form.errors.tanggal }}
                        </p>
                    </div>

                    <!-- TOTAL PRODUKSI -->
                    <div class="space-y-2">
                        <Label
                            for="total_produksi"
                            class="text-sm font-medium text-gray-700"
                        >
                            Total Produksi
                        </Label>

                        <input
                            id="total_produksi"
                            v-model="form.total_produksi"
                            type="number"
                            min="0"
                            step="0.01"
                            placeholder="Contoh: 100"
                            class="h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                        />

                        <p class="text-xs text-gray-500">
                            Masukkan jumlah produksi dalam satuan yang digunakan oleh Dinas.
                        </p>

                        <p
                            v-if="form.errors.total_produksi"
                            class="text-sm text-red-600"
                        >
                            {{ form.errors.total_produksi }}
                        </p>
                    </div>

                    <!-- TOTAL LIFTING -->
                    <div class="space-y-2">
                        <Label
                            for="total_lifting"
                            class="text-sm font-medium text-gray-700"
                        >
                            Total Lifting
                        </Label>

                        <input
                            id="total_lifting"
                            v-model="form.total_lifting"
                            type="number"
                            min="0"
                            step="0.01"
                            placeholder="Contoh: 95"
                            class="h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                        />

                        <p
                            v-if="form.errors.total_lifting"
                            class="text-sm text-red-600"
                        >
                            {{ form.errors.total_lifting }}
                        </p>
                    </div>

                    <!-- BUTTON -->
                    <div class="flex items-center justify-end gap-3 border-t pt-6">
                        <Link
                            :href="route('laporan-harian.index')"
                            class="rounded-lg border px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            Batal
                        </Link>

                        <Button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Laporan' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>