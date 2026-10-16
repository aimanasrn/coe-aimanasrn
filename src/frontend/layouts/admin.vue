<script setup lang="ts">
import Sidebar from '~/components/admin/Sidebar.vue'
</script>

<template>
  <div class="min-h-screen bg-surface-muted text-[#37463f] font-sans md:pl-[272px] [&_button:focus-visible]:outline-2 [&_button:focus-visible]:outline-primary [&_button:focus-visible]:outline-offset-4 [&_a:focus-visible]:outline-2 [&_a:focus-visible]:outline-primary [&_a:focus-visible]:outline-offset-4 [&_button:disabled]:opacity-50 [&_button:disabled]:cursor-not-allowed">
    <Sidebar />

    <main class="pt-[52px] pr-7 pb-10 pl-[34px] max-md:px-5 max-md:pt-[88px] max-md:pb-7 min-[1700px]:mx-auto min-[1700px]:max-w-[1500px] min-[1700px]:px-10">
      <slot />
    </main>
  </div>
</template>
