<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import CardDescription from '@/components/ui/card/CardDescription.vue';
import CardHeader from '@/components/ui/card/CardHeader.vue';
import CardTitle from '@/components/ui/card/CardTitle.vue';
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
    bkuKontrak: BkuKontrak;
    bkus: Bku[];
    kontraks: Kontrak[];
}>();

const form = useForm({
    bku_id: props.bkuKontrak.bku_id.toString(),
    kontrak_id: props.bkuKontrak.kontrak_id.toString(),
    jumlah_sumur: props.bkuKontrak.jumlah_sumur.toString(),
});

const submit = () => {
    form.put(
        route('bku-kontrak.update', {
            bku_kontrak: props.bkuKontrak.id,
        }),
        {
            preserveScroll: true,
        }
    );
};
</script>


<template>

    <Head title="Edit Kontrak" />

    <AppLayout>
        <div class="p-6 max-w-xl">
            <Card>

                <CardHeader>
                    <CardTitle class="text-2xl font-semibold text-gray-900">
                        Edit BKU Kontrak
                    </CardTitle>
                    <CardDescription class="mt-1 text-sm text-gray-600">
                        Perbarui data Badan Kerjasama Usaha terikat Kontrak.
                    </CardDescription>
                </CardHeader>

                <CardContent>
                    <form @submit.prevent="submit" class="space-y-5">
                        <!-- BKU -->
                        <div class="mb-2 block text-sm font-medium text-gray-700">
                            <Label for="bku_id">
                                BKU
                            </Label>

                            <select id="bku_id" v-model="form.bku_id"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                                <option value="" disabled>
                                    Pilih BKU
                                </option>

                                <option v-for="bku in props.bkus" :key="bku.id" :value="bku.id">
                                    {{ bku.nama }}
                                </option>
                            </select>

                            <p v-if="form.errors.bku_id" class="text-sm text-destructive">
                                {{ form.errors.bku_id }}
                            </p>
                        </div>

                        <!-- Kontrak -->
                        <div class="mb-2 block text-sm font-medium text-gray-700">
                            <Label for="kontrak_id">
                                Kontrak
                            </Label>

                            <select id="kontrak_id" v-model="form.kontrak_id"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                                <option value="" disabled>
                                    Pilih Kontrak
                                </option>

                                <option v-for="kontrak in props.kontraks" :key="kontrak.id" :value="kontrak.id">
                                    {{ kontrak.nama }}
                                </option>
                            </select>

                            <p v-if="form.errors.kontrak_id" class="text-sm text-destructive">
                                {{ form.errors.kontrak_id }}
                            </p>
                        </div>

                        <!-- Jumlah Sumur -->
                        <div class="mb-2 block text-sm font-medium text-gray-700">
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
                        <div class="flex gap-3 pt-2">
                            <Button variant="outline" as-child>
                                <Link :href="route('bku-kontrak.index')">
                                    Batal
                                </Link>
                            </Button>

                            <Button type="submit" :disabled="form.processing"
                                class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50">
                                {{
                                    form.processing
                                        ? 'Meperbarui...'
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