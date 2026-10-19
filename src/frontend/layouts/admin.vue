<script setup lang="ts">
  import Sidebar from '~/components/admin/Sidebar.vue'
</script>

<template>
  <div class="workspace">
    <Sidebar />
    <main class="workspace-main">
      <slot />
    </main>
  </div>
</template>
