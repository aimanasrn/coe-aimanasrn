<script setup lang="ts">
  import type { CartItem } from '~/types/shop'
  import { formatMoney } from '~/utils/catalog'

  defineProps<{ item: CartItem }>()
  const { update, remove, busy } = useCart()
  const failed = ref(false)
</script>

<template>
  <article class="flex gap-4 p-4 sm:p-6">
    <NuxtLink
      :to="'/shop/' + item.product.id"
      class="grid size-20 flex-none place-items-center overflow-hidden rounded-lg bg-elevated sm:size-24"
      :aria-label="'View ' + item.product.name"
      tabindex="-1"
    >
      <img
        v-if="item.product.image_urls[0] && !failed"
        class="size-full object-cover"
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

    <div class="flex min-w-0 flex-1 flex-col gap-3 sm:flex-row sm:justify-between">
      <div class="min-w-0">
        <p class="text-xs font-medium text-primary">{{ item.product.category?.name }}</p>
        <h3 class="font-semibold text-highlighted wrap-anywhere">
          <NuxtLink
            :to="'/shop/' + item.product.id"
            class="hover:text-primary"
          >
            {{ item.product.name }}
          </NuxtLink>
        </h3>
        <p class="text-sm text-muted">{{ formatMoney(item.product.price) }} each</p>
        <UAlert
          v-if="!item.available"
          class="mt-2"
          color="warning"
          variant="subtle"
          icon="i-lucide-triangle-alert"
          role="status"
          :description="
            item.max_quantity
              ? 'Only ' + item.max_quantity + ' available. Please reduce the quantity.'
              : 'Out of stock. Please remove this item.'
          "
        />
        <div class="mt-3 flex flex-wrap items-center gap-3">
          <UButtonGroup
            role="group"
            :aria-label="'Quantity for ' + item.product.name"
          >
            <UButton
              icon="i-lucide-minus"
              color="neutral"
              variant="outline"
              size="sm"
              :aria-label="'Decrease quantity of ' + item.product.name"
              :disabled="busy || item.quantity <= 1 || !item.max_quantity"
              @click="update(item.product.id, Math.min(item.quantity - 1, item.max_quantity))"
            />
            <span
              class="grid min-w-10 place-items-center border-y border-accented px-2 text-sm font-semibold text-highlighted"
              aria-live="polite"
            >
              {{ item.quantity }}
            </span>
            <UButton
              icon="i-lucide-plus"
              color="neutral"
              variant="outline"
              size="sm"
              :aria-label="'Increase quantity of ' + item.product.name"
              :disabled="busy || item.quantity >= item.max_quantity"
              @click="update(item.product.id, item.quantity + 1)"
            />
          </UButtonGroup>
          <UButton
            label="Remove"
            color="neutral"
            variant="link"
            size="sm"
            :aria-label="'Remove ' + item.product.name + ' from cart'"
            :disabled="busy"
            @click="remove(item.product.id)"
          />
        </div>
      </div>
      <p class="font-bold whitespace-nowrap text-highlighted sm:text-right">
        {{ formatMoney(item.line_total) }}
      </p>
    </div>
  </article>
</template>
