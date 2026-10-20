<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, onMounted, computed, watch } from 'vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

interface BkuItem {
    id: number;
    nama: string;
    kota: string | null;
    penetapan: number;
    total_produksi: number | string;
}

const props = withDefaults(
    defineProps<{
        bkuList?: BkuItem[];
    }>(),
    {
        bkuList: () => [],
    }
);

const page = usePage<any>();

// Fix leaflet icon path issue with Vite
delete (L.Icon.Default.prototype as any)._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon-2x.png',
    iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon.png',
    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
});

// Koordinat referensi Kabupaten / Kota / Kecamatan migas di Sumatera Selatan
const CITY_COORDINATES: Record<string, [number, number]> = {
    'Tanjung Enim': [-3.7153, 103.8015],
    'Keluang': [-2.6778, 103.9011],
    'Palembang': [-2.9761, 104.7754],
    'Lahat': [-3.7925, 103.5413],
    'Prabumulih': [-3.4286, 104.2300],
    'Muara Enim': [-3.6441, 103.7770],
    'Musi Banyuasin': [-2.8943, 103.8569],
    'Sekayu': [-2.8833, 103.8333],
    'Sungai Lilin': [-2.6056, 104.0928],
    'Babat Toman': [-2.7333, 103.5833],
    'Pendopo': [-3.3108, 103.8447],
    'PALI': [-3.2166, 103.8447],
    'Penukal Abab Lematang Ilir': [-3.2166, 103.8447],
    'Banyuasin': [-2.8837, 104.3811],
    'Ogan Ilir': [-3.4357, 104.6293],
    'Indralaya': [-3.2389, 104.6469],
    'Ogan Komering Ilir': [-3.3616, 104.8398],
    'OKI': [-3.3616, 104.8398],
    'Kayuagung': [-3.3941, 104.8466],
    'Ogan Komering Ulu': [-4.1293, 104.1685],
    'OKU': [-4.1293, 104.1685],
    'Baturaja': [-4.1293, 104.1685],
    'OKU Timur': [-3.8587, 104.5492],
    'Martapura': [-4.3167, 104.3500],
    'OKU Selatan': [-4.6548, 104.0044],
    'Muaradua': [-4.5244, 104.0886],
    'Musi Rawas': [-3.0805, 103.0315],
    'Muratara': [-2.7483, 102.8524],
    'Musi Rawas Utara': [-2.7483, 102.8524],
    'Lubuklinggau': [-3.2964, 102.8617],
    'Pagar Alam': [-4.0279, 103.2505],
    'Empat Lawang': [-3.7431, 102.9995],
};

const cleanCityName = (name?: string | null): string => {
    if (!name) return '';
    return name
        .trim()
        .toLowerCase()
        .replace(/^(kabupaten|kab\.|kota)\s+/i, '')
        .trim();
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
    for (const [key, coords] of Object.entries(CITY_COORDINATES)) {
        const keyClean = cleanCityName(key);
        if (keyClean === clean || keyClean.includes(clean) || clean.includes(keyClean)) {
            return coords;
        }
    }
    return [-3.3194 + (Math.sin(cityName.length) * 0.3), 104.2 + (Math.cos(cityName.length) * 0.4)];
};

const selectedKabupaten = ref<string | null>(null);
const mapContainer = ref<HTMLElement | null>(null);
let leafletMap: L.Map | null = null;
let markersLayer: L.LayerGroup | null = null;

const filteredBku = computed(() => {
    if (!selectedKabupaten.value) {
        return props.bkuList;
    }
    return props.bkuList.filter(bku => isSameCity(bku.kota, selectedKabupaten.value));
});

const renderMarkers = () => {
    if (!leafletMap || !markersLayer) return;

    markersLayer.clearLayers();

    const locationMap = new Map<string, { displayName: string; coords: [number, number]; count: number; totalProd: number }>();

    (props.bkuList || []).forEach(bku => {
        if (!bku.kota || !bku.kota.trim()) return;
        const normalizedKey = cleanCityName(bku.kota);
        const prod = Number(bku.total_produksi) || 0;

        if (locationMap.has(normalizedKey)) {
            const item = locationMap.get(normalizedKey)!;
            item.count += 1;
            item.totalProd += prod;
        } else {
            locationMap.set(normalizedKey, {
                displayName: bku.kota.trim(),
                coords: getCoordinates(bku.kota),
                count: 1,
                totalProd: prod,
            });
        }
    });

    locationMap.forEach(loc => {
        const marker = L.marker(loc.coords as L.LatLngExpression);

        marker.bindPopup(`
            <div class="text-xs">
                <b class="text-sm font-semibold text-slate-800">${loc.displayName}</b><br>
                <div class="mt-1 space-y-0.5">
                    <div>Jumlah BKU: <b class="text-slate-700">${loc.count}</b></div>
                    <div>Total Produksi: <b class="text-emerald-700">${loc.totalProd.toLocaleString('id-ID')} Barel</b></div>
                </div>
                <div class="mt-2 text-amber-600 font-medium">Klik untuk filter daftar BKU</div>
            </div>
        `);

        marker.on('mouseover', () => {
            marker.openPopup();
        });

        marker.on('mouseout', () => {
            if (selectedKabupaten.value !== loc.displayName) {
                marker.closePopup();
            }
        });

        marker.on('click', () => {
            selectedKabupaten.value = loc.displayName;
            marker.openPopup();
            leafletMap?.flyTo(loc.coords as L.LatLngExpression, 9, {
                animate: true,
                duration: 1.2
            });
        });

        markersLayer!.addLayer(marker);
    });
};

onMounted(() => {
    if (mapContainer.value) {
        // Hapus leaflet ID jika ada dari HMR sebelumnya untuk mencegah error
        if ((mapContainer.value as any)._leaflet_id) {
            (mapContainer.value as any)._leaflet_id = null;
        }

        leafletMap = L.map(mapContainer.value).setView([-3.3194, 104.2142], 7);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19
        }).addTo(leafletMap);

        markersLayer = L.layerGroup().addTo(leafletMap);

        renderMarkers();
    }
});

watch(
    () => props.bkuList,
    () => {
        renderMarkers();
    },
    { deep: true }
);

const resetFilter = () => {
    selectedKabupaten.value = null;
    leafletMap?.flyTo([-3.3194, 104.2142], 7, {
        animate: true,
        duration: 1.2
    });
};
</script>

<template>

    <Head title="Peta BKU Sumatera Selatan" />

    <div
        class="min-h-screen bg-slate-50 font-sans text-slate-900 selection:bg-amber-500 selection:text-white dark:bg-slate-950 dark:text-slate-100">
        <!-- Navigation -->
        <nav
            class="sticky top-0 z-50 flex items-center justify-between border-b border-slate-200 bg-white/80 px-6 py-4 backdrop-blur-md dark:border-slate-800 dark:bg-slate-950/80">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl text-white">
                    <img src="/images/logo_esdm.png" alt="Logo ESDM" />
                </div>
                <h1 class="text-xl font-bold tracking-tight text-slate-800 dark:text-white">
                    ESDM <span class="text-amber-500">Sumsel</span>
                </h1>
            </div>

            <div class="flex items-center gap-4">
                <Link v-if="page.props.auth?.user" :href="route('dashboard')"
                    class="rounded-full bg-amber-500 px-5 py-2 text-sm font-medium text-white shadow-md shadow-amber-500/20 transition-all hover:bg-amber-600 hover:shadow-lg hover:shadow-amber-500/30">
                    Dashboard
                </Link>
                <template v-else>
                    <Link :href="route('login')"
                        class="rounded-full bg-slate-900 px-5 py-2 text-sm font-medium text-white shadow-md transition-all hover:bg-slate-800 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100">
                        Log in
                    </Link>
                </template>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-8 text-center">
                <h2
                    class="bg-gradient-to-r from-amber-500 to-orange-600 bg-clip-text text-3xl font-extrabold text-transparent sm:text-5xl">
                    Sebaran BKU Sumatera Selatan
                </h2>
                <p class="mx-auto mt-4 max-w-2xl text-lg text-slate-600 dark:text-slate-400">
                    Peta interaktif wilayah operasi produksi minyak bumi. Klik pada marker kabupaten/kota untuk melihat
                    daftar
                    BKU di wilayah tersebut.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                <!-- Map Section -->
                <div class="lg:col-span-2">
                    <div
                        class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-200/50 dark:border-slate-800 dark:bg-slate-900 dark:shadow-none">
                        <div
                            class="border-b border-slate-100 bg-slate-50 px-6 py-4 dark:border-slate-800 dark:bg-slate-900/50">
                            <h3
                                class="flex items-center gap-2 text-lg font-semibold text-slate-800 dark:text-slate-200">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor" class="h-5 w-5 text-amber-500">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z" />
                                </svg>
                                Peta Interaktif
                            </h3>
                        </div>
                        <div class="relative h-[500px] w-full">
                            <div ref="mapContainer" class="h-full w-full"></div>
                        </div>
                    </div>
                </div>

                <!-- Data List Section -->
                <div class="flex flex-col">
                    <div
                        class="flex h-full flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-200/50 dark:border-slate-800 dark:bg-slate-900 dark:shadow-none">
                        <div
                            class="flex items-center justify-between border-b border-slate-100 bg-slate-50 px-6 py-4 dark:border-slate-800 dark:bg-slate-900/50">
                            <h3
                                class="flex items-center gap-2 text-lg font-semibold text-slate-800 dark:text-slate-200">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor" class="h-5 w-5 text-amber-500">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                </svg>
                                Daftar BKU
                            </h3>
                            <button v-if="selectedKabupaten" @click="resetFilter"
                                class="flex items-center gap-1 rounded-full bg-slate-200 px-3 py-1 text-xs font-medium text-slate-700 transition-colors hover:bg-slate-300 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                    class="h-3 w-3">
                                    <path
                                        d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                                </svg>
                                Reset Filter
                            </button>
                        </div>

                        <div class="bg-amber-50/50 px-6 py-3 dark:bg-amber-900/10" v-if="selectedKabupaten">
                            <p class="text-sm text-amber-800 dark:text-amber-400">
                                Menampilkan BKU di <span class="font-bold">{{ selectedKabupaten }}</span>
                            </p>
                        </div>

                        <div class="flex-1 overflow-y-auto p-2">
                            <ul v-if="filteredBku.length > 0" class="space-y-2">
                                <li v-for="(bku, index) in filteredBku" :key="bku.id"
                                    class="group rounded-xl border border-transparent bg-white p-4 transition-all hover:border-amber-200 hover:bg-amber-50 hover:shadow-md dark:bg-slate-900 dark:hover:border-amber-900/50 dark:hover:bg-slate-800/80">
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <h4
                                                class="font-semibold text-slate-800 transition-colors group-hover:text-amber-600 dark:text-slate-200 dark:group-hover:text-amber-400">
                                                {{ bku.nama }}
                                            </h4>
                                            <div
                                                class="mt-1 flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                                    fill="currentColor" class="h-3.5 w-3.5">
                                                    <path fill-rule="evenodd"
                                                        d="M9.69 18.933l.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 00.281-.14c.186-.096.446-.24.757-.433.62-.384 1.445-.966 2.274-1.765C15.302 14.988 17 12.493 17 9A7 7 0 103 9c0 3.492 1.698 5.988 3.355 7.584a13.731 13.731 0 002.273 1.765 11.842 11.842 0 00.976.544l.062.029.018.008.006.003zM10 11.25a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                {{ bku.kota || 'Tidak ada data kota' }}
                                            </div>
                                        </div>
                                        <div class="items-end gap-1.5">
                                            <span
                                                class="items-center rounded-md bg-amber-50 px-2 py-1 text-xs font-semibold text-amber-700 ring-1 ring-inset ring-amber-600/20 dark:bg-amber-400/10 dark:text-amber-400 dark:ring-amber-400/20">
                                                Produksi: {{ Number(bku.total_produksi).toLocaleString('id-ID') }} Barel
                                            </span>
                                        </div>
                                    </div>
                                    <hr v-if="index !== filteredBku.length - 1"
                                        class="mt-2 border-slate-200/60 dark:border-slate-800" />
                                </li>
                            </ul>
                            <div v-else class="flex h-full flex-col items-center justify-center py-12 text-center">
                                <div class="rounded-full bg-slate-100 p-3 dark:bg-slate-800">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="h-6 w-6 text-slate-400">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                    </svg>
                                </div>
                                <h4 class="mt-4 font-medium text-slate-900 dark:text-slate-200">Tidak ada data BKU</h4>
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                    Belum ada data BKU untuk wilayah ini.
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
/* Adjust Leaflet popup styles to match the premium theme */
.leaflet-popup-content-wrapper {
    border-radius: 0.75rem;
    box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
}

.leaflet-popup-content {
    margin: 12px 16px;
    font-family: inherit;
    font-size: 0.875rem;
    color: #1e293b;
}

.dark .leaflet-popup-content-wrapper {
    background-color: #0f172a;
    color: #f1f5f9;
}

.dark .leaflet-popup-content {
    color: #f1f5f9;
}

.dark .leaflet-popup-tip {
    background-color: #0f172a;
}
</style>
