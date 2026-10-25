```vue
<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { cn } from '@/lib/utils';

import { Button } from '@/components/ui/button';
import { Field, FieldGroup, FieldLabel } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import InputError from './InputError.vue';
import Checkbox from './ui/checkbox/Checkbox.vue';
import Label from '@/Components/ui/label/Label.vue';
import TextLink from './TextLink.vue';
import FieldSeparator from './ui/field/FieldSeparator.vue';

defineProps<{
    status?: string;
    canResetPassword: boolean;
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
    <form :class="cn('flex flex-col gap-6')" @submit.prevent="submit">
        <FieldGroup>
            <!-- Judul -->
            <div class="flex flex-col items-center gap-1 text-center">
                <h1 class="text-2xl font-bold">
                    Masuk ke akun Anda
                </h1>

                <p class="text-muted-foreground text-sm text-balance">
                    Masukkan email Anda di bawah ini
                </p>
            </div>

            <!-- Email -->
            <Field>
                <FieldLabel for="email">
                    Alamat Email
                </FieldLabel>

                <Input id="email" v-model="form.email" type="email" placeholder="jhon@gmail.com" autocomplete="email"
                    required autofocus :tabindex="1" />

                <InputError :message="form.errors.email" />
            </Field>

            <!-- Password -->
            <Field>
                <div class="flex items-center justify-between gap-2">
                    <FieldLabel for="password">
                        Password
                    </FieldLabel>
                </div>

                <Input id="password" v-model="form.password" type="password" placeholder="Password"
                    autocomplete="current-password" required :tabindex="2" />

                <InputError :message="form.errors.password" />
            </Field>

            <!-- Remember me -->
            <div class="flex items-center">
                <Label for="remember" class="flex cursor-pointer items-center gap-2">
                    <Checkbox id="remember" v-model:checked="form.remember" :tabindex="3" />

                    <span>Remember me</span>
                </Label>
            </div>

            <!-- Tombol Login -->
            <Field>
                <Button type="submit" class="w-full bg-yellow-500 text-black hover:bg-yellow-600" :tabindex="4"
                    :disabled="form.processing">
                    {{ form.processing ? 'Logging in...' : 'Login' }}
                </Button>
            </Field>
            <FieldSeparator>
                <TextLink v-if="canResetPassword" :href="route('password.request')" class="text-sm" :tabindex="5">
                    Lupa password?
                </TextLink>
            </FieldSeparator>
        </FieldGroup>
    </form>
</template>
```