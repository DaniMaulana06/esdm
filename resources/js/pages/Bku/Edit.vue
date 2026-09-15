<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';


interface Bku {
    id: number;
    nama: string;
    penetapan: number;
}

const props = defineProps<{
    bku: Bku;
}>();

const form = useForm({
    nama: props.bku.nama,
    penetapan: props.bku.penetapan,
});

const submit = () => {
    form.put(route('bku.update', { bku: props.bku.id }));
}
</script>


<template>

    <Head title="Edit BKU" />

    <AppLayout>
        <div class="p-6">
            <div class="mb-6">
                <h1 class="text-2xl font-semibold text-gray-900">
                    Edit Badan Kerjasama Usaha
                </h1>
                <p class="mt-1 text-sm text-gray-600">
                    Perbarui data Badan Kerjasama Usaha.
                </p>
            </div>

            <div class="max-w-xl rounded-lg border bg-white p-6 shadow-sm">
                <form @submit.prevent="submit" class="space-y-5">

                    <div>
                        <label
                            for="name"
                            class="mb-2 block text-sm font-medium text-gray-700"
                        >
                            Nama BKU
                        </label>

                        <input
                            id="nama"
                            v-model="form.nama"
                            type="text"
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 p-1"
                        />

                        <p
                            v-if="form.errors.nama"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.nama }}
                        </p>
                    </div>

                    <div>
                        <label
                            for="penetapan"
                            class="mb-2 block text-sm font-medium text-gray-700"
                        >
                            Penetapan
                        </label>

                        <input
                            id="penetapan"
                            v-model="form.penetapan"
                            type="number"
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 p-1"
                        />

                        <p
                            v-if="form.errors.penetapan"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.penetapan }}
                        </p>
                    </div>

                    <!-- Tombol -->
                    <div class="flex gap-3">
                        <Link
                            :href="route('bku.index')"
                            class="rounded-lg border px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50" prefetch
                        >
                            Batal
                        </Link>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
                        >
                            {{ form.processing ? 'Memperbarui...' : 'Update' }}
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </AppLayout>
</template>