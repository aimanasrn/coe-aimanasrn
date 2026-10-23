<script setup lang="ts">
  import CartLine from '~/components/shop/CartLine.vue'
  definePageMeta({ layout: 'shop', colorMode: 'light' })
  useHead({ title: 'Your cart · StudyMart' })
  const { cart, busy, loading, error, refresh, clear } = useCart()

  onBeforeMount(() => {
    // The shop layout starts the first load; refresh later visits to the cart.
    if (!loading.value) void refresh()
  })
</script>
<template>
  <section>
    <header class="flex items-end justify-between flex-wrap gap-5 mb-8">
      <div>
        <h1
          class="
            text-[36px] leading-[1.2] font-[650] tracking-[-1px] text-heading wrap-anywhere
            max-[761px]:text-[30px]
          "
        >
          Your cart
        </h1>
        <p class="mt-3 text-base leading-[1.6] text-muted">
          A few good things for your next study session.
        </p>
      </div>
      <NuxtLink
        to="/shop"
        class="inline-flex items-center gap-2 text-sm text-primary font-semibold"
        ><UIcon
          class="w-4.5 h-4.5"
          name="i-lucide-arrow-left"
          aria-hidden="true"
        />Continue shopping</NuxtLink
      >
    </header>
    <div
      v-if="error"
      class="
        flex min-h-87.5 flex-col items-center justify-center text-center gap-4.5 py-10 px-4
        [&_p]:text-muted [&_p]:leading-[1.7]
      "
      role="alert"
    >
      <h2 class="text-[24px] leading-[1.4] font-semibold">{{ error }}</h2>
      <AppButton @click="refresh()"> Try again </AppButton>
    </div>
    <div
      v-else-if="loading || !cart"
      class="
        flex min-h-87.5 flex-col items-center justify-center text-center gap-4.5 py-10 px-4
        [&_h2]:text-[24px] [&_h2]:leading-[1.4] [&_h2]:font-semibold [&_p]:text-muted
        [&_p]:leading-[1.7]
      "
    >
      <StudyMartLoader label="Loading your cart" />
    </div>
    <div
      v-else-if="!cart.items.length"
      class="flex flex-col items-center justify-center text-center gap-4.5 py-10 px-4 min-h-115"
    >
      <div class="w-26 h-26 grid place-items-center rounded-full bg-secondary text-primary mb-2">
        <UIcon
          name="i-lucide-shopping-cart"
          class="size-12"
          aria-hidden="true"
        />
      </div>
      <h2 class="text-[24px] leading-[1.4] font-semibold">
        Your cart is waiting for its first find.
      </h2>
      <p class="text-muted leading-[1.7]">Explore books and stationery to get started.</p>
      <AppButton to="/shop"
        >Explore the shop<UIcon
          name="i-lucide-arrow-right"
          aria-hidden="true"
      /></AppButton>
    </div>
    <div
      v-else
      class="
        grid grid-cols-[minmax(0,_1fr)_340px] gap-8 items-start
        max-[1101px]:grid-cols-[minmax(0,_1fr)_300px] max-[1101px]:gap-5 max-[761px]:grid-cols-1
      "
      :aria-busy="busy"
    >
      <div
        class="bg-white border border-border rounded-2xl py-0 px-6 max-[481px]:py-0 max-[481px]:px-4"
      >
        <div class="flex items-center justify-between gap-4 py-5 px-0 text-[14px]">
          <p>{{ cart.item_count }} {{ cart.item_count === 1 ? 'item' : 'items' }}</p>
          <button
            type="button"
            class="text-muted text-xs underline underline-offset-3 cursor-pointer hover:text-[#a03939]"
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
        class="bg-white border border-border rounded-2xl p-6.5 max-[761px]:max-w-none"
        aria-labelledby="cart-summary-title"
      >
        <h2
          class="text-[21px] font-semibold mb-6.5"
          id="cart-summary-title"
        >
          Cart summary
        </h2>
        <div class="flex justify-between gap-5 text-base">
          <span>Subtotal</span
          ><strong class="text-heading text-lg whitespace-nowrap"
            >RM {{ Number(cart.subtotal).toFixed(2) }}</strong
          >
        </div>
        <p class="text-xs leading-[1.8] text-muted border-t border-border my-5.5 mx-0 pt-5">
          Your cart is saved to your account. Prices and availability are checked when you make
          changes. Items are not reserved.
        </p>
        <AppButton
          class="w-full justify-center"
          to="/shop"
          >Keep exploring<UIcon
            name="i-lucide-arrow-right"
            aria-hidden="true"
        /></AppButton>
      </aside>
    </div>
  </section>
</template>
