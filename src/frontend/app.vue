<script setup lang="ts">
import '~/assets/css/notifications.css'
</script>

<template>
  <UApp
    :tooltip="{ delayDuration: 200 }"
    :toaster="{ position: 'top-right', expand: true, duration: 5000 }"
  >
    <NuxtLayout>
      <NuxtPage />
    </NuxtLayout>
  </UApp>
</template>
