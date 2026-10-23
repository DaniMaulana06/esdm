<script setup lang="ts">
import LoginForm from '@/components/LoginForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

// const form = useForm({
//     email: '',
//     password: '',
//     remember: false,
// });

// const submit = () => {
//     form.post(route('login'), {
//         onFinish: () => form.reset('password'),
//     });
// };
</script>

<template>

    <Head title="Login" />

    <div class="grid min-h-svh lg:grid-cols-2">
        <!-- Sisi formulir -->
        <div class="flex flex-col p-6 md:p-10">
            <div class="flex justify-center gap-2 md:justify-start">
                <a href="/login" class="flex items-center gap-2 font-medium">
                    <img src="images/logo_esdm.png" height="120" width="120" />
                    Pelaporan Produksi & Lifting harian SUMAS
                </a>
            </div>

            <div class="flex flex-1 items-center justify-center">
                <div class="w-full max-w-sm">
                    <LoginForm :can-reset-password="canResetPassword" :status="status" />
                </div>
            </div>
        </div>

        <!-- Sisi gambar -->
        <div class="relative hidden bg-muted lg:block">
            <img src="/images/login_esdm.jpg" alt="Ilustrasi kegiatan energi dan pertambangan"
                class="absolute inset-0 size-full object-cover" />
            <div class="absolute inset-0 bg-black/40" />
            <div class="absolute bottom-10 left-10 right-10 text-white">
                <h2 class="text-3xl font-bold">
                    Sistem Pelaporan Produksi Minyak Bumi dari Sumur Minyak Rakyat Provinsi Sumatera Selatan
                </h2>
                <p class="mt-3 text-sm text-white/90">
                    <a href="https://desdm.sumselprov.go.id/">  
                        Dinas Energi dan Sumber Daya Mineral
                        Provinsi Sumatera Selatan
                    </a>
                </p>
            </div>
        </div>
    </div>
</template>
