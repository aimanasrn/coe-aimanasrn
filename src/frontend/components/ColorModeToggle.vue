<script setup lang="ts">
  const colorMode = useColorMode()
  const isDark = computed(() => colorMode.value === 'dark')
  const label = computed(() => (isDark.value ? 'Switch to light mode' : 'Switch to dark mode'))

  function toggle() {
    colorMode.preference = isDark.value ? 'light' : 'dark'
  }
</script>

<template>
  <ClientOnly>
    <UTooltip :text="label">
      <UButton
        :icon="isDark ? 'i-lucide-moon' : 'i-lucide-sun'"
        color="neutral"
        variant="ghost"
        :aria-label="label"
        @click="toggle"
      />
    </UTooltip>
    <template #fallback>
      <div class="size-8" />
    </template>
  </ClientOnly>
</template>
