<script setup lang="ts">
  import ShopNavigation from '~/components/shop/ShopNavigation.vue'
  import { useAuth } from '~/composables/useAuth'
  import { useCart } from '~/composables/useCart'

  const { user } = useAuth()
  const { refresh } = useCart()
  useLazyAsyncData('cart-initial-' + user.value?.id, refresh, { server: false })
</script>

<template>
  <div class="flex min-h-dvh flex-col bg-muted">
    <ShopNavigation />
    <main class="flex-1">
      <UContainer class="py-8 sm:py-12">
        <slot />
      </UContainer>
    </main>
    <footer class="border-t border-default bg-default">
      <UContainer class="flex flex-wrap items-center justify-between gap-3 py-6 text-sm text-muted">
        <span class="font-semibold text-highlighted">StudyMart</span>
        <span>A little more ready for every day of learning.</span>
      </UContainer>
    </footer>
  </div>
</template>
