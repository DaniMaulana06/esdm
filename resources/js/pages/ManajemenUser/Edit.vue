<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import { Card, CardContent, CardDescription, CardHeader } from '@/components/ui/card';
import CardTitle from '@/components/ui/card/CardTitle.vue';
import Input from '@/components/ui/input/Input.vue';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Save } from 'lucide-vue-next';
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

            <!-- Card Form - Center -->
            <div class="flex justify-center">
                <Card class="w-full max-w-2xl border border-gray-200 shadow-sm">

                    <!-- Card Header -->
                    <CardHeader>
                        <CardTitle class="text-lg font-semibold">
                            Informasi Pengguna
                        </CardTitle>

                        <CardDescription>
                            Perbarui informasi akun pengguna di bawah ini.
                        </CardDescription>
                    </CardHeader>

                    <!-- Card Content -->
                    <CardContent>
                        <form @submit.prevent="submit" class="space-y-3">

                            <!-- Nama -->
                            <div class="space-y-2">
                                <Label for="name" class="text-sm font-medium">
                                    Nama
                                </Label>

                                <Input id="name" v-model="form.name" type="text" class="h-10 w-full" />

                                <p v-if="form.errors.name" class="text-sm text-red-600">
                                    {{ form.errors.name }}
                                </p>
                            </div>

                            <!-- Email -->
                            <div class="space-y-2">
                                <Label for="email" class="text-sm font-medium">
                                    Email
                                </Label>

                                <Input id="email" v-model="form.email" type="email" class="h-10 w-full" />

                                <p v-if="form.errors.email" class="text-sm text-red-600">
                                    {{ form.errors.email }}
                                </p>
                            </div>

                            <!-- Role -->
                            <div class="space-y-2">
                                <Label for="role" class="text-sm font-medium">
                                    Role
                                </Label>

                                <Select v-model="form.role">
                                    <SelectTrigger id="role" class="h-10 w-full">
                                        <SelectValue placeholder="Pilih Role" />
                                    </SelectTrigger>

                                    <SelectContent>
                                        <SelectItem value="admin">
                                            Admin
                                        </SelectItem>

                                        <SelectItem value="staf_dinas">
                                            Staf Dinas
                                        </SelectItem>

                                        <SelectItem value="operator_bku">
                                            Operator BKU
                                        </SelectItem>
                                    </SelectContent>
                                </Select>

                                <p v-if="form.errors.role" class="text-sm text-red-600">
                                    {{ form.errors.role }}
                                </p>
                            </div>

                            <!-- BKU -->
                            <div v-if="isOperatorBku" class="space-y-2">
                                <Label for="bku_id" class="text-sm font-medium">
                                    BKU
                                </Label>

                                <Select v-model="form.bku_id">
                                    <SelectTrigger id="bku_id" class="h-10 w-full">
                                        <SelectValue placeholder="Pilih BKU" />
                                    </SelectTrigger>

                                    <SelectContent>
                                        <SelectItem v-for="bku in props.bkus" :key="bku.id" :value="String(bku.id)">
                                            {{ bku.nama }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>

                                <p v-if="form.errors.bku_id" class="text-sm text-red-600">
                                    {{ form.errors.bku_id }}
                                </p>
                            </div>

                            <!-- Password -->
                            <div class="space-y-2">
                                <Label for="password" class="text-sm font-medium">
                                    Password Baru
                                </Label>

                                <Input id="password" v-model="form.password" type="password"
                                    placeholder="Kosongkan jika tidak ingin mengubah" class="h-10 w-full" />

                                <p v-if="form.errors.password" class="text-sm text-red-600">
                                    {{ form.errors.password }}
                                </p>
                            </div>

                            <!-- Konfirmasi Password -->
                            <div class="space-y-2">
                                <Label for="password_confirmation" class="text-sm font-medium">
                                    Konfirmasi Password Baru
                                </Label>

                                <Input id="password_confirmation" v-model="form.password_confirmation" type="password"
                                    placeholder="Ulangi password baru" class="h-10 w-full" />
                            </div>

                            <!-- Button -->
                            <div class="flex items-center justify-end gap-3 border-t pt-6">
                                <Link :href="route('users.index')"
                                    class="rounded-lg border px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                                    Batal
                                </Link>

                                <Button type="submit" :disabled="form.processing"
                                    class="rounded-lg bg-yellow-500 px-4 py-2 text-sm font-medium text-gray-900 transition hover:bg-yellow-600 disabled:cursor-not-allowed disabled:opacity-50">
                                    <Save
                                        v-if="!form.processing"
                                        :size="17"
                                        :stroke-width="2.5"
                                    />
                                    {{
                                        form.processing
                                            ? 'Menyimpan...'
                                            : 'Simpan Perubahan'
                                    }}
                                </Button>
                            </div>

                        </form>
                    </CardContent>
                </Card>
            </div>

        </div>
    </AppLayout>
</template>
