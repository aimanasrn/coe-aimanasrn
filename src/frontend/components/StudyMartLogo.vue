<script setup lang="ts">
  defineProps<{
    admin?: boolean
    caption?: string
    inverse?: boolean
  }>()
</script>

<template>
  <span
    class="
      inline-flex items-center gap-2.5 font-sans leading-none font-bold tracking-[-0.8px]
      whitespace-nowrap
    "
    :class="inverse ? 'text-[26px] text-white' : 'text-[28px] text-brand-deep'"
  >
    <img
      :src="inverse ? '/studymart-mark-inverse.svg' : '/studymart-mark.svg'"
      alt=""
      width="40"
      height="40"
      class="block size-10 flex-none"
    />
    <span class="flex flex-col gap-1.75">
      <span>StudyMart</span>
      <span
        v-if="admin"
        class="text-[11px] font-normal tracking-[2px] uppercase opacity-75"
      >
        {{ caption || 'Admin' }}
      </span>
    </span>
  </span>
</template>
