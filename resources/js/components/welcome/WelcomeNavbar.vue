<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Layers } from 'lucide-vue-next';

withDefaults(defineProps<{ authenticated?: boolean }>(), { authenticated: false });
</script>

<template>
    <nav
        class="sticky top-0 z-[1000] border-b border-slate-200/80 bg-white/90 px-6 py-3.5 shadow-sm backdrop-blur-lg dark:border-slate-800 dark:bg-slate-950/90">
        <div class="mx-auto flex max-w-8xl items-center justify-between">
            <div class="flex items-center gap-3">
                <img src="/images/logo_esdm.png" alt="Logo ESDM" class="h-9 w-auto object-contain" />
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-slate-800 dark:text-white">Dinas ESDM <span
                            class="text-amber-500">Sumsel</span></h1>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Portal Sumur Minyak Rakyat Provinsi Sumsel
                    </p>
                </div>
            </div>
            <Link v-if="authenticated" :href="route('dashboard')"
                class="inline-flex items-center gap-2 rounded-full bg-amber-500 px-5 py-2 text-sm font-semibold text-white shadow-sm transition-all hover:bg-amber-600">
                <Layers class="h-4 w-4" /> Dashboard
            </Link>
            <Link v-else :href="route('login')"
                class="rounded-full bg-amber-500 px-5 py-2 text-sm font-semibold text-white shadow-sm transition-all hover:bg-amber-600">
                Log in
            </Link>
        </div>
    </nav>
</template>
