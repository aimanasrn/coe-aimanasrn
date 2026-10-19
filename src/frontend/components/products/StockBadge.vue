<script setup lang="ts">
  const props = defineProps<{ quantity: number; threshold: number }>()
  const state = computed(() =>
    props.quantity === 0 ? 'out' : props.quantity <= props.threshold ? 'low' : 'in'
  )
  const labels = { in: 'In stock', low: 'Low stock', out: 'Out of stock' }
</script>

<template>
  <span
    class="inline-flex items-center gap-1.5 rounded-[20px] px-2.5 py-[5px] text-sm leading-[1.5] font-medium whitespace-nowrap before:size-2 before:rounded-full before:bg-current"
    :class="{
      'bg-[#eaf6ed] text-[#258448]': state === 'in',
      'bg-[#fff3dc] text-[#8d610b]': state === 'low',
      'bg-[#ffeded] text-[#b83d3d]': state === 'out'
    }"
  >
    {{ labels[state] }}
  </span>
</template>
