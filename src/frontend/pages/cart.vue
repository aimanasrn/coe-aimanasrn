<script setup lang="ts">
  import CartLine from '~/components/shop/CartLine.vue'
  import { formatMoney } from '~/utils/catalog'

  definePageMeta({ layout: 'shop' })
  useHead({ title: 'Your cart · StudyMart' })
  const { cart, busy, loading, error, refresh, clear } = useCart()

  onBeforeMount(() => {
    // The shop layout starts the first load; refresh later visits to the cart.
    if (!loading.value) void refresh()
  })
</script>

<template>
  <section>
    <PageHeader
      title="Your cart"
      description="A few good things for your next study session."
    >
      <template #actions>
        <UButton
          to="/shop"
          label="Continue shopping"
          icon="i-lucide-arrow-left"
          color="neutral"
          variant="outline"
        />
      </template>
    </PageHeader>

    <EmptyState
      v-if="error"
      role="alert"
      icon="i-lucide-cloud-off"
      :title="error"
    >
      <UButton
        label="Try again"
        icon="i-lucide-refresh-cw"
        @click="refresh()"
      />
    </EmptyState>
    <div
      v-else-if="loading || !cart"
      class="grid min-h-87.5 place-items-center"
    >
      <StudyMartLoader label="Loading your cart" />
    </div>
    <UCard v-else-if="!cart.items.length">
      <EmptyState
        icon="i-lucide-shopping-cart"
        title="Your cart is waiting for its first find."
        description="Explore books and stationery to get started."
      >
        <UButton
          to="/shop"
          label="Explore the shop"
          trailing-icon="i-lucide-arrow-right"
        />
      </EmptyState>
    </UCard>
    <div
      v-else
      class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_340px]"
      :aria-busy="busy"
    >
      <UCard :ui="{ body: 'p-0 sm:p-0' }">
        <template #header>
          <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-highlighted">
              {{ cart.item_count }} {{ cart.item_count === 1 ? 'item' : 'items' }}
            </h2>
            <UButton
              label="Clear cart"
              icon="i-lucide-trash-2"
              color="error"
              variant="ghost"
              size="sm"
              :disabled="busy"
              @click="clear()"
            />
          </div>
        </template>
        <ul class="divide-y divide-default">
          <li
            v-for="item in cart.items"
            :key="item.product.id"
          >
            <CartLine :item="item" />
          </li>
        </ul>
      </UCard>

      <UCard
        as="aside"
        aria-labelledby="cart-summary-title"
        class="lg:sticky lg:top-24"
      >
        <template #header>
          <h2
            id="cart-summary-title"
            class="font-semibold text-highlighted"
          >
            Cart summary
          </h2>
        </template>
        <div class="flex items-baseline justify-between gap-5">
          <span class="text-muted">Subtotal</span>
          <strong class="text-2xl font-bold whitespace-nowrap text-highlighted">
            {{ formatMoney(cart.subtotal) }}
          </strong>
        </div>
        <p class="mt-4 text-xs leading-relaxed text-muted">
          Your cart is saved to your account. Prices and availability are checked when you make
          changes. Items are not reserved.
        </p>
        <template #footer>
          <UButton
            to="/shop"
            label="Keep exploring"
            trailing-icon="i-lucide-arrow-right"
            block
            size="lg"
          />
        </template>
      </UCard>
    </div>
  </section>
</template>
