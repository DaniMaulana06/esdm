
<script setup lang="ts">
import { computed, provide, useSlots } from "vue"
import type { ChartConfig } from "./interface"

const props = defineProps<{
  config: ChartConfig
}>()

const slots = useSlots()

const config = computed(() => props.config)

provide("chartConfig", config)
</script>

<template>
  <div
    class="flex w-full flex-col justify-center text-xs"
    :style="{
      '--vis-tooltip-background-color': 'transparent',
      '--vis-tooltip-border-color': 'transparent',
    }"
  >
    <slot />
  </div>
</template>
