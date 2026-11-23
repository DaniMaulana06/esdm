<script setup lang="ts">
import { Filter, RotateCcw, Search } from 'lucide-vue-next';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';

interface Props {
    selectedBulan: string;
    selectedTahun: string;
    selectedKabupaten: string | null;
    searchQuery: string;
    daftarBulan: { value: string; label: string }[];
    tahunTersedia: number[];
    daftarKabupaten: string[];
    labelPeriode: string;
}

defineProps<Props>();
const emit = defineEmits<{
    (e: 'update:selectedBulan', value: string): void;
    (e: 'update:selectedTahun', value: string): void;
    (e: 'update:selectedKabupaten', value: string | null): void;
    (e: 'update:searchQuery', value: string): void;
    (e: 'reset'): void;
}>();
</script>

<template>
    <section
        class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="mb-3 flex items-center gap-2">
            <Filter class="h-4 w-4 text-slate-500" />
            <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">Filter Data</span>
            <span
                class="ml-auto rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-700 dark:bg-amber-950/60 dark:text-amber-300">{{
                labelPeriode }}</span>
        </div>
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5">
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">Bulan</label>
                <Select :model-value="selectedBulan" @update:model-value="emit('update:selectedBulan', String($event))">
                    <SelectTrigger class="w-full">
                        <SelectValue placeholder="Pilih Bulan" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectGroup>
                            <SelectItem v-for="b in daftarBulan" :key="b.value" :value="b.value">{{ b.label }}
                            </SelectItem>
                        </SelectGroup>
                    </SelectContent>
                </Select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">Tahun</label>
                <Select :model-value="selectedTahun" @update:model-value="emit('update:selectedTahun', String($event))">
                    <SelectTrigger class="w-full">
                        <SelectValue placeholder="Pilih Tahun" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectGroup>
                            <SelectItem value="all">Semua Tahun</SelectItem>
                            <SelectItem v-for="t in tahunTersedia" :key="t" :value="String(t)">{{ t }}</SelectItem>
                        </SelectGroup>
                    </SelectContent>
                </Select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">Kabupaten/Kota</label>
                <Select :model-value="selectedKabupaten ?? 'all'"
                    @update:model-value="emit('update:selectedKabupaten', $event === 'all' ? null : String($event))">
                    <SelectTrigger class="w-full">
                        <SelectValue placeholder="Semua Wilayah" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectGroup>
                            <SelectItem value="all">Semua Wilayah</SelectItem>
                            <SelectItem v-for="kab in daftarKabupaten" :key="kab" :value="kab">{{ kab }}</SelectItem>
                        </SelectGroup>
                    </SelectContent>
                </Select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">Cari BKU</label>
                <div class="relative">
                    <Search
                        class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                    <Input :model-value="searchQuery" @update:model-value="emit('update:searchQuery', String($event))"
                        type="text" placeholder="Ketik nama BKU..." class="pl-9" />
                </div>
            </div>
            <div class="flex items-end">
                <Button @click="emit('reset')"
                    class="inline-flex w-full items-center justify-center gap-1.5 rounded-md border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700">
                    <RotateCcw class="h-3.5 w-3.5" /> Reset
                </Button>
            </div>
        </div>
    </section>
</template>
