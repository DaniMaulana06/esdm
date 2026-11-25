<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { getLocalTimeZone, parseDate, today } from '@internationalized/date';
import { RotateCw, Search } from 'lucide-vue-next';
import type { DateValue } from 'reka-ui';
import { computed, reactive, shallowRef } from 'vue';
import DashboardDatePicker from './DashboardDatePicker.vue';
import type { Bku, DashboardFilters, FilterPayload, Kontrak } from './types';

const props = defineProps<{
    filters: DashboardFilters;
    bkus: Bku[];
    kontraks: Kontrak[];
    isOperator: boolean;
}>();

const emit = defineEmits<{
    (e: 'apply', payload: FilterPayload): void;
    (e: 'reset'): void;
}>();

const defaultPlaceholder = today(getLocalTimeZone());

const startDate = shallowRef<DateValue | undefined>(props.filters.start_date ? parseDate(props.filters.start_date) : today(getLocalTimeZone()));

const endDate = shallowRef<DateValue | undefined>(props.filters.end_date ? parseDate(props.filters.end_date) : today(getLocalTimeZone()));

const form = reactive({
    start_date: props.filters.start_date,
    end_date: props.filters.end_date,
    bku_id: props.filters.bku_id as number | null,
    kontrak_id: props.filters.kontrak_id as number | null,
});

const updateStartDate = (value: DateValue) => {
    startDate.value = value;
    form.start_date = value.toString();
};

const updateEndDate = (value: DateValue) => {
    endDate.value = value;
    form.end_date = value.toString();
};

const kontrakModel = computed({
    get: () => (form.kontrak_id !== null ? String(form.kontrak_id) : 'all'),
    set: (value: string) => {
        form.kontrak_id = value === 'all' ? null : Number(value);
    },
});

const bkuModel = computed({
    get: () => (form.bku_id !== null ? String(form.bku_id) : 'all'),
    set: (value: string) => {
        form.bku_id = value === 'all' ? null : Number(value);
    },
});

const applyFilter = () => {
    emit('apply', {
        start_date: form.start_date,
        end_date: form.end_date,
        bku_id: form.bku_id || undefined,
        kontrak_id: form.kontrak_id || undefined,
    });
};

const resetFilter = () => {
    form.start_date = props.filters.start_date;
    form.end_date = props.filters.end_date;
    form.bku_id = null;
    form.kontrak_id = null;

    emit('reset');
};
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>Filter Dashboard</CardTitle>
        </CardHeader>

        <CardContent>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <DashboardDatePicker
                    label="Dari Tanggal"
                    :model-value="startDate"
                    :default-placeholder="defaultPlaceholder"
                    @update:model-value="updateStartDate"
                />

                <DashboardDatePicker
                    label="Sampai Tanggal"
                    :model-value="endDate"
                    :default-placeholder="defaultPlaceholder"
                    @update:model-value="updateEndDate"
                />

                <div v-if="!isOperator">
                    <Label class="mb-2 block text-sm font-medium">BKU</Label>

                    <Select v-model="bkuModel">
                        <SelectTrigger class="w-[280px]">
                            <SelectValue placeholder="Pilih BKU" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectGroup>
                                <SelectItem value="all">Semua BKU</SelectItem>
                                <SelectItem v-for="bku in bkus" :key="bku.id" :value="String(bku.id)">
                                    {{ bku.nama }}
                                </SelectItem>
                            </SelectGroup>
                        </SelectContent>
                    </Select>
                </div>

                <div>
                    <Label class="mb-2 block text-sm font-medium">Kontrak</Label>

                    <Select v-model="kontrakModel">
                        <SelectTrigger class="w-[280px]">
                            <SelectValue placeholder="Pilih Kontrak" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectGroup>
                                <SelectItem value="all">Semua Kontrak</SelectItem>
                                <SelectItem v-for="kontrak in kontraks" :key="kontrak.id" :value="String(kontrak.id)">
                                    {{ kontrak.nama }}
                                </SelectItem>
                            </SelectGroup>
                        </SelectContent>
                    </Select>
                </div>
            </div>

            <div class="mt-4 flex justify-end gap-2">
                <Button type="button" variant="outline" @click="resetFilter">
                    <RotateCw v-if="!isOperator" />
                    Reset
                </Button>

                <Button type="button" :variant="isOperator ? 'default' : 'submit'" @click="applyFilter">
                    <Search v-if="!isOperator" />
                    Terapkan Filter
                </Button>
            </div>
        </CardContent>
    </Card>
</template>
