<script setup lang="ts">
import { cn } from '@/lib/utils';
import type { HTMLAttributes } from 'vue';
import Separator from '../separator/Separator.vue';

const props = defineProps<{
    class?: HTMLAttributes['class'];
}>();
</script>

<template>
    <div data-sidebar="header" :class="cn('flex flex-col gap-2 p-2', props.class)">
        <slot />
    </div>
    <separator/>
</template>
