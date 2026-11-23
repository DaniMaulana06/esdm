<script setup lang="ts">
import { MapPin, Building2, CalendarDays } from 'lucide-vue-next';
import { Barrel } from '@lucide/vue';

defineProps<{
    selectedKabupaten: string | null;
    totalBku: number;
    totalProduksi: number;
    labelPeriode: string;
}>();
</script>

<template>
    <section class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div
            class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between"><span
                    class="text-xs font-medium uppercase tracking-wider text-slate-500">Wilayah Terpilih</span>
                <MapPin class="h-5 w-5 text-amber-500" />
            </div>
            <div class="mt-2 text-xl font-bold text-slate-800 dark:text-white">
                {{ selectedKabupaten || 'Seluruh Sumatera Selatan' }}</div>
        </div>
        <div
            class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between"><span
                    class="text-xs font-medium uppercase tracking-wider text-slate-500">Jumlah BKU</span>
                <Building2 class="h-5 w-5 text-blue-500" />
            </div>
            <div class="mt-2 text-2xl font-bold text-slate-800 dark:text-white">{{ totalBku }} <span
                    class="text-sm font-normal text-slate-500">Unit Usaha</span></div>
        </div>
        <div
            class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between"><span
                    class="text-xs font-medium uppercase tracking-wider text-slate-500">Total Produksi</span>
                <Barrel class="h-5 w-5 text-amber-900" />
            </div>
            <div class="mt-2 text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{
                totalProduksi.toLocaleString('id-ID') }} <span class="text-sm font-normal text-slate-500">Barel</span>
            </div>
            <p class="mt-1 flex items-center gap-1 text-xs text-slate-500">
                <CalendarDays class="mr-0.5 inline h-3 w-3" />{{ labelPeriode }}
            </p>
        </div>
    </section>
</template>
