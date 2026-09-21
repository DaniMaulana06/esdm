<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import { CardContent } from '@/components/ui/card';
import Card from '@/components/ui/card/Card.vue';
import CardDescription from '@/components/ui/card/CardDescription.vue';
import CardHeader from '@/components/ui/card/CardHeader.vue';
import CardTitle from '@/components/ui/card/CardTitle.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';


interface Kontrak {
    id: number;
    nama: string;
    keterangan: string;
}

const props = defineProps<{
    kontrak: Kontrak;
}>();

const form = useForm({
    nama: props.kontrak.nama,
    keterangan: props.kontrak.keterangan,
});

const submit = () => {
    form.put(route('kontrak.update', { kontrak: props.kontrak.id }));
}
</script>


<template>

    <Head title="Edit Kontrak" />

    <AppLayout>
        <div class="p-6 max-w-xl">
            <Card>
                <CardHeader>
                    <CardTitle class="text-2xl font-semibold text-gray-900">
                        Edit Kontrak
                    </CardTitle>
                    <CardDescription class="mt-1 text-sm text-gray-600">
                        Perbarui data Kontrak.
                    </CardDescription>
                </CardHeader>

                <CardContent>
                    <form @submit.prevent="submit" class="space-y-5">

                        <div>
                            <Label for="nama" class="mb-2 block text-sm font-medium text-gray-700">
                                Nama Kontrak
                            </Label>

                            <Input id="nama" v-model="form.nama" type="text"
                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 p-2" />

                            <p v-if="form.errors.nama" class="mt-1 text-sm text-red-600">
                                {{ form.errors.nama }}
                            </p>
                        </div>

                        <div>
                            <Label for="keterangan" class="mb-2 block text-sm font-medium text-gray-700">
                                Keterangan
                            </Label>

                            <Input id="keterangan" v-model="form.keterangan" type="text"
                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 p-2" />

                            <p v-if="form.errors.keterangan" class="mt-1 text-sm text-red-600">
                                {{ form.errors.keterangan }}
                            </p>
                        </div>

                        <!-- Tombol -->
                        <div class="flex gap-3">
                            <Link :href="route('kontrak.index')"
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