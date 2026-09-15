<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem } from '@/types';
import { Head, router, Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { route } from 'ziggy-js';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Badan Kerjasama Usaha',
        href: route('bku.index'),
    }
];

interface Bku {
    id: number;
    name: string;
    penetapan: number;
    created_at: string;
    updated_at: string;
}

const deleteItem = (id: number) => {
    if (confirm('apakah kamu yakin ingin menghapus bku ini?')) {
        router.delete(route('bku.destroy', id));
    }
}

// const page = usePage();
// const flash = computed(() => page.props.flash as { success?: string; error?: string });
defineProps<{
    bkus: Bku[];
}>();
</script>

<template>

    <Head title="BKU" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">
                        Badan Kerjasama Usaha
                    </h1>

                    <p class="mt-1 text-sm text-gray-600">
                        Kelola data BKU.
                    </p>
                </div>
                <Link :href="route('bku.create')" prefetch
                    class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    + Tambah BKU
                </Link>
            </div>

            <div class="overflow-hidden rounded-lg border bg-white shadow-sm">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">
                                No
                            </th>

                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">
                                Nama BKU
                            </th>

                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">
                                Penetapan
                            </th>

                            <th class="px-6 py-3 text-center text-sm font-semibold text-gray-700">
                                Aksi
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y">
                        <tr v-for="(bku, index) in bkus" :key="bku.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ index + 1 }}
                            </td>

                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                {{ bku.name }}
                            </td>

                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                {{ bku.penetapan }}
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex justify-center gap-2">
                                    <Link :href="route('bku.edit', { bku: bku.id })"
                                        class="rounded-md bg-yellow-500 px-3 py-1.5 text-sm text-white hover:bg-yellow-600">
                                        Edit
                                    </Link>

                                    <button @click="deleteItem(bku.id)"
                                        class="rounded-md bg-red-600 px-3 py-1.5 text-sm text-white hover:bg-red-700">
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="bkus.length === 0">
                            <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">
                                Belum ada data Bku.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>