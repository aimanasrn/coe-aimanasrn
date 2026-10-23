<script setup lang="ts">
  import ShopNavigation from '~/components/shop/ShopNavigation.vue'
  import { useAuth } from '~/composables/useAuth'
  import { useCart } from '~/composables/useCart'
  const { user } = useAuth()
  const { refresh } = useCart()
  useLazyAsyncData('cart-initial-' + user.value?.id, refresh, { server: false })
</script>
<template>
  <div
    class="
      min-h-dvh flex flex-col bg-surface-muted text-body [&_button:focus-visible]:outline-2
      [&_button:focus-visible]:outline-solid [&_button:focus-visible]:outline-primary
      [&_button:focus-visible]:outline-offset-4 [&_a:focus-visible]:outline-2
      [&_a:focus-visible]:outline-solid [&_a:focus-visible]:outline-primary
      [&_a:focus-visible]:outline-offset-4 [&_button:disabled]:cursor-not-allowed
      [&_button:disabled]:opacity-50
    "
  >
    <ShopNavigation />
    <main
      class="
        w-full max-w-320 my-0 mx-auto pt-12 px-8 pb-16 flex-1 min-w-0 min-[1600px]:max-w-350
        max-[761px]:pt-8 max-[761px]:px-5 max-[761px]:pb-12
      "
    >
      <slot />
    </main>
    <footer
      class="text-center border-t border-border p-6 text-xs text-muted max-[761px]:text-[12px]"
    >
      StudyMart · A little more ready for every day of learning.
    </footer>
  </div>
</template>
