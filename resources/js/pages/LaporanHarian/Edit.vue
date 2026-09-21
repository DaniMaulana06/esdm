```vue
<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Save } from 'lucide-vue-next';
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
        <div class="p-6 max-w-xl">
            <Card>
                <CardHeader>
                    <CardTitle class="text-2xl font-semibold">
                        Edit Laporan Harian
                    </CardTitle>

                    <CardDescription class="text-sm text-muted-foreground">
                        Perbaiki data produksi dan lifting pada laporan harian.
                    </CardDescription>
                </CardHeader>

                <CardContent>
                    <form class="space-y-6" @submit.prevent="submit">
                        <!-- BKU -->
                        <div class="space-y-2">
                            <Label for="bku">BKU</Label>

                            <Input id="bku" :model-value="props.laporanHarian.bku_kontrak?.bku?.nama ?? ''" readonly
                                class="bg-muted" />
                        </div>

                        <!-- Kontrak -->
                        <div class="space-y-2">
                            <Label for="kontrak">Kontrak</Label>

                            <Input id="kontrak" :model-value="props.laporanHarian.bku_kontrak?.kontrak?.nama ?? ''
                                " readonly class="bg-muted" />
                        </div>

                        <!-- Tanggal -->
                        <div class="space-y-2">
                            <Label for="tanggal">Tanggal</Label>

                            <Input id="tanggal" :model-value="formatTanggal(props.laporanHarian.tanggal)" readonly
                                class="bg-muted" />
                        </div>

                        <!-- Total Produksi -->
                        <div class="space-y-2">
                            <Label for="total_produksi">
                                Total Produksi
                            </Label>

                            <Input id="total_produksi" v-model="form.total_produksi" type="number" step="any" min="0"
                                placeholder="Masukkan total produksi" />

                            <p v-if="form.errors.total_produksi" class="text-sm text-destructive">
                                {{ form.errors.total_produksi }}
                            </p>
                        </div>

                        <!-- Total Lifting -->
                        <div class="space-y-2">
                            <Label for="total_lifting">
                                Total Lifting
                            </Label>

                            <Input id="total_lifting" v-model="form.total_lifting" type="number" step="any" min="0"
                                placeholder="Masukkan total lifting" />

                            <p v-if="form.errors.total_lifting" class="text-sm text-destructive">
                                {{ form.errors.total_lifting }}
                            </p>
                        </div>

                        <!-- Tombol -->
                        <div class="flex justify-end gap-3">
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
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
```
