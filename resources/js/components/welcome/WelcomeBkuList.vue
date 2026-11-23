<script setup lang="ts">
import { Building2, MapPin } from 'lucide-vue-next';
interface BkuItem {
    id: number; nama: string; kota: string | null; kategori: string | null;
    penetapan: number; total_produksi: number | string; total_lifting: number | string;
}
interface CategoryMeta { badge: string; }
defineProps<{ bkuList: BkuItem[]; categoryMeta: Record<string, CategoryMeta> }>();
</script>

<template>
    <section
        class="flex flex-col rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div
            class="flex items-center justify-between border-b border-slate-100 bg-slate-50/70 px-5 py-3 dark:border-slate-800 dark:bg-slate-900/50">
            <h3 class="flex items-center gap-2 text-sm font-semibold text-slate-800 dark:text-slate-200">
                <Building2 class="h-4 w-4 text-amber-500" />Daftar Unit BKU
            </h3>
            <span class="text-xs font-medium text-slate-500">{{ bkuList.length }} BKU</span>
        </div>
        <div class="max-h-[400px] overflow-y-auto p-3">
            <ul v-if="bkuList.length > 0" class="space-y-2">
                <li v-for="bku in bkuList" :key="bku.id"
                    class="group rounded-xl border border-slate-100 bg-slate-50/40 p-3 transition-all hover:border-amber-200 hover:bg-amber-50/60 hover:shadow-sm dark:border-slate-800/80 dark:bg-slate-900/60 dark:hover:border-amber-900/40 dark:hover:bg-slate-800/60">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <h4
                                    class="truncate text-sm font-semibold text-slate-800 transition-colors group-hover:text-amber-600 dark:text-slate-200 dark:group-hover:text-amber-400">
                                    {{ bku.nama }}</h4>
                                <span
                                    class="inline-flex flex-shrink-0 items-center rounded-md px-1.5 py-0.5 text-[10px] font-medium ring-1 ring-inset"
                                    :class="categoryMeta[bku.kategori || 'Lainnya']?.badge || categoryMeta.Lainnya?.badge">{{
                                    bku.kategori || 'Umum' }}</span>
                            </div>
                            <div class="mt-0.5 flex items-center gap-1 text-xs text-slate-500 dark:text-slate-400">
                                <MapPin class="h-3 w-3 flex-shrink-0" /><span class="truncate">
                                    {{ bku.kota || 'Tidak ada data kota' }}</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0 text-right"><span
                                class="inline-flex items-center rounded-lg bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-600/20 dark:bg-emerald-950/40 dark:text-emerald-400">{{
                                    Number(bku.total_produksi).toLocaleString('id-ID') }} Barel</span></div>
                    </div>
                </li>
            </ul>
            <div v-else class="flex flex-col items-center justify-center py-8 text-center">
                <div class="rounded-full bg-slate-100 p-3 dark:bg-slate-800">
                    <Building2 class="h-6 w-6 text-slate-400" />
                </div>
                <h4 class="mt-3 font-medium text-slate-800 dark:text-slate-200">Tidak ada data BKU</h4>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Belum ada data BKU untuk filter yang dipilih.
                </p>
            </div>
        </div>
    </section>
</template>
