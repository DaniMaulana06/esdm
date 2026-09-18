<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import CardTitle from '@/components/ui/card/CardTitle.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
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
}

interface Props {
    user: User;
    bkus: Bku[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'User Management',
        href: route('users.index'),
    },
    {
        title: 'Edit User',
        href: route('users.edit', {
            user: props.user.id,
        }),
    },
];

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    role: props.user.role,
    bku_id: props.user.bku_id ? String(props.user.bku_id) : '',
    password: '',
    password_confirmation: '',
});

const isOperatorBku = computed(() => {
    return form.role === 'operator_bku';
});

const submit = () => {
    form.put(
        route('users.update', {
            user: props.user.id,
        }),
    );
};
</script>

<template>

    <Head title="Edit User" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">

            <!-- Header -->
            <div class="mb-6">
                <CardTitle class="text-2xl font-semibold text-gray-900">
                    Edit User
                </CardTitle>

                <p class="mt-1 text-sm text-gray-600">
                    Perbarui informasi akun pengguna.
                </p>
            </div>

            <!-- Form -->
            <div class="max-w-2xl overflow-hidden rounded-lg border bg-white shadow-sm">
                <form @submit.prevent="submit" class="space-y-6 p-6">

                    <!-- Nama -->
                    <div class="space-y-2">
                        <Label for="name" class="text-sm font-medium text-gray-700">
                            Nama
                        </Label>

                        <input id="name" v-model="form.name" type="text"
                            class="h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" />

                        <p v-if="form.errors.name" class="text-sm text-red-600">
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <!-- Email -->
                    <div class="space-y-2">
                        <Label for="email" class="text-sm font-medium text-gray-700">
                            Email
                        </Label>

                        <input id="email" v-model="form.email" type="email"
                            class="h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" />

                        <p v-if="form.errors.email" class="text-sm text-red-600">
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <!-- Role -->
                    <div class="space-y-2">
                        <Label for="role" class="text-sm font-medium text-gray-700">
                            Role
                        </Label>

                        <select id="role" v-model="form.role"
                            class="h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                            <option value="staf_dinas">
                                Staf Dinas
                            </option>

                            <option value="operator_bku">
                                Operator BKU
                            </option>
                        </select>

                        <p v-if="form.errors.role" class="text-sm text-red-600">
                            {{ form.errors.role }}
                        </p>
                    </div>

                    <!-- BKU -->
                    <div v-if="isOperatorBku" class="space-y-2">
                        <Label for="bku_id" class="text-sm font-medium text-gray-700">
                            BKU
                        </Label>

                        <select id="bku_id" v-model="form.bku_id"
                            class="h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                            <option value="" disabled>
                                Pilih BKU
                            </option>

                            <option v-for="bku in props.bkus" :key="bku.id" :value="bku.id">
                                {{ bku.nama }}
                            </option>
                        </select>

                        <p v-if="form.errors.bku_id" class="text-sm text-red-600">
                            {{ form.errors.bku_id }}
                        </p>
                    </div>

                    <!-- Password -->
                    <div class="space-y-2">
                        <Label for="password" class="text-sm font-medium text-gray-700">
                            Password Baru
                        </Label>

                        <Input id="password" v-model="form.password" type="password"
                            placeholder="Kosongkan jika tidak ingin mengubah"
                            class="h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" />

                        <p v-if="form.errors.password" class="text-sm text-red-600">
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <!-- Konfirmasi Password -->
                    <div class="space-y-2">
                        <Label for="password_confirmation" class="text-sm font-medium text-gray-700">
                            Konfirmasi Password Baru
                        </Label>

                        <Input id="password_confirmation" v-model="form.password_confirmation" type="password"
                            placeholder="Ulangi password baru"
                            class="h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" />
                    </div>

                    <!-- Button -->
                    <div class="flex items-center justify-end gap-3 border-t pt-6">
                        <Link :href="route('users.index')"
                            class="rounded-lg border px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Batal
                        </Link>

                        <Button type="submit" :disabled="form.processing"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50">
                            {{
                                form.processing
                                    ? 'Menyimpan...'
                                    : 'Simpan Perubahan'
                            }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>