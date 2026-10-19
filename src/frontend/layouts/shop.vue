<script setup lang="ts">
  import ShopNavigation from '~/components/shop/ShopNavigation.vue'
  const { user } = useAuth()
  const { refresh } = useCart()
  await useAsyncData('cart-initial-' + user.value?.id, refresh)
</script>
<template>
  <div class="shop-shell">
    <ShopNavigation />
    <main class="shop-main"><slot /></main>
    <footer class="shop-footer">StudyMart · A little more ready for every day of learning.</footer>
  </div>
</template>
