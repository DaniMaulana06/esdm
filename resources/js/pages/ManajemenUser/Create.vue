<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import CardDescription from '@/components/ui/card/CardDescription.vue';
import CardHeader from '@/components/ui/card/CardHeader.vue';
import CardTitle from '@/components/ui/card/CardTitle.vue';
import Input from '@/components/ui/input/Input.vue';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
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
        <div class="p-6">
            <!-- Card Form - Center -->
            <div class="flex justify-center">
                <Card class="w-full max-w-2xl border border-gray-200 shadow-sm">

                    <CardHeader>
                        <CardTitle class="text-lg font-semibold">
                            Informasi Pengguna
                        </CardTitle>

                        <CardDescription>
                            Lengkapi informasi untuk membuat akun pengguna baru.
                        </CardDescription>
                    </CardHeader>

                    <CardContent>
                        <form @submit.prevent="submit" class="space-y-3">

                            <!-- Nama -->
                            <div class="space-y-2">
                                <Label for="name" class="text-sm font-medium">
                                    Nama
                                </Label>

                                <Input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    placeholder="Masukkan nama"
                                    class="h-10 w-full"
                                />

                                <p v-if="form.errors.name" class="text-sm text-red-600">
                                    {{ form.errors.name }}
                                </p>
                            </div>

                            <!-- Email -->
                            <div class="space-y-2">
                                <Label for="email" class="text-sm font-medium">
                                    Email
                                </Label>

                                <Input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    placeholder="contoh@email.com"
                                    class="h-10 w-full"
                                />

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

                                    <SelectContent class="w-[--reka-select-trigger-width]">
                                        <SelectGroup>
                                            <SelectItem value="admin">
                                                Admin
                                            </SelectItem>

                                            <SelectItem value="staf_dinas">
                                                Staf Dinas
                                            </SelectItem>

                                            <SelectItem value="operator_bku">
                                                Operator BKU
                                            </SelectItem>
                                        </SelectGroup>
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

                                    <SelectContent class="w-[--reka-select-trigger-width]">
                                        <SelectGroup>
                                            <SelectItem
                                                v-for="bku in props.bkus"
                                                :key="bku.id"
                                                :value="String(bku.id)"
                                            >
                                                {{ bku.nama }}
                                            </SelectItem>
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>

                                <p v-if="form.errors.bku_id" class="text-sm text-red-600">
                                    {{ form.errors.bku_id }}
                                </p>
                            </div>

                            <!-- Password -->
                            <div class="space-y-2">
                                <Label for="password" class="text-sm font-medium">
                                    Password
                                </Label>

                                <Input
                                    id="password"
                                    v-model="form.password"
                                    type="password"
                                    placeholder="Minimal 8 karakter"
                                    class="h-10 w-full"
                                />

                                <p v-if="form.errors.password" class="text-sm text-red-600">
                                    {{ form.errors.password }}
                                </p>
                            </div>

                            <!-- Konfirmasi Password -->
                            <div class="space-y-2">
                                <Label for="password_confirmation" class="text-sm font-medium">
                                    Konfirmasi Password
                                </Label>

                                <Input
                                    id="password_confirmation"
                                    v-model="form.password_confirmation"
                                    type="password"
                                    placeholder="Ulangi password"
                                    class="h-10 w-full"
                                />
                            </div>

                            <!-- Button -->
                            <div class="flex items-center justify-end gap-3 border-t pt-6">

                                <Link
                                    :href="route('users.index')"
                                    class="rounded-lg border px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                                >
                                    Batal
                                </Link>

                                <Button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="rounded-lg bg-yellow-500 px-4 py-2 text-sm font-medium text-gray-900 transition hover:bg-yellow-600 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <Save
                                        v-if="!form.processing"
                                        :size="17"
                                        :stroke-width="2.5"
                                    />

                                    {{
                                        form.processing
                                            ? 'Menyimpan...'
                                            : 'Simpan'
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
