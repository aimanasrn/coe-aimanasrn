<script setup lang="ts">
  const { isLoading } = useLoadingIndicator({ throttle: 0, hideDelay: 150 })
</script>

<template>
  <div
    v-if="isLoading"
    class="fixed inset-0 z-80 grid place-items-center bg-white/90 backdrop-blur-xs"
  >
    <StudyMartLoader label="Loading page" />
  </div>
</template>
