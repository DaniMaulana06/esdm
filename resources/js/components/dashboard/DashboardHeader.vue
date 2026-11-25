<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Printer } from 'lucide-vue-next';

defineProps<{
    showPrint: boolean;
}>();

const emit = defineEmits<{ (e: 'print'): void }>();
</script>

<template>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">Dashboard</h2>

            <p class="mt-1 text-sm text-gray-500">Ringkasan produksi dan pelaporan harian minyak bumi.</p>
        </div>

        <Button v-if="showPrint" type="button" variant="submit" @click="emit('print')">
            <Printer class="mr-2 h-4 w-4" />
            Cetak Laporan
        </Button>
    </div>
</template>
