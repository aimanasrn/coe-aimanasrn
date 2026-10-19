<script setup lang="ts">
  import CartLine from '~/components/shop/CartLine.vue'
  definePageMeta({ layout: 'shop', colorMode: 'light' })
  useHead({ title: 'Your cart · StudyMart' })
  const { cart, busy, error, refresh, clear } = useCart()
  const { user } = useAuth()
  await useAsyncData('cart-page-' + user.value?.id, refresh)
</script>
<template>
  <section>
    <header class="shop-page-heading">
      <div>
        <h1>Your cart</h1>
        <p>A few good things for your next study session.</p>
      </div>
      <NuxtLink
        to="/shop"
        class="shop-back"
        ><UIcon
          name="i-lucide-arrow-left"
          aria-hidden="true"
        />Continue shopping</NuxtLink
      >
    </header>
    <div
      v-if="error"
      class="shop-empty"
      role="alert"
    >
      <h2>{{ error }}</h2>
      <button
        class="catalog-button catalog-button--primary"
        @click="refresh()"
      >
        Try again
      </button>
    </div>
    <div
      v-else-if="!cart"
      class="shop-empty"
    >
      <StudyMartLoader label="Loading your cart" />
    </div>
    <div
      v-else-if="!cart.items.length"
      class="shop-empty cart-empty"
    >
      <div class="cart-empty__icon">
        <UIcon
          name="i-lucide-shopping-cart"
          class="size-12"
          aria-hidden="true"
        />
      </div>
      <h2>Your cart is waiting for its first find.</h2>
      <p>Explore books and stationery to get started.</p>
      <NuxtLink
        to="/shop"
        class="catalog-button catalog-button--primary"
        >Explore the shop<UIcon
          name="i-lucide-arrow-right"
          aria-hidden="true"
      /></NuxtLink>
    </div>
    <div
      v-else
      class="cart-layout"
      :aria-busy="busy"
    >
      <div class="cart-items">
        <div class="cart-items__header">
          <p>{{ cart.item_count }} {{ cart.item_count === 1 ? 'item' : 'items' }}</p>
          <button
            type="button"
            class="cart-remove"
            :disabled="busy"
            @click="clear()"
          >
            Clear cart
          </button>
        </div>
        <CartLine
          v-for="item in cart.items"
          :key="item.product.id"
          :item="item"
        />
      </div>
      <aside
        class="cart-summary"
        aria-labelledby="cart-summary-title"
      >
        <h2 id="cart-summary-title">Cart summary</h2>
        <div>
          <span>Subtotal</span><strong>RM {{ Number(cart.subtotal).toFixed(2) }}</strong>
        </div>
        <p class="cart-summary__note">
          Your cart is saved to your account. Prices and availability are checked when you make
          changes. Items are not reserved.
        </p>
        <NuxtLink
          to="/shop"
          class="catalog-button catalog-button--primary"
          >Keep exploring<UIcon
            name="i-lucide-arrow-right"
            aria-hidden="true"
        /></NuxtLink>
      </aside>
    </div>
  </section>
</template>
