<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import Button from '@/components/ui/button/Button.vue';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import CardDescription from '@/components/ui/card/CardDescription.vue';
import CardHeader from '@/components/ui/card/CardHeader.vue';
import CardTitle from '@/components/ui/card/CardTitle.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { SharedData, type BreadcrumbItem } from '@/types';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
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

const getLaporanError = (
    index: number,
    field: 'bku_kontrak_id' | 'total_produksi' | 'total_lifting',
): string | undefined => {
    return form.errors[
        `laporan.${index}.${field}` as keyof typeof form.errors
    ];
};

const form = useForm({
    tanggal: new Date().toISOString().split('T')[0],
    laporan: props.bkuKontraks.map((item) => ({
        bku_kontrak_id: item.id,
        total_produksi: '',
        total_lifting: '',
        keterangan: '',
    })),
});

const submit = () => {
    form.post(route('laporan-harian.store'), {
        preserveScroll: true,
    });
};

const page = usePage<SharedData>();
const showFlash = ref(true);
const flash = computed(() => page.props.flash as {
    success?: string; error?: string
}
);
onMounted(() => {
    if (flash.value.success || flash.value.error) {
        setTimeout(() => {
            showFlash.value = false;
        }, 3000);
    }
});

const closeFlash = () => {
    showFlash.value = false;
};
</script>

<template>

    <Head title="Tambah Laporan Harian" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <Card>

                <!-- Header -->
                <CardHeader>
                    <CardTitle>
                        Tambah Laporan Harian
                    </CardTitle>

                    <CardDescription>
                        Isi data laporan produksi dan lifting harian.
                    </CardDescription>
                </CardHeader>

                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
    
                        <!-- 2 KOLOM -->
                        <div class="grid grid-cols-1 gap-3 lg:grid-cols-3 px-6">
    
                            <!-- KOLOM KIRI -->
                            <div class="lg:col-span-1">
    
                                <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                                    <div class="border-b border-gray-200 px-6 py-4">
                                        <h3 class="text-base font-semibold text-gray-800">
                                            Informasi Laporan
                                        </h3>
    
                                        <p class="mt-1 text-sm text-gray-500">
                                            Informasi dasar laporan.
                                        </p>
                                    </div>
    
                                    <div class="space-y-6 px-6 py-6">
    
                                        <!-- Nama / BKU -->
                                        <div>
                                            <Label class="mb-2 block text-sm font-medium text-gray-700">
                                                BKU
                                            </Label>
    
                                            <Input :model-value="props.bkuKontraks[0]?.bku?.nama ?? '-'" readonly
                                                class="bg-gray-50" />
                                        </div>
    
                                        <!-- Tanggal -->
                                        <div>
                                            <Label class="mb-2 block text-sm font-medium text-gray-700">
                                                Tanggal
                                            </Label>
    
                                            <Input v-model="form.tanggal" type="date" readonly class="bg-gray-50" />
    
                                            <InputError :message="form.errors.tanggal" class="mt-2" />
                                        </div>
    
                                    </div>
                                </div>
    
                            </div>
    
                            <!-- KOLOM KANAN -->
                            <div class="lg:col-span-2 mb-6">
    
                                <div v-if="Object.keys(form.errors).length > 0"
                                    class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 mb-3">
                                    <p class="text-sm font-medium text-red-800">
                                        Laporan hari sudah ada, ajukan justifikasi jika ingin merubah data.
                                    </p>
                                </div>
    
                                <!-- FLASH ERROR -->
                                <div v-if="showFlash && flash.error"
                                    class="mb-4 flex items-center justify-between rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700">
                                    <span>
                                        {{ flash.error }}
                                    </span>
    
                                    <button type="button" @click="closeFlash"
                                        class="ml-4 text-lg font-bold text-red-700 hover:text-red-900">
                                        ×
                                    </button>
                                </div>
    
                                <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                                    <div div class="border-b border-gray-200 px-6 py-4">
                                        <h3 class="text-base font-semibold text-gray-800">
                                            Laporan Kontrak
                                        </h3>
    
                                        <p class="mt-1 text-sm text-gray-500">
                                            Isi produksi dan lifting untuk setiap
                                            kontrak.
                                        </p>
                                    </div>
    
                                    <div class="space-y-3 px-6 py-6">
    
                                        <!-- KONTRAK -->
                                        <div v-for="(item, index) in props.bkuKontraks" :key="item.id"
                                            class="rounded-lg border border-gray-200">
    
                                            <!-- Nama kontrak -->
                                            <div class="border-b border-gray-200 bg-gray-50 px-4 py-3">
                                                <h4 class="text-sm font-semibold text-gray-800">
                                                    {{ item.kontrak.nama }}
                                                </h4>
                                            </div>
    
                                            <!-- Form -->
                                            <div class="grid grid-cols-1 gap-5 p-4 sm:grid-cols-2">
    
                                                <!-- Produksi -->
                                                <div>
                                                    <Label>
                                                        Total Produksi
                                                    </Label>
    
                                                    <Input v-model="form.laporan[index]
                                                        .total_produksi
                                                        " type="number" min="0" step="0.01"
                                                        placeholder="Masukkan produksi" />
    
                                                    <InputError :message="getLaporanError(index, 'total_produksi')"
                                                        class="mt-2" />
                                                </div>
    
                                                <!-- Lifting -->
                                                <div>
                                                    <Label class="mb-2 block text-sm font-medium text-gray-700">
                                                        Total Lifting
                                                    </Label>
    
                                                    <Input v-model="form.laporan[index]
                                                        .total_lifting
                                                        " type="number" min="0" step="0.01"
                                                        placeholder="Masukkan lifting" />
    
                                                    <InputError :message="getLaporanError(index, 'total_lifting')"
                                                        class="mt-2" />
                                                </div>
    
    
                                            </div>
                                            <!-- Keterangan -->
                                            <div class="px-4 pb-4">
                                                <Label class="mb-2 block text-sm font-medium text-gray-700">
                                                    Keterangan
                                                </Label>
    
                                                <Input v-model="form.laporan[index]
                                                    .keterangan
                                                    " type="text" min="0" step="0.01" placeholder="Keterangan" />
    
                                                <InputError :message="getLaporanError(index, 'total_lifting')"
                                                    class="mt-2" />
                                            </div>
                                        </div>
    
                                    </div>
                                    <!-- Error umum -->
                                    <div v-if="form.errors.laporan"
                                        class="rounded-lg border border-red-200 bg-red-50 px-4 py-3">
                                        <p class="text-sm text-red-600">
                                            {{ form.errors.laporan }}
                                        </p>
                                    </div>
    
    
                                    <!-- BUTTON -->
                                    <div class="flex items-center justify-end gap-3 mx-6 mb-4">
                                        <Button type="button" variant="outline" as-child>
                                            <Link :href="route('laporan-harian.index')">
                                                Batal
                                            </Link>
                                        </Button>
    
                                        <Button type="submit" :disabled="form.processing ||
                                            props.bkuKontraks.length === 0
                                            ">
                                            {{
                                                form.processing
                                                    ? 'Menyimpan...'
                                                    : 'Simpan Laporan'
                                            }}
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>