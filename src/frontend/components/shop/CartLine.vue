<script setup lang="ts">
  import type { CartItem } from '~/types/shop'
  defineProps<{ item: CartItem }>()
  const { update, remove, busy } = useCart()
  const failed = ref(false)
</script>
<template>
  <article class="cart-line">
    <NuxtLink
      :to="'/shop/' + item.product.id"
      class="cart-line__image"
      :aria-label="'View ' + item.product.name"
    >
      <img
        v-if="item.product.image_urls[0] && !failed"
        :src="item.product.image_urls[0]"
        :alt="item.product.name"
        @error="failed = true"
      />
      <UIcon
        v-else
        name="i-lucide-book-open"
        class="size-8 text-primary/40"
        aria-hidden="true"
      />
    </NuxtLink>
    <div class="cart-line__details">
      <p class="text-xs text-muted">{{ item.product.category?.name }}</p>
      <h2>
        <NuxtLink :to="'/shop/' + item.product.id">{{ item.product.name }}</NuxtLink>
      </h2>
      <p class="text-sm text-muted">RM {{ Number(item.product.price).toFixed(2) }} each</p>
      <p
        v-if="!item.available"
        class="mt-2 text-sm text-warning"
        role="status"
      >
        {{
          item.max_quantity
            ? 'Only ' + item.max_quantity + ' available. Please reduce the quantity.'
            : 'Out of stock. Please remove this item.'
        }}
      </p>
      <div class="cart-line__controls">
        <div
          class="cart-stepper"
          :aria-label="'Quantity for ' + item.product.name"
          role="group"
        >
          <button
            type="button"
            :aria-label="'Decrease quantity of ' + item.product.name"
            :disabled="busy || item.quantity <= 1 || !item.max_quantity"
            @click="update(item.product.id, Math.min(item.quantity - 1, item.max_quantity))"
          >
            <UIcon
              name="i-lucide-minus"
              aria-hidden="true"
            />
          </button>
          <span aria-live="polite">{{ item.quantity }}</span>
          <button
            type="button"
            :aria-label="'Increase quantity of ' + item.product.name"
            :disabled="busy || item.quantity >= item.max_quantity"
            @click="update(item.product.id, item.quantity + 1)"
          >
            <UIcon
              name="i-lucide-plus"
              aria-hidden="true"
            />
          </button>
        </div>
        <button
          type="button"
          class="cart-remove"
          :aria-label="'Remove ' + item.product.name + ' from cart'"
          :disabled="busy"
          @click="remove(item.product.id)"
        >
          Remove
        </button>
      </div>
    </div>
    <p class="cart-line__total">RM {{ Number(item.line_total).toFixed(2) }}</p>
  </article>
</template>
