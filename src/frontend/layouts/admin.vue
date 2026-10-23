<script setup lang="ts">
  import Sidebar from '~/components/admin/Sidebar.vue'
</script>

<template>
  <div
    class="
      min-h-svh pl-68 bg-surface-muted text-body text-base
      [&_:is(a,_button):focus-visible]:outline-2 [&_:is(a,_button):focus-visible]:outline-solid
      [&_:is(a,_button):focus-visible]:outline-primary
      [&_:is(a,_button):focus-visible]:outline-offset-4 [&_button:disabled]:opacity-50
      [&_button:disabled]:cursor-not-allowed max-md:pl-0 motion-reduce:[&_*]:scroll-auto
      motion-reduce:[&_*]:duration-0
    "
  >
    <Sidebar />
    <main
      class="
        w-full max-w-410 min-w-0 my-0 mx-auto py-10 px-8 min-[1700px]:pr-10 min-[1700px]:pl-10
        max-md:pt-26 max-md:px-5 max-md:pb-7
      "
    >
      <slot />
    </main>
  </div>
</template>
