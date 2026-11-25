<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import { Label } from '@/components/ui/label';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { cn } from '@/lib/utils';
import { CalendarIcon } from '@lucide/vue';
import type { DateValue } from 'reka-ui';

defineProps<{
    modelValue: DateValue | undefined;
    label: string;
    defaultPlaceholder: DateValue;
}>();

const emit = defineEmits<{ (e: 'update:modelValue', value: DateValue): void }>();

const handleUpdate = (value: DateValue | undefined) => {
    if (!value) {
        return;
    }

    emit('update:modelValue', value);
};
</script>

<template>
    <div>
        <Label class="mb-2 block text-sm font-medium">{{ label }}</Label>

        <Popover>
            <PopoverTrigger as-child>
                <Button variant="outline" :class="cn('w-[280px] justify-start text-left font-normal', !modelValue && 'text-muted-foreground')">
                    <CalendarIcon class="mr-2 h-4 w-4" />
                    {{ modelValue ? modelValue.toString() : 'Pilih tanggal' }}
                </Button>
            </PopoverTrigger>
            <PopoverContent class="w-auto p-0">
                <Calendar
                    :model-value="modelValue"
                    :initial-focus="true"
                    :default-placeholder="defaultPlaceholder"
                    layout="month-and-year"
                    @update:model-value="handleUpdate"
                />
            </PopoverContent>
        </Popover>
    </div>
</template>
