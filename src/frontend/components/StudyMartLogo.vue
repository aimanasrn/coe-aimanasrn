<script setup lang="ts">
defineProps<{
  admin?: boolean
}>()
</script>

<template>
  <span
    :class="
      admin
        ? 'inline-flex flex-col items-start whitespace-nowrap'
        : 'inline-flex items-center whitespace-nowrap'
    "
  >
    <img
      src="/studymart-logo.png"
      alt="StudyMart"
      width="2172"
      height="724"
      :class="
        admin
          ? 'h-auto w-[224px] shrink-0'
          : 'h-auto w-[156px] shrink-0 sm:w-[180px]'
      "
    />

    <span
      v-if="admin"
      class="-mt-2 ml-[55px] text-[10px] font-semibold uppercase tracking-[0.18em] text-muted"
    >
      Admin
    </span>
  </span>
</template>
