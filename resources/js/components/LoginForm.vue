```vue
<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import { cn } from '@/lib/utils';

import Field from './ui/field/Field.vue';
import Card from './ui/card/Card.vue';
import CardContent from './ui/card/CardContent.vue';
import FieldGroup from './ui/field/FieldGroup.vue';
import FieldLabel from './ui/field/FieldLabel.vue';
import Input from './ui/input/Input.vue';
import InputError from './InputError.vue';
import Button from './ui/button/Button.vue';
import FieldDescription from './ui/field/FieldDescription.vue';

const props = defineProps<{
    class?: HTMLAttributes['class'];
    canResetPassword?: boolean;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <div :class="cn('w-full max-w-5xl flex flex-col gap-6', props.class)">
        <Card class="w-full overflow-hidden p-0">
            <CardContent class="grid p-0 md:grid-cols-2">
                <form class="p-6 md:p-8 border-yellow-400" @submit.prevent="submit">
                    <FieldGroup>
                        <div class="flex flex-col items-center gap-2 text-center">
                            <h1 class="text-2xl font-bold">
                                Selamat Datang
                            </h1>

                            <p class="text-muted-foreground text-balance">
                                Masuk ke akun pelaporan produksi minyak ESDM
                            </p>
                        </div>

                        <Field>
                            <FieldLabel for="email">
                                Alamat Email
                            </FieldLabel>

                            <Input id="email" v-model="form.email" type="email" placeholder="nama@email.com"
                                autocomplete="username" autofocus required :disabled="form.processing" />

                            <InputError :message="form.errors.email" />
                        </Field>

                        <Field>
                            <div class="flex items-center justify-between">
                                <FieldLabel for="password">
                                    Password
                                </FieldLabel>

                                <Link v-if="canResetPassword" :href="route('password.request')"
                                    class="text-sm underline-offset-2 hover:underline">
                                    Lupa password?
                                </Link>
                            </div>

                            <Input id="password" v-model="form.password" type="password" placeholder="Masukkan password"
                                autocomplete="current-password" required :disabled="form.processing" />

                            <InputError :message="form.errors.password" />
                        </Field>

                        <Field>
                            <label for="remember" class="flex items-center gap-2 text-sm">
                                <input id="remember" v-model="form.remember" type="checkbox"
                                    class="size-4 rounded border-input" :disabled="form.processing" />

                                Ingat saya
                            </label>
                        </Field>

                        <Field>
                            <Button type="submit" class="w-full bg-yellow-500 text-black hover:bg-yellow-600"
                                :disabled="form.processing">
                                <span v-if="form.processing">
                                    Memproses...
                                </span>

                                <span v-else>
                                    Masuk
                                </span>
                            </Button>
                        </Field>
                    </FieldGroup>
                </form>

                <div class="relative hidden min-h-[480px] md:block">
                    <img src="/images/logo_esdm.png" alt="Logo ESDM"
                        class="absolute inset-0 h-full w-full object-contain p-8" />
                </div>
            </CardContent>
        </Card>

        <FieldDescription class="px-6 text-center">
            Sistem Pelaporan Produksi Harian Minyak Bumi
            <br />
            Dinas ESDM Provinsi Sumatera Selatan
        </FieldDescription>
    </div>
</template>
```