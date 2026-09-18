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
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { route } from 'ziggy-js';

interface Bku {
    id: number;
    nama: string;
}

interface Props {
    bkus: Bku[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'User Management',
        href: route('users.index'),
    },
    {
        title: 'Tambah User',
        href: route('users.create'),
    },
];

const form = useForm({
    name: '',
    email: '',
    role: '',
    bku_id: '',
    password: '',
    password_confirmation: '',
});

const isOperatorBku = computed(() => {
    return form.role === 'operator_bku';
});

const submit = () => {
    form.post(route('users.store'));
};
</script>

<template>

    <Head title="Tambah User" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-2xl p-6">
            <Card>
                <CardHeader>

                    <CardTitle>
                        Tambah User
                    </CardTitle>

                    <CardDescription>
                        Tambahkan akun pengguna baru ke dalam sistem.
                    </CardDescription>
                </CardHeader>

                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">

                        <!-- Nama -->
                        <div class="space-y-2">
                            <Label for="name" class="text-sm font-medium text-gray-700">
                                Nama
                            </Label>

                            <Input id="name" v-model="form.name" type="text" placeholder="Masukkan nama"
                                class="h-10 w-full rounded-md border border-input bg-background px-3 py-1 text-sm" />

                            <p v-if="form.errors.name" class="text-sm text-red-600">
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <!-- Email -->
                        <div class="space-y-2">
                            <Label for="email" class="text-sm font-medium text-gray-700">
                                Email
                            </Label>

                            <Input id="email" v-model="form.email" type="email" placeholder="contoh@email.com"
                                class="h-10 w-full rounded-md border border-input bg-background px-3 py-1 text-sm" />

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
                                class="h-10 w-full rounded-md border border-input bg-background px-3 py-1 text-sm">
                                <option value="" disabled>
                                    Pilih Role
                                </option>

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
                                class="h-10 w-full rounded-md border border-input bg-background px-3 py-1 text-sm">
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
                                Password
                            </Label>

                            <input id="password" v-model="form.password" type="password"
                                placeholder="Minimal 8 karakter"
                                class="h-10 w-full rounded-md border border-input bg-background px-3 py-1 text-sm" />

                            <p v-if="form.errors.password" class="text-sm text-red-600">
                                {{ form.errors.password }}
                            </p>
                        </div>

                        <!-- Konfirmasi Password -->
                        <div class="space-y-2">
                            <Label for="password_confirmation" class="text-sm font-medium text-gray-700">
                                Konfirmasi Password
                            </Label>

                            <input id="password_confirmation" v-model="form.password_confirmation" type="password"
                                placeholder="Ulangi password"
                                class="h-10 w-full rounded-md border border-input bg-background px-3 py-1 text-sm" />
                        </div>

                        <!-- Button -->
                        <div class="flex items-center justify-end gap-3 border-t pt-6">
                            <Link :href="route('users.index')"
                                class="rounded-lg border px-4 py-1 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                Batal
                            </Link>

                            <Button type="submit" :disabled="form.processing"
                                class="rounded-lg bg-blue-600 px-4 py-1 text-sm font-medium text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50">
                                {{
                                    form.processing
                                        ? 'Menyimpan...'
                                        : 'Simpan User'
                                }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>