
<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage<{
    status?: string | null
}>()

const status = computed(() => page.props.status)

const emailForm = useForm({
    email: '',
})

const otpForm = useForm({
    otp: '',
})

function sendOtp() {
    emailForm.post(route('email-otp.send'), {
        preserveScroll: true,
    })
}

function verifyOtp() {
    otpForm.post(route('email-otp.verify'), {
        preserveScroll: true,
    })
}
</script>

<template>
    <Head title="Verifikasi Email" />

    <div class="flex min-h-screen items-center justify-center bg-gray-50 px-4 py-10">
        <div class="w-full max-w-md space-y-6 rounded-xl border bg-white p-6 shadow-sm">
            <div class="space-y-2 text-center">
                <h1 class="text-2xl font-bold text-gray-900">
                    Verifikasi Email
                </h1>

                <p class="text-sm text-gray-600">
                    Masukkan email aktif untuk menerima kode OTP verifikasi akun SUMAS.
                </p>
            </div>

            <p
                v-if="status"
                class="rounded-md bg-green-50 p-3 text-sm text-green-700"
            >
                {{ status }}
            </p>

            <form @submit.prevent="sendOtp" class="space-y-3">
                <label for="email" class="block text-sm font-medium text-gray-700">
                    Email aktif
                </label>

                <input
                    id="email"
                    v-model="emailForm.email"
                    type="email"
                    required
                    autocomplete="email"
                    placeholder="nama@email.com"
                    class="w-full rounded-md border px-3 py-2 outline-none focus:ring-2 focus:ring-yellow-400"
                />

                <p v-if="emailForm.errors.email" class="text-sm text-red-600">
                    {{ emailForm.errors.email }}
                </p>

                <button
                    type="submit"
                    :disabled="emailForm.processing"
                    class="w-full rounded-md bg-yellow-400 px-4 py-2 font-semibold text-gray-900 hover:bg-yellow-500 disabled:opacity-60"
                >
                    {{ emailForm.processing ? 'Mengirim...' : 'Kirim Kode OTP' }}
                </button>
            </form>

            <div class="border-t pt-5">
                <form @submit.prevent="verifyOtp" class="space-y-3">
                    <label for="otp" class="block text-sm font-medium text-gray-700">
                        Kode OTP
                    </label>

                    <input
                        id="otp"
                        v-model="otpForm.otp"
                        type="text"
                        inputmode="numeric"
                        autocomplete="one-time-code"
                        maxlength="6"
                        pattern="[0-9]{6}"
                        required
                        placeholder="Masukkan 6 digit kode"
                        class="w-full rounded-md border px-3 py-2 tracking-widest outline-none focus:ring-2 focus:ring-yellow-400"
                    />

                    <p v-if="otpForm.errors.otp" class="text-sm text-red-600">
                        {{ otpForm.errors.otp }}
                    </p>

                    <button
                        type="submit"
                        :disabled="otpForm.processing"
                        class="w-full rounded-md bg-gray-900 px-4 py-2 font-semibold text-white hover:bg-gray-800 disabled:opacity-60"
                    >
                        {{ otpForm.processing ? 'Memverifikasi...' : 'Verifikasi Email' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>
