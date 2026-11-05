<script setup lang="ts">
  defineProps<{ caption?: string }>()
</script>

<template>
  <span class="inline-flex items-center gap-2.5 leading-none whitespace-nowrap">
    <img
      src="/studymart-mark.svg"
      alt=""
      width="36"
      height="36"
      class="block size-9 flex-none dark:hidden"
    />
    <img
      src="/studymart-mark-inverse.svg"
      alt=""
      width="36"
      height="36"
      class="hidden size-9 flex-none dark:block"
    />
    <span class="flex flex-col gap-1">
      <span class="text-xl font-extrabold tracking-tight text-highlighted">StudyMart</span>
      <span
        v-if="caption"
        class="text-[11px] font-semibold tracking-[0.18em] text-muted uppercase"
      >
        {{ caption }}
      </span>
    </span>
  </span>
</template>
