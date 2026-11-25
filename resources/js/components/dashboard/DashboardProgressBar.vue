<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        value: number;
        max: number;
        colorClass?: string;
    }>(),
    {
        colorClass: 'bg-gray-800',
    },
);

const percentage = computed(() => (props.max > 0 ? (props.value / props.max) * 100 : 0));
</script>

<template>
    <div class="h-2 rounded-full bg-gray-100">
        <div class="h-2 rounded-full" :class="colorClass" :style="{ width: `${percentage}%` }" />
    </div>
</template>
