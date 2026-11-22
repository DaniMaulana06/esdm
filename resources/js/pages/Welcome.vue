<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import masterKota from '@/data/kota_sumsel.json';
import {
    MapPin,
    PieChart as PieChartIcon,
    RotateCcw,
    Building2,
    Layers,
    CalendarDays,
    Filter,
    Search,
} from 'lucide-vue-next';
import { Barrel } from '@lucide/vue';
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';

// ══════════════════════════════════════════════════════════════════════════
// INTERFACES
// ══════════════════════════════════════════════════════════════════════════

interface BkuItem {
    id: number;
    nama: string;
    kota: string | null;
    kategori: string | null;
    penetapan: number;
    total_produksi: number | string;
    total_lifting: number | string;
}

interface Filters {
    bulan: string;
    tahun: string;
}

interface CategorySegment {
    name: string;
    count: number;
    percentage: number;
    color: string;
    bgClass: string;
    textClass: string;
    strokeDash: string;
    strokeOffset: number;
}

// ══════════════════════════════════════════════════════════════════════════
// PROPS — Ini adalah titik di mana Vue menerima data dari Controller
// ══════════════════════════════════════════════════════════════════════════

const props = withDefaults(
    defineProps<{
        /** Daftar BKU + total produksi (sudah difilter bulan/tahun oleh backend) */
        bkuList?: BkuItem[];
        /** Filter bulan & tahun yang sedang aktif */
        filters?: Filters;
        /** Daftar tahun yang tersedia di database */
        tahunTersedia?: number[];
    }>(),
    {
        bkuList: () => [],
        filters: () => ({ bulan: String(new Date().getMonth() + 1), tahun: String(new Date().getFullYear()) }),
        tahunTersedia: () => [new Date().getFullYear()],
    }
);

const page = usePage<any>();

// ══════════════════════════════════════════════════════════════════════════
// KONSTANTA
// ══════════════════════════════════════════════════════════════════════════

const NAMA_BULAN: Record<string, string> = {
    '1': 'Januari', '2': 'Februari', '3': 'Maret',
    '4': 'April', '5': 'Mei', '6': 'Juni',
    '7': 'Juli', '8': 'Agustus', '9': 'September',
    '10': 'Oktober', '11': 'November', '12': 'Desember',
};

const DAFTAR_BULAN = [
    { value: 'all', label: 'Semua Bulan' },
    ...Object.entries(NAMA_BULAN).map(([val, label]) => ({ value: val, label })),
];

const CATEGORY_COLORS: Record<string, { hex: string; bg: string; text: string; badge: string }> = {
    Perusahaan: {
        hex: '#3B82F6',
        bg: 'bg-blue-500',
        text: 'text-blue-600 dark:text-blue-400',
        badge: 'bg-blue-50 text-blue-700 ring-blue-700/10 dark:bg-blue-900/20 dark:text-blue-300'
    },
    Koperasi: {
        hex: '#10B981',
        bg: 'bg-emerald-500',
        text: 'text-emerald-600 dark:text-emerald-400',
        badge: 'bg-emerald-50 text-emerald-700 ring-emerald-700/10 dark:bg-emerald-900/20 dark:text-emerald-300'
    },
    UMKM: {
        hex: '#F59E0B',
        bg: 'bg-amber-500',
        text: 'text-amber-600 dark:text-amber-400',
        badge: 'bg-amber-50 text-amber-700 ring-amber-700/10 dark:bg-amber-900/20 dark:text-amber-300'
    },
    Lainnya: {
        hex: '#8B5CF6',
        bg: 'bg-purple-500',
        text: 'text-purple-600 dark:text-purple-400',
        badge: 'bg-purple-50 text-purple-700 ring-purple-700/10 dark:bg-purple-900/20 dark:text-purple-300'
    },
};

// ══════════════════════════════════════════════════════════════════════════
// LEAFLET ICON FIX
// ══════════════════════════════════════════════════════════════════════════

delete (L.Icon.Default.prototype as any)._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon-2x.png',
    iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon.png',
    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
});

// ══════════════════════════════════════════════════════════════════════════
// STATE
// ══════════════════════════════════════════════════════════════════════════

// Filter Bulan & Tahun (di-sync dari props.filters yang dikirim Controller)
const selectedBulan = ref<string>(props.filters.bulan ?? 'all');
const selectedTahun = ref<string>(props.filters.tahun ?? String(new Date().getFullYear()));

// Filter Wilayah (client-side, via klik peta atau dropdown)
const selectedKabupaten = ref<string | null>(null);
const searchQuery = ref('');

// Pie chart hover
const hoveredCategory = ref<string | null>(null);

// Leaflet refs
const mapContainer = ref<HTMLElement | null>(null);
let leafletMap: L.Map | null = null;
let markersLayer: L.LayerGroup | null = null;

// ══════════════════════════════════════════════════════════════════════════
// HELPER FUNCTIONS
// ══════════════════════════════════════════════════════════════════════════

const cleanCityName = (name?: string | null): string => {
    if (!name) return '';
    return name.trim().toLowerCase().replace(/^(kabupaten|kab\.|kota)\s+/i, '').trim();
};

const isSameCity = (cityA?: string | null, cityB?: string | null): boolean => {
    if (!cityA || !cityB) return false;
    const a = cleanCityName(cityA);
    const b = cleanCityName(cityB);
    if (!a || !b) return false;
    return a === b || a.includes(b) || b.includes(a);
};

const getCoordinates = (cityName: string): [number, number] => {
    const clean = cleanCityName(cityName);
    const found = masterKota.find(item => {
        const namaClean = cleanCityName(item.nama);
        const resmiClean = cleanCityName(item.nama_resmi);
        return namaClean === clean || resmiClean === clean || clean.includes(namaClean) || namaClean.includes(clean);
    });
    return found ? [found.latitude, found.longitude] : [-3.3194, 104.2142];
};

const getCategoryMeta = (cat?: string | null) => {
    if (!cat) return CATEGORY_COLORS.Lainnya;
    return CATEGORY_COLORS[cat] || CATEGORY_COLORS.Lainnya;
};

/** Label periode aktif untuk ditampilkan di UI */
const labelPeriode = computed(() => {
    const bulan = selectedBulan.value;
    const tahun = selectedTahun.value;

    if (bulan === 'all' && tahun === 'all') return 'Seluruh Periode';
    if (bulan === 'all') return `Tahun ${tahun}`;
    if (tahun === 'all') return `Bulan ${NAMA_BULAN[bulan] ?? bulan}`;
    return `${NAMA_BULAN[bulan] ?? bulan} ${tahun}`;
});

// ══════════════════════════════════════════════════════════════════════════
// DAFTAR KABUPATEN/KOTA UNIK (untuk dropdown wilayah)
// ══════════════════════════════════════════════════════════════════════════

const daftarKabupaten = computed(() => {
    const cities = new Set<string>();
    props.bkuList.forEach(bku => {
        if (bku.kota && bku.kota.trim()) cities.add(bku.kota.trim());
    });
    return Array.from(cities).sort();
});

// ══════════════════════════════════════════════════════════════════════════
// COMPUTED DATA — Filter data berdasarkan wilayah & pencarian
// ══════════════════════════════════════════════════════════════════════════

const filteredBku = computed(() => {
    let list = props.bkuList;

    if (selectedKabupaten.value) {
        list = list.filter(bku => isSameCity(bku.kota, selectedKabupaten.value));
    }

    if (searchQuery.value.trim()) {
        const q = searchQuery.value.trim().toLowerCase();
        list = list.filter(bku => bku.nama.toLowerCase().includes(q));
    }

    return list;
});

const totalProduksiFiltered = computed(() =>
    filteredBku.value.reduce((acc, curr) => acc + (Number(curr.total_produksi) || 0), 0)
);

// ══════════════════════════════════════════════════════════════════════════
// DONUT CHART — Statistik Kategori BKU
// ══════════════════════════════════════════════════════════════════════════

const categoryStats = computed<CategorySegment[]>(() => {
    const list = filteredBku.value;
    const total = list.length;
    if (total === 0) return [];

    const counts: Record<string, number> = {};
    list.forEach(bku => {
        const cat = bku.kategori && bku.kategori.trim() ? bku.kategori.trim() : 'Lainnya';
        counts[cat] = (counts[cat] || 0) + 1;
    });

    let accumulatedOffset = 0;
    const segments: CategorySegment[] = [];
    const sorted = Object.entries(counts).sort((a, b) => b[1] - a[1]);

    sorted.forEach(([catName, count]) => {
        const percentage = Math.round((count / total) * 100);
        const meta = getCategoryMeta(catName);
        segments.push({
            name: catName,
            count,
            percentage,
            color: meta.hex,
            bgClass: meta.bg,
            textClass: meta.text,
            strokeDash: `${percentage} ${100 - percentage}`,
            strokeOffset: -accumulatedOffset,
        });
        accumulatedOffset += percentage;
    });

    return segments;
});

const activeHoveredData = computed(() => {
    if (!hoveredCategory.value) return null;
    return categoryStats.value.find(s => s.name === hoveredCategory.value) || null;
});

// ══════════════════════════════════════════════════════════════════════════
// FILTER ACTIONS — Kirim request ke backend saat filter bulan/tahun berubah
// ══════════════════════════════════════════════════════════════════════════

const applyPeriodFilter = () => {
    router.get(
        route('home'),
        {
            bulan: selectedBulan.value,
            tahun: selectedTahun.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );
};

const resetAllFilters = () => {
    selectedKabupaten.value = null;
    searchQuery.value = '';
    selectedBulan.value = String(new Date().getMonth() + 1);
    selectedTahun.value = String(new Date().getFullYear());
    leafletMap?.flyTo([-3.3194, 104.2142], 7, { animate: true, duration: 1.2 });
    applyPeriodFilter();
};

const resetMapFilter = () => {
    selectedKabupaten.value = null;
    leafletMap?.flyTo([-3.3194, 104.2142], 7, { animate: true, duration: 1.2 });
};

// Watch dropdown perubahan bulan/tahun → fetch data baru dari backend
watch(selectedBulan, () => applyPeriodFilter());
watch(selectedTahun, () => applyPeriodFilter());

// ══════════════════════════════════════════════════════════════════════════
// LEAFLET MAP
// ══════════════════════════════════════════════════════════════════════════

const renderMarkers = () => {
    if (!leafletMap || !markersLayer) return;
    markersLayer.clearLayers();

    const locationMap = new Map<string, {
        displayName: string;
        coords: [number, number];
        count: number;
        totalProd: number;
        categories: Record<string, number>;
    }>();

    (props.bkuList || []).forEach(bku => {
        if (!bku.kota || !bku.kota.trim()) return;
        const normalizedKey = cleanCityName(bku.kota);
        const prod = Number(bku.total_produksi) || 0;
        const cat = bku.kategori || 'Lainnya';

        if (locationMap.has(normalizedKey)) {
            const item = locationMap.get(normalizedKey)!;
            item.count += 1;
            item.totalProd += prod;
            item.categories[cat] = (item.categories[cat] || 0) + 1;
        } else {
            locationMap.set(normalizedKey, {
                displayName: bku.kota.trim(),
                coords: getCoordinates(bku.kota),
                count: 1,
                totalProd: prod,
                categories: { [cat]: 1 },
            });
        }
    });

    locationMap.forEach(loc => {
        const marker = L.marker(loc.coords as L.LatLngExpression);
        const categoryText = Object.entries(loc.categories)
            .map(([cat, cnt]) => `${cat}: ${cnt}`)
            .join(', ');

        marker.bindPopup(`
            <div class="text-xs p-0.5">
                <div class="text-sm font-bold text-slate-800">${loc.displayName}</div>
                <div class="mt-1 text-[11px] text-slate-500">${labelPeriode.value}</div>
                <div class="mt-2 space-y-1 text-slate-600">
                    <div class="flex justify-between gap-4">
                        <span>Jumlah BKU:</span>
                        <b class="text-slate-800">${loc.count}</b>
                    </div>
                    <div class="flex justify-between gap-4">
                        <span>Total Produksi:</span>
                        <b class="text-emerald-600">${loc.totalProd.toLocaleString('id-ID')} Barel</b>
                    </div>
                    <div class="text-[11px] text-slate-500 pt-1 border-t border-slate-100">
                        ${categoryText}
                    </div>
                </div>
            </div>
        `);

        marker.on('mouseover', () => marker.openPopup());
        marker.on('mouseout', () => {
            if (selectedKabupaten.value !== loc.displayName) marker.closePopup();
        });
        marker.on('click', () => {
            selectedKabupaten.value = loc.displayName;
            marker.openPopup();
            leafletMap?.flyTo(loc.coords as L.LatLngExpression, 9, { animate: true, duration: 1.2 });
        });

        markersLayer!.addLayer(marker);
    });
};

onMounted(() => {
    if (mapContainer.value) {
        if ((mapContainer.value as any)._leaflet_id) {
            (mapContainer.value as any)._leaflet_id = null;
        }

        leafletMap = L.map(mapContainer.value).setView([-3.3194, 104.2142], 7);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19,
        }).addTo(leafletMap);

        markersLayer = L.layerGroup().addTo(leafletMap);
        renderMarkers();
    }
});

onUnmounted(() => {
    if (leafletMap) {
        leafletMap.remove();
        leafletMap = null;
    }
});

watch(() => props.bkuList, () => renderMarkers(), { deep: true });
</script>

<template>

    <Head title="Peta BKU Sumatera Selatan" />

    <div
        class="min-h-screen bg-slate-50 font-sans text-slate-900 selection:bg-amber-500 selection:text-white dark:bg-slate-950 dark:text-slate-100">

        <!-- ════════════════════════════════════════════════════════════ -->
        <!-- NAVIGATION BAR                                              -->
        <!-- ════════════════════════════════════════════════════════════ -->
        <nav
            class="sticky top-0 z-[1000] border-b border-slate-200/80 bg-white/90 px-6 py-3.5 shadow-sm backdrop-blur-lg dark:border-slate-800 dark:bg-slate-950/90">
            <div class="mx-auto flex max-w-7xl items-center justify-between">
                <div class="flex items-center gap-3">
                    <img src="/images/logo_esdm.png" alt="Logo ESDM" class="h-9 w-auto object-contain" />
                    <div>
                        <h1 class="text-xl font-bold tracking-tight text-slate-800 dark:text-white">
                            Dinas ESDM <span class="text-amber-500">Sumsel</span>
                        </h1>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">
                            Portal Sumur Minyak Rakyat Provinsi Sumsel
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <Link v-if="page.props.auth?.user" :href="route('dashboard')"
                        class="inline-flex items-center gap-2 rounded-full bg-amber-500 px-5 py-2 text-sm font-semibold text-white shadow-sm transition-all hover:bg-amber-600">
                        <Layers class="h-4 w-4" />
                        Dashboard
                    </Link>
                    <Link v-else :href="route('login')"
                        class="rounded-full bg-amber-500 px-5 py-2 text-sm font-semibold text-white shadow-sm transition-all hover:bg-amber-600">
                        Log in
                    </Link>
                </div>
            </div>
        </nav>

        <!-- ════════════════════════════════════════════════════════════ -->
        <!-- MAIN CONTENT                                                -->
        <!-- ════════════════════════════════════════════════════════════ -->
        <main class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">

            <!-- ──────────────────────────────────────────────────────── -->
            <!-- HERO SECTION — Judul & Deskripsi                        -->
            <!-- ──────────────────────────────────────────────────────── -->
            <div class="text-center">
                <h2 class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl dark:text-white">
                    Peta Sebaran BKU Pengelola
                    <span class="text-amber-500">Sumur Minyak Rakyat</span>
                </h2>
                <p class="mx-auto mt-2 max-w-2xl text-sm text-slate-600 dark:text-slate-400">
                    Pantau sebaran unit usaha dan realisasi produksi minyak bumi per wilayah.
                    Gunakan filter di bawah untuk melihat data per bulan atau kabupaten/kota.
                </p>
            </div>

            <!-- ──────────────────────────────────────────────────────── -->
            <!-- BILAH FILTER — Bulan, Tahun, Kabupaten, Cari, Reset     -->
            <!-- ──────────────────────────────────────────────────────── -->
            <div
                class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center gap-2 mb-3">
                    <Filter class="h-4 w-4 text-slate-500" />
                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">Filter Data</span>
                    <span
                        class="ml-auto rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-700 dark:bg-amber-950/60 dark:text-amber-300">
                        {{ labelPeriode }}
                    </span>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5">
                    <!-- Filter Bulan -->
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-500">Bulan</label>
                        <Select v-model="selectedBulan">
                            <SelectTrigger class="w-full">
                                <SelectValue placeholder="Pilih Bulan" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectGroup>
                                    <SelectItem v-for="b in DAFTAR_BULAN" :key="b.value" :value="b.value">
                                        {{ b.label }}
                                    </SelectItem>
                                </SelectGroup>
                            </SelectContent>
                        </Select>
                    </div>

                    <!-- Filter Tahun -->
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-500">Tahun</label>
                        <Select v-model="selectedTahun">
                            <SelectTrigger class="w-full">
                                <SelectValue placeholder="Pilih Tahun" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectGroup>
                                    <SelectItem value="all">Semua Tahun</SelectItem>
                                    <SelectItem v-for="t in tahunTersedia" :key="t" :value="String(t)">
                                        {{ t }}
                                    </SelectItem>
                                </SelectGroup>
                            </SelectContent>
                        </Select>
                    </div>

                    <!-- Filter Kabupaten/Kota (client-side) -->
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-500">Kabupaten/Kota</label>
                        <Select v-model="selectedKabupaten">
                            <SelectTrigger class="w-full">
                                <SelectValue placeholder="Semua Wilayah" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectGroup>
                                    <SelectItem :value="null as any">Semua Wilayah</SelectItem>
                                    <SelectItem v-for="kab in daftarKabupaten" :key="kab" :value="kab">
                                        {{ kab }}
                                    </SelectItem>
                                </SelectGroup>
                            </SelectContent>
                        </Select>
                    </div>

                    <!-- Pencarian Nama BKU (client-side) -->
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-500">Cari BKU</label>
                        <div class="relative">
                            <Search
                                class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                            <Input v-model="searchQuery" type="text" placeholder="Ketik nama BKU..." class="pl-9" />
                        </div>
                    </div>

                    <!-- Tombol Reset -->
                    <div class="flex items-end">
                        <Button @click="resetAllFilters" 
                            class="inline-flex w-full items-center justify-center gap-1.5 rounded-md border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700">
                            <RotateCcw class="h-3.5 w-3.5" />
                            Reset
                        </Button>
                    </div>
                </div>
            </div>

            <!-- ──────────────────────────────────────────────────────── -->
            <!-- KARTU KPI RINGKASAN — Wilayah, Jumlah BKU, Produksi     -->
            <!-- ──────────────────────────────────────────────────────── -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <!-- Wilayah Terpilih -->
                <div
                    class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium uppercase tracking-wider text-slate-500">
                            Wilayah Terpilih
                        </span>
                        <MapPin class="h-5 w-5 text-amber-500" />
                    </div>
                    <div class="mt-2 text-xl font-bold text-slate-800 dark:text-white">
                        {{ selectedKabupaten || 'Seluruh Sumatera Selatan' }}
                    </div>
                </div>

                <!-- Jumlah BKU -->
                <div
                    class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium uppercase tracking-wider text-slate-500">
                            Jumlah BKU
                        </span>
                        <Building2 class="h-5 w-5 text-blue-500" />
                    </div>
                    <div class="mt-2 text-2xl font-bold text-slate-800 dark:text-white">
                        {{ filteredBku.length }}
                        <span class="text-sm font-normal text-slate-500">Unit Usaha</span>
                    </div>
                </div>

                <!-- Total Produksi -->
                <div
                    class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium uppercase tracking-wider text-slate-500">
                            Total Produksi
                        </span>
                        <Barrel class="h-5 w-5 text-amber-900" />
                    </div>
                    <div class="mt-2 text-2xl font-bold text-emerald-600 dark:text-emerald-400">
                        {{ totalProduksiFiltered.toLocaleString('id-ID') }}
                        <span class="text-sm font-normal text-slate-500">Barel</span>
                    </div>
                    <p class="flex gap-1 mt-1 text-xs text-slate-500 items-center">
                        <CalendarDays class="mr-0.5 inline h-3 w-3" />
                        {{ labelPeriode }}
                    </p>
                </div>
            </div>

            <!-- ──────────────────────────────────────────────────────── -->
            <!-- GRID UTAMA: Peta (Kiri) & Statistik + Daftar BKU (Kanan)-->
            <!-- ──────────────────────────────────────────────────────── -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">

                <!-- === KOLOM PETA INTERAKTIF (7 col) === -->
                <div class="lg:col-span-7">
                    <div
                        class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <div
                            class="flex items-center justify-between border-b border-slate-100 bg-slate-50/70 px-5 py-3 dark:border-slate-800 dark:bg-slate-900/50">
                            <h3
                                class="flex items-center gap-2 text-sm font-semibold text-slate-800 dark:text-slate-200">
                                <MapPin class="h-4 w-4 text-amber-500" />
                                Peta Sebaran Wilayah
                            </h3>
                            <button v-if="selectedKabupaten" @click="resetMapFilter"
                                class="inline-flex items-center gap-1 rounded-md bg-amber-50 px-2 py-1 text-xs font-semibold text-amber-700 hover:bg-amber-100 dark:bg-amber-950/50 dark:text-amber-300">
                                <RotateCcw class="h-3 w-3" />
                                Reset Peta
                            </button>
                        </div>
                        <div class="relative z-0 h-[550px] w-full">
                            <div ref="mapContainer" class="h-full w-full"></div>
                        </div>
                    </div>
                </div>

                <!-- === KOLOM STATISTIK + DAFTAR BKU (5 col) === -->
                <div class="space-y-5 lg:col-span-5">

                    <!-- KARTU DONUT CHART: Proporsi Kategori -->
                    <div
                        class="rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <div
                            class="flex items-center justify-between border-b border-slate-100 bg-slate-50/70 px-5 py-3 dark:border-slate-800 dark:bg-slate-900/50">
                            <h3
                                class="flex items-center gap-2 text-sm font-semibold text-slate-800 dark:text-slate-200">
                                <PieChartIcon class="h-4 w-4 text-blue-500" />
                                Proporsi Kategori BKU
                            </h3>
                            <span v-if="selectedKabupaten"
                                class="rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-semibold text-amber-800 dark:bg-amber-950/80 dark:text-amber-300">
                                {{ selectedKabupaten }}
                            </span>
                            <span v-else class="text-[11px] text-slate-500">
                                Semua Wilayah
                            </span>
                        </div>

                        <div class="p-5">
                            <div v-if="categoryStats.length > 0">
                                <!-- Donut Chart SVG -->
                                <div class="relative flex items-center justify-center py-1">
                                    <div class="relative h-36 w-36">
                                        <svg viewBox="0 0 42 42" class="h-full w-full">
                                            <circle cx="21" cy="21" r="15.91549430918954" fill="transparent"
                                                stroke="currentColor" stroke-width="5"
                                                class="text-slate-100 dark:text-slate-800" />
                                            <circle v-for="slice in categoryStats" :key="slice.name" cx="21" cy="21"
                                                r="15.91549430918954" fill="transparent" :stroke="slice.color"
                                                :stroke-width="hoveredCategory === slice.name ? '6.5' : '5'"
                                                :stroke-dasharray="slice.strokeDash"
                                                :stroke-dashoffset="slice.strokeOffset" transform="rotate(-90 21 21)"
                                                class="cursor-pointer transition-all duration-300"
                                                :class="{ 'opacity-40': hoveredCategory && hoveredCategory !== slice.name }"
                                                @mouseenter="hoveredCategory = slice.name"
                                                @mouseleave="hoveredCategory = null" />
                                        </svg>
                                        <div
                                            class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center text-center">
                                            <template v-if="activeHoveredData">
                                                <span
                                                    class="text-2xl font-bold tracking-tight text-slate-800 dark:text-white">
                                                    {{ activeHoveredData.percentage }}%
                                                </span>
                                                <span
                                                    class="max-w-[90px] truncate text-[10px] font-semibold text-slate-500 dark:text-slate-400">
                                                    {{ activeHoveredData.name }}
                                                </span>
                                            </template>
                                            <template v-else>
                                                <span
                                                    class="text-2xl font-bold tracking-tight text-slate-800 dark:text-white">
                                                    {{ filteredBku.length }}
                                                </span>
                                                <span
                                                    class="text-[10px] font-medium uppercase tracking-wider text-slate-400">
                                                    Total BKU
                                                </span>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                <!-- Legend Kategori -->
                                <div class="mt-3 grid grid-cols-2 gap-2">
                                    <div v-for="cat in categoryStats" :key="cat.name"
                                        @mouseenter="hoveredCategory = cat.name" @mouseleave="hoveredCategory = null"
                                        class="flex cursor-pointer items-center justify-between rounded-lg border p-2 transition-all"
                                        :class="hoveredCategory === cat.name
                                            ? 'border-slate-300 bg-slate-50 shadow-sm dark:border-slate-700 dark:bg-slate-800'
                                            : 'border-slate-100 bg-slate-50/50 hover:bg-slate-50 dark:border-slate-800/80 dark:bg-slate-900/40 dark:hover:bg-slate-800/60'">
                                        <div class="flex items-center gap-1.5">
                                            <span class="h-2.5 w-2.5 flex-shrink-0 rounded-full"
                                                :style="{ backgroundColor: cat.color }" />
                                            <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                                                {{ cat.name }}
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-1 text-xs">
                                            <span class="font-bold text-slate-900 dark:text-white">{{ cat.count
                                                }}</span>
                                            <span
                                                class="rounded bg-slate-200/70 px-1 py-0.5 text-[10px] font-bold text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                                {{ cat.percentage }}%
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-else class="py-6 text-center text-sm text-slate-500">
                                Belum ada data kategori untuk wilayah ini.
                            </div>
                        </div>
                    </div>

                    <!-- KARTU DAFTAR BKU -->
                    <div
                        class="flex flex-col rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <div
                            class="flex items-center justify-between border-b border-slate-100 bg-slate-50/70 px-5 py-3 dark:border-slate-800 dark:bg-slate-900/50">
                            <h3
                                class="flex items-center gap-2 text-sm font-semibold text-slate-800 dark:text-slate-200">
                                <Building2 class="h-4 w-4 text-amber-500" />
                                Daftar Unit BKU
                            </h3>
                            <span class="text-xs font-medium text-slate-500">
                                {{ filteredBku.length }} BKU
                            </span>
                        </div>

                        <div class="max-h-[400px] overflow-y-auto p-3">
                            <ul v-if="filteredBku.length > 0" class="space-y-2">
                                <li v-for="bku in filteredBku" :key="bku.id"
                                    class="group rounded-xl border border-slate-100 bg-slate-50/40 p-3 transition-all hover:border-amber-200 hover:bg-amber-50/60 hover:shadow-sm dark:border-slate-800/80 dark:bg-slate-900/60 dark:hover:border-amber-900/40 dark:hover:bg-slate-800/60">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center gap-2">
                                                <h4
                                                    class="truncate text-sm font-semibold text-slate-800 transition-colors group-hover:text-amber-600 dark:text-slate-200 dark:group-hover:text-amber-400">
                                                    {{ bku.nama }}
                                                </h4>
                                                <span
                                                    class="inline-flex flex-shrink-0 items-center rounded-md px-1.5 py-0.5 text-[10px] font-medium ring-1 ring-inset"
                                                    :class="getCategoryMeta(bku.kategori).badge">
                                                    {{ bku.kategori || 'Umum' }}
                                                </span>
                                            </div>
                                            <div
                                                class="mt-0.5 flex items-center gap-1 text-xs text-slate-500 dark:text-slate-400">
                                                <MapPin class="h-3 w-3 flex-shrink-0" />
                                                <span class="truncate">{{ bku.kota || 'Tidak ada data kota' }}</span>
                                            </div>
                                        </div>
                                        <div class="flex-shrink-0 text-right">
                                            <span
                                                class="inline-flex items-center rounded-lg bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-600/20 dark:bg-emerald-950/40 dark:text-emerald-400">
                                                {{ Number(bku.total_produksi).toLocaleString('id-ID') }} Barel
                                            </span>
                                        </div>
                                    </div>
                                </li>
                            </ul>

                            <div v-else class="flex flex-col items-center justify-center py-8 text-center">
                                <div class="rounded-full bg-slate-100 p-3 dark:bg-slate-800">
                                    <Building2 class="h-6 w-6 text-slate-400" />
                                </div>
                                <h4 class="mt-3 font-medium text-slate-800 dark:text-slate-200">
                                    Tidak ada data BKU
                                </h4>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                    Belum ada data BKU untuk filter yang dipilih.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<style>
/* Kustomisasi Popup Leaflet */
.leaflet-popup-content-wrapper {
    border-radius: 1rem;
    box-shadow: 0 10px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
    border: 1px solid rgba(226, 232, 240, 0.8);
    padding: 0;
}

.leaflet-popup-content {
    margin: 12px 16px;
    font-family: inherit;
    font-size: 0.875rem;
    color: #1e293b;
    line-height: 1.4;
}

.dark .leaflet-popup-content-wrapper {
    background-color: #0f172a;
    border-color: #1e293b;
    color: #f1f5f9;
}

.dark .leaflet-popup-content {
    color: #f1f5f9;
}

.dark .leaflet-popup-tip {
    background-color: #0f172a;
}
</style>
