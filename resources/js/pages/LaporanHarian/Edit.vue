```vue
<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Link } from '@inertiajs/vue3';
import CardDescription from '@/components/ui/card/CardDescription.vue';

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

interface LaporanHarian {
    id: number;
    bku_kontrak_id: number;
    tanggal: string;
    total_produksi: string;
    total_lifting: string;
    keterangan: string;
    bku_kontrak: BkuKontrak;
}

interface Props {
    laporanHarian: LaporanHarian;
}

const props = defineProps<Props>();
console.log('LAPORAN:', props.laporanHarian);
console.log('BKU KONTRAK:', props.laporanHarian.bku_kontrak);
console.log('BKU:', props.laporanHarian.bku_kontrak?.bku);
console.log('KONTRAK:', props.laporanHarian.bku_kontrak?.kontrak);
console.log('TANGGAL:', props.laporanHarian.tanggal);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Laporan Harian',
        href: route('laporan-harian.index'),
    },
    {
        title: 'Edit',
        href: route('laporan-harian.edit', {
            laporan_harian: props.laporanHarian.id,
        }),
    },
];


const form = useForm({
    total_produksi: props.laporanHarian.total_produksi,
    total_lifting: props.laporanHarian.total_lifting,
    keterangan: props.laporanHarian.keterangan,
});

const submit = () => {
    form.put(
        route('laporan-harian.update', {
            laporan_harian: props.laporanHarian.id,
        }),
    );
};

const formatTanggal = (tanggal: string) => {
    return new Date(tanggal).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    });
};
</script>

<template>

    <Head title="Edit Laporan Harian" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <Card>
                <CardHeader>
                    <CardTitle>
                        Edit Laporan Harian
                    </CardTitle>

                    <CardDescription>
                        Perbaiki data produksi dan lifting pada laporan harian.
                    </CardDescription>
                </CardHeader>

                <CardContent>
                    <form class="space-y-6" @submit.prevent="submit">

                        <!-- 2 Kolom -->
                        <div class="grid grid-cols-1 gap-3 lg:grid-cols-3 px-6">

                            <!-- kolom kiri -->
                            <div class="lg:col-span-1">

                                <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">

                                    <div class="space-y-6 px-6 py-6">
                                        <!-- BKU -->
                                        <div>
                                            <Label for="bku">BKU</Label>

                                            <Input id="bku"
                                                :model-value="props.laporanHarian.bku_kontrak?.bku?.nama ?? ''" readonly
                                                class="bg-muted" />
                                        </div>

                                        <!-- Kontrak -->
                                        <div>
                                            <Label for="kontrak">Kontrak</Label>

                                            <Input id="kontrak" :model-value="props.laporanHarian.bku_kontrak?.kontrak?.nama ?? ''
                                                " readonly class="bg-muted" />
                                        </div>

                                        <!-- Tanggal -->
                                        <div>
                                            <Label for="tanggal">Tanggal</Label>

                                            <Input id="tanggal"
                                                :model-value="formatTanggal(props.laporanHarian.tanggal)" readonly
                                                class="bg-muted" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- kolom kanan -->
                            <div class="lg:col-span-2 mb-6">

                                <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                                    <div class="space-y-3 px-6 py-6">

                                        <!-- Total Produksi -->
                                        <div>
                                            <Label for="total_produksi">
                                                Total Produksi
                                            </Label>

                                            <Input id="total_produksi" v-model="form.total_produksi" type="number"
                                                step="any" min="0" placeholder="Masukkan total produksi" />

                                            <p v-if="form.errors.total_produksi" class="text-sm text-destructive">
                                                {{ form.errors.total_produksi }}
                                            </p>
                                        </div>

                                        <!-- Total Lifting -->
                                        <div>
                                            <Label for="total_lifting">
                                                Total Lifting
                                            </Label>

                                            <Input id="total_lifting" v-model="form.total_lifting" type="number"
                                                step="any" min="0" placeholder="Masukkan total lifting" />

                                            <p v-if="form.errors.total_lifting" class="text-sm text-destructive">
                                                {{ form.errors.total_lifting }}
                                            </p>
                                        </div>

                                        <!-- Keterangan -->
                                        <div>
                                            <Label for="keterangan">
                                                Keterangan
                                            </Label>

                                            <Input id="keterangan" v-model="form.keterangan" type="text" step="any"
                                                placeholder="Keterangan" />

                                            <p v-if="form.errors.keterangan" class="text-sm text-destructive">
                                                {{ form.errors.keterangan }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <!-- Tombol -->
                                <div class="flex justify-end gap-3 mt-4">
                                    <Button type="button" variant="outline" as-child>
                                        <Link :href="route('laporan-harian.index')
                                            ">
                                            Batal
                                        </Link>
                                    </Button>

                                    <Button type="submit" :disabled="form.processing"
                                        class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50">

                                        {{
                                            form.processing
                                                ? 'Memperbarui...'
                                                : 'Update'
                                        }}
                                    </Button>
                                </div>

                            </div>
                        </div>

                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
```
