<script setup lang="ts">
import { onMounted, onUnmounted, ref, watch } from 'vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import masterKota from '@/data/kota_sumsel.json';
import { MapPin, RotateCcw } from 'lucide-vue-next';

interface BkuItem {
    id: number; nama: string; kota: string | null; kategori: string | null;
    penetapan: number; total_produksi: number | string; total_lifting: number | string;
}
const props = defineProps<{ bkuList: BkuItem[]; selectedKabupaten: string | null; labelPeriode: string }>();
const emit = defineEmits<{ (e: 'select-city', city: string): void; (e: 'reset-filter'): void }>();
const mapContainer = ref<HTMLElement | null>(null);
let leafletMap: L.Map | null = null;
let markersLayer: L.LayerGroup | null = null;

// Perbaikan URL ikon default Leaflet
// @ts-ignore - properti internal Leaflet untuk menghapus URL ikon bawaan
delete (L.Icon.Default.prototype as any)._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon-2x.png',
    iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon.png',
    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
});

const cleanCityName = (name?: string | null): string => {
    if (!name) return '';
    return name.trim().toLowerCase().replace(/^(kabupaten|kab\.|kota)\s+/i, '').trim();
};
const getCoordinates = (cityName: string): [number, number] => {
    const clean = cleanCityName(cityName);
    const found = masterKota.find((item: any) => {
        const namaClean = cleanCityName(item.nama);
        const resmiClean = cleanCityName(item.nama_resmi);
        return namaClean === clean || resmiClean === clean || clean.includes(namaClean) || namaClean.includes(clean);
    });
    return found ? [found.latitude, found.longitude] : [-3.3194, 104.2142];
};
const flyToDefault = () => leafletMap?.flyTo([-3.3194, 104.2142], 7, { animate: true, duration: 1.2 });
const renderMarkers = () => {
    if (!leafletMap || !markersLayer) return;
    markersLayer.clearLayers();
    const locationMap = new Map<string, { displayName: string; coords: [number, number]; count: number; totalProd: number; categories: Record<string, number> }>();
    props.bkuList.forEach((bku) => {
        if (!bku.kota?.trim()) return;
        const key = cleanCityName(bku.kota);
        const prod = Number(bku.total_produksi) || 0;
        const cat = bku.kategori || 'Lainnya';
        if (locationMap.has(key)) {
            const item = locationMap.get(key)!;
            item.count += 1; item.totalProd += prod; item.categories[cat] = (item.categories[cat] || 0) + 1;
        } else {
            locationMap.set(key, { displayName: bku.kota.trim(), coords: getCoordinates(bku.kota), count: 1, totalProd: prod, categories: { [cat]: 1 } });
        }
    });
    locationMap.forEach((loc) => {
        const marker = L.marker(loc.coords as L.LatLngExpression);
        const categoryText = Object.entries(loc.categories).map(([cat, cnt]) => `${cat}: ${cnt}`).join(', ');
        marker.bindPopup(`<div class="text-xs p-0.5"><div class="text-sm font-bold text-slate-800">${loc.displayName}</div><div class="mt-1 text-[11px] text-slate-500">${props.labelPeriode}</div><div class="mt-2 space-y-1 text-slate-600"><div class="flex justify-between gap-4"><span>Jumlah BKU:</span><b class="text-slate-800">${loc.count}</b></div><div class="flex justify-between gap-4"><span>Total Produksi:</span><b class="text-emerald-600">${loc.totalProd.toLocaleString('id-ID')} Barel</b></div><div class="border-t border-slate-100 pt-1 text-[11px] text-slate-500">${categoryText}</div></div></div>`);
        marker.on('mouseover', () => marker.openPopup());
        marker.on('mouseout', () => { if (props.selectedKabupaten !== loc.displayName) marker.closePopup(); });
        marker.on('click', () => { emit('select-city', loc.displayName); marker.openPopup(); leafletMap?.flyTo(loc.coords as L.LatLngExpression, 9, { animate: true, duration: 1.2 }); });
        markersLayer!.addLayer(marker);
    });
};
onMounted(() => {
    if (!mapContainer.value) return;
    if ((mapContainer.value as any)._leaflet_id) (mapContainer.value as any)._leaflet_id = null;
    leafletMap = L.map(mapContainer.value).setView([-3.3194, 104.2142], 7);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors', maxZoom: 19 }).addTo(leafletMap);
    markersLayer = L.layerGroup().addTo(leafletMap);
    renderMarkers();
});
onUnmounted(() => { if (leafletMap) { leafletMap.remove(); leafletMap = null; markersLayer = null; } });
watch(() => props.bkuList, renderMarkers, { deep: true });
watch(() => props.labelPeriode, renderMarkers);
watch(() => props.selectedKabupaten, (city) => {
    if (!city) return;
    const coords = getCoordinates(city);
    leafletMap?.flyTo(coords, 9, { animate: true, duration: 1.2 });
});
defineExpose({ flyToDefault });
</script>

<template>
    <section
        class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div
            class="flex items-center justify-between border-b border-slate-100 bg-slate-50/70 px-5 py-3 dark:border-slate-800 dark:bg-slate-900/50">
            <h3 class="flex items-center gap-2 text-sm font-semibold text-slate-800 dark:text-slate-200">
                <MapPin class="h-4 w-4 text-amber-500" />Peta Sebaran Wilayah
            </h3>
            <button v-if="selectedKabupaten" @click="emit('reset-filter'); flyToDefault()"
                class="inline-flex items-center gap-1 rounded-md bg-amber-50 px-2 py-1 text-xs font-semibold text-amber-700 hover:bg-amber-100 dark:bg-amber-950/50 dark:text-amber-300">
                <RotateCcw class="h-3 w-3" />Reset Peta
            </button>
        </div>
        <div class="relative z-0 h-[550px] w-full">
            <div ref="mapContainer" class="h-full w-full"></div>
        </div>
    </section>
</template>

<style>
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
