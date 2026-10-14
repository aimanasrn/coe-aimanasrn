<script setup lang="ts">
import Sidebar from '~/components/admin/Sidebar.vue'
import '~/assets/css/admin.css'
import '~/assets/css/products.css'
</script>

<template>
  <div class="sm-admin-shell">
    <Sidebar />

    <main class="sm-admin-main">
      <slot />
    </main>
  </div>
</template>
