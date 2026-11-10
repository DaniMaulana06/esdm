<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import { Pagination, PaginationContent, PaginationEllipsis, PaginationItem, PaginationNext, PaginationPrevious } from '@/components/ui/pagination';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Pencil, Trash2 } from 'lucide-vue-next';
import { route } from 'ziggy-js';

interface Bku {
    id: number;
    nama: string;
}

interface User {
    id: number;
    name: string;
    email: string;
    role: string;
    bku_id: number | null;
    bku?: Bku | null;
    email_verified_at: string | null;
    created_at: string;
}

interface Pagination<T> {
    current_page: number;
    data: T[];
    first_page_url: string;
    from: number | null;
    last_page: number;
    last_page_url: string;
    links: {
        url: string | null;
        label: string;
        active: boolean;
    }[];
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number | null;
    total: number;
}

interface Props {
    users: Pagination<User>;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Manajemen User',
        href: route('users.index'),
    },
];

const formatRole = (role: string) => {
    switch (role) {
        case 'admin':
            return 'Admin';

        case 'staf_dinas':
            return 'Staf Dinas';

        case 'operator_bku':
            return 'Operator BKU';

        default:
            return role;
    }
};

const deleteUser = (id: number) => {
    if (confirm('Apakah kamu yakin ingin menghapus user ini?')) {
        router.delete(
            route('users.destroy', {
                user: id,
            }),
        );
    }
};

const currentQuery = () => {
    const query = new URLSearchParams(window.location.search);
    const result: Record<string, string> = {};
    query.forEach((value, key) => {
        result[key] = value;
    });
    return result;
};

const goToPage = (page: number) => {
    router.get(
        route('users.index'),
        {
            ...currentQuery(),
            page,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};
</script>

<template>

    <Head title="Manajemen User" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">

            <!-- Header -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">
                        Manajemen User
                    </h1>

                    <p class="mt-1 text-sm text-gray-600">
                        Kelola akun pengguna sistem.
                    </p>
                </div>

                <Link :href="route('users.create')"
                    class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    + Tambah User
                </Link>
            </div>

            <!-- Table -->
            <div class="overflow-hidden rounded-lg border bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">
                                    No
                                </th>

                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">
                                    Nama
                                </th>

                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">
                                    Email
                                </th>

                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">
                                    Role
                                </th>

                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">
                                    BKU
                                </th>

                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">
                                    Status Email
                                </th>

                                <th class="px-6 py-3 text-center text-sm font-semibold text-gray-700">
                                    Aksi
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y">
                            <tr v-for="(user, index) in props.users.data" :key="user.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{
                                        (props.users.current_page - 1) *
                                        props.users.per_page +
                                        index +
                                        1
                                    }}
                                </td>

                                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                    {{ user.name }}
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ user.email }}
                                </td>

                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-700">
                                        {{ formatRole(user.role) }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ user.bku?.nama ?? '-' }}
                                </td>

                                <td class="px-6 py-4">
                                    <span v-if="user.email_verified_at"
                                        class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700">
                                        Terverifikasi
                                    </span>

                                    <span v-else
                                        class="inline-flex rounded-full bg-yellow-100 px-3 py-1 text-xs font-medium text-yellow-700">
                                        Belum Terverifikasi
                                    </span>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex justify-center gap-2">
                                        <Link :href="route('users.edit', {
                                            user: user.id,
                                        })
                                            "
                                            class="rounded-md bg-yellow-500 px-3 py-1.5 text-sm text-white hover:bg-yellow-600">
                                            <Pencil :size="16" :stroke-width="3" />
                                        </Link>

                                        <Button type="button" @click="deleteUser(user.id)"
                                            class="rounded-md bg-red-600 px-3 py-1.5 text-sm text-white hover:bg-red-700">
                                            <Trash2 :size="16" :stroke-width="3" />
                                        </Button>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="props.users.data.length === 0">
                                <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500">
                                    Belum ada data user.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="props.users.last_page > 1"
                    class="flex flex-col items-center justify-between gap-4 border-t px-4 py-4 sm:flex-row">

                    <!-- Navigasi halaman -->
                    <Pagination v-slot="{ page: activePage }" :items-per-page="users.per_page" :total="users.total"
                        :page="users.current_page" @update:page="goToPage">
                        <PaginationContent v-slot="{ items }">
                            <PaginationPrevious />

                            <template v-for="(item, index) in items" :key="index">
                                <PaginationItem v-if="item.type === 'page'" :value="item.value"
                                    :is-active="item.value === activePage">
                                    {{ item.value }}
                                </PaginationItem>

                                <PaginationEllipsis v-else :index="index" />
                            </template>

                            <PaginationNext />
                        </PaginationContent>
                    </Pagination>
                </div>
            </div>
        </div>
    </AppLayout>
</template>