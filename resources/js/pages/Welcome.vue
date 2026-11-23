<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import WelcomeNavbar from '@/components/welcome/WelcomeNavbar.vue';
import WelcomeHero from '@/components/welcome/WelcomeHero.vue';
import WelcomeFilterBar from '@/components/welcome/WelcomeFilterBar.vue';
import WelcomeKpiCards from '@/components/welcome/WelcomeKpiCards.vue';
import WelcomeMap from '@/components/welcome/WelcomeMap.vue';
import WelcomeCategoryChart from '@/components/welcome/WelcomeCategoryChart.vue';
import WelcomeBkuList from '@/components/welcome/WelcomeBkuList.vue';

interface BkuItem {
    id: number;
    nama: string;
    kota: string | null;
    kategori: string | null;
    penetapan: number;
    total_produksi: number | string;
    total_lifting: number | string;
}
interface Filters { bulan: string; tahun: string; }
interface CategorySegment {
    name: string; count: number; percentage: number; color: string;
    bgClass: string; textClass: string; badge: string;
    strokeDash: string; strokeOffset: number;
}

const props = withDefaults(defineProps<{
    bkuList?: BkuItem[];
    filters?: Filters;
    tahunTersedia?: number[];
}>(), {
    bkuList: () => [],
    filters: () => ({ bulan: String(new Date().getMonth() + 1), tahun: String(new Date().getFullYear()) }),
    tahunTersedia: () => [new Date().getFullYear()],
});
const page = usePage<any>();

const NAMA_BULAN: Record<string, string> = {
    '1': 'Januari', '2': 'Februari', '3': 'Maret', '4': 'April', '5': 'Mei', '6': 'Juni',
    '7': 'Juli', '8': 'Agustus', '9': 'September', '10': 'Oktober', '11': 'November', '12': 'Desember',
};
const DAFTAR_BULAN = [
    { value: 'all', label: 'Semua Bulan' },
    ...Object.entries(NAMA_BULAN).map(([value, label]) => ({ value, label })),
];
const CATEGORY_COLORS: Record<string, { hex: string; bg: string; text: string; badge: string }> = {
    Perusahaan: { hex: '#3B82F6', bg: 'bg-blue-500', text: 'text-blue-600 dark:text-blue-400', badge: 'bg-blue-50 text-blue-700 ring-blue-700/10 dark:bg-blue-900/20 dark:text-blue-300' },
    Koperasi: { hex: '#10B981', bg: 'bg-emerald-500', text: 'text-emerald-600 dark:text-emerald-400', badge: 'bg-emerald-50 text-emerald-700 ring-emerald-700/10 dark:bg-emerald-900/20 dark:text-emerald-300' },
    UMKM: { hex: '#F59E0B', bg: 'bg-amber-500', text: 'text-amber-600 dark:text-amber-400', badge: 'bg-amber-50 text-amber-700 ring-amber-700/10 dark:bg-amber-900/20 dark:text-amber-300' },
    Lainnya: { hex: '#8B5CF6', bg: 'bg-purple-500', text: 'text-purple-600 dark:text-purple-400', badge: 'bg-purple-50 text-purple-700 ring-purple-700/10 dark:bg-purple-900/20 dark:text-purple-300' },
};
const getCategoryMeta = (category?: string | null) => !category ? CATEGORY_COLORS.Lainnya : (CATEGORY_COLORS[category] || CATEGORY_COLORS.Lainnya);

const selectedBulan = ref<string>(props.filters.bulan ?? 'all');
const selectedTahun = ref<string>(props.filters.tahun ?? String(new Date().getFullYear()));
const selectedKabupaten = ref<string | null>(null);
const searchQuery = ref('');
const hoveredCategory = ref<string | null>(null);
const mapRef = ref<InstanceType<typeof WelcomeMap> | null>(null);

const labelPeriode = computed(() => {
    const bulan = selectedBulan.value;
    const tahun = selectedTahun.value;
    if (bulan === 'all' && tahun === 'all') return 'Seluruh Periode';
    if (bulan === 'all') return `Tahun ${tahun}`;
    if (tahun === 'all') return `Bulan ${NAMA_BULAN[bulan] ?? bulan}`;
    return `${NAMA_BULAN[bulan] ?? bulan} ${tahun}`;
});
const daftarKabupaten = computed(() => {
    const cities = new Set<string>();
    props.bkuList.forEach((bku) => { if (bku.kota?.trim()) cities.add(bku.kota.trim()); });
    return Array.from(cities).sort();
});
const cleanCityName = (name?: string | null): string => {
    if (!name) return '';
    return name.trim().toLowerCase().replace(/^(kabupaten|kab\.|kota)\s+/i, '').trim();
};
const isSameCity = (cityA?: string | null, cityB?: string | null): boolean => {
    if (!cityA || !cityB) return false;
    const a = cleanCityName(cityA);
    const b = cleanCityName(cityB);
    return !!a && !!b && (a === b || a.includes(b) || b.includes(a));
};
const filteredBku = computed(() => {
    let list = props.bkuList;
    if (selectedKabupaten.value) list = list.filter((bku) => isSameCity(bku.kota, selectedKabupaten.value));
    if (searchQuery.value.trim()) {
        const query = searchQuery.value.trim().toLowerCase();
        list = list.filter((bku) => bku.nama.toLowerCase().includes(query));
    }
    return list;
});
const totalProduksiFiltered = computed(() => filteredBku.value.reduce((sum, item) => sum + (Number(item.total_produksi) || 0), 0));
const categoryStats = computed<CategorySegment[]>(() => {
    const list = filteredBku.value;
    const total = list.length;
    if (!total) return [];
    const counts: Record<string, number> = {};
    list.forEach((bku) => {
        const category = bku.kategori?.trim() || 'Lainnya';
        counts[category] = (counts[category] || 0) + 1;
    });
    let accumulatedOffset = 0;
    const segments: CategorySegment[] = [];
    Object.entries(counts).sort((a, b) => b[1] - a[1]).forEach(([name, count]) => {
        const percentage = Math.round((count / total) * 100);
        const meta = getCategoryMeta(name);
        segments.push({ name, count, percentage, color: meta.hex, bgClass: meta.bg, textClass: meta.text, badge: meta.badge, strokeDash: `${percentage} ${100 - percentage}`, strokeOffset: -accumulatedOffset });
        accumulatedOffset += percentage;
    });
    return segments;
});
const activeHoveredData = computed(() => hoveredCategory.value ? categoryStats.value.find((item) => item.name === hoveredCategory.value) || null : null);

const applyPeriodFilter = () => {
    router.get(route('home'), { bulan: selectedBulan.value, tahun: selectedTahun.value }, { preserveState: true, preserveScroll: true, replace: true });
};
const resetAllFilters = () => {
    selectedKabupaten.value = null;
    searchQuery.value = '';
    selectedBulan.value = String(new Date().getMonth() + 1);
    selectedTahun.value = String(new Date().getFullYear());
    mapRef.value?.flyToDefault();
    applyPeriodFilter();
};
const resetMapFilter = () => {
    selectedKabupaten.value = null;
    mapRef.value?.flyToDefault();
};
watch(selectedBulan, () => applyPeriodFilter());
watch(selectedTahun, () => applyPeriodFilter());
</script>

<template>

    <Head title="Peta BKU Sumatera Selatan" />
    <div
        class="min-h-screen bg-slate-50 font-sans text-slate-900 selection:bg-amber-500 selection:text-white dark:bg-slate-950 dark:text-slate-100">
        <WelcomeNavbar :authenticated="!!page.props.auth?.user" />
        <main class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
            <WelcomeHero />
            <WelcomeKpiCards :selected-kabupaten="selectedKabupaten" :total-bku="filteredBku.length"
                :total-produksi="totalProduksiFiltered" :label-periode="labelPeriode" />
            <WelcomeFilterBar :selected-bulan="selectedBulan" :selected-tahun="selectedTahun"
                :selected-kabupaten="selectedKabupaten" :search-query="searchQuery" :daftar-bulan="DAFTAR_BULAN"
                :tahun-tersedia="props.tahunTersedia" :daftar-kabupaten="daftarKabupaten" :label-periode="labelPeriode"
                @update:selected-bulan="selectedBulan = $event" @update:selected-tahun="selectedTahun = $event"
                @update:selected-kabupaten="selectedKabupaten = $event" @update:search-query="searchQuery = $event"
                @reset="resetAllFilters" 
                />
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
                <div class="lg:col-span-7">
                    <WelcomeMap ref="mapRef" :bku-list="props.bkuList" :selected-kabupaten="selectedKabupaten"
                        :label-periode="labelPeriode" @select-city="selectedKabupaten = $event"
                        @reset-filter="resetMapFilter" />
                </div>
                <div class="space-y-5 lg:col-span-5">
                    <WelcomeCategoryChart :category-stats="categoryStats" :hovered-category="hoveredCategory"
                        :active-hovered-data="activeHoveredData" :selected-kabupaten="selectedKabupaten"
                        :total-bku="filteredBku.length" @update:hovered-category="hoveredCategory = $event" />
                    <WelcomeBkuList :bku-list="filteredBku" :category-meta="CATEGORY_COLORS" />
                </div>
            </div>
        </main>
    </div>
</template>
