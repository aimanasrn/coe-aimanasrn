<script setup lang="ts">
  import type { CartItem } from '~/types/shop'
  defineProps<{ item: CartItem }>()
  const { update, remove, busy } = useCart()
  const failed = ref(false)
</script>
<template>
  <article
    class="
      grid grid-cols-[100px_minmax(0,_1fr)_auto] gap-5.5 py-6 px-0 border-t border-border
      max-[1101px]:grid-cols-[80px_minmax(0,_1fr)] max-[1101px]:gap-4
      max-[481px]:grid-cols-[72px_minmax(0,_1fr)] max-[481px]:gap-3.5
    "
  >
    <NuxtLink
      :to="'/shop/' + item.product.id"
      class="
        w-25 h-30 bg-[#edf2ef] rounded-[10px] overflow-hidden grid place-items-center
        max-[1101px]:w-20 max-[1101px]:h-25 max-[481px]:w-18 max-[481px]:h-23
      "
      :aria-label="'View ' + item.product.name"
    >
      <img
        class="w-full h-full object-cover"
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
    <div>
      <p class="text-xs text-muted">{{ item.product.category?.name }}</p>
      <h2
        class="text-[17px] leading-normal mt-1 mx-0 mb-2 font-semibold wrap-anywhere max-[481px]:text-base"
      >
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
      <div class="mt-4 flex items-center gap-5 max-[481px]:gap-3 max-[481px]:flex-wrap">
        <div
          class="flex items-center border border-border rounded-lg overflow-hidden"
          :aria-label="'Quantity for ' + item.product.name"
          role="group"
        >
          <button
            class="
              w-9 h-9 grid place-items-center cursor-pointer bg-white hover:bg-secondary
              disabled:opacity-35 disabled:cursor-not-allowed
            "
            type="button"
            :aria-label="'Decrease quantity of ' + item.product.name"
            :disabled="busy || item.quantity <= 1 || !item.max_quantity"
            @click="update(item.product.id, Math.min(item.quantity - 1, item.max_quantity))"
          >
            <UIcon
              class="size-4"
              name="i-lucide-minus"
              aria-hidden="true"
            />
          </button>
          <span
            class="min-w-8 text-center text-[14px]"
            aria-live="polite"
            >{{ item.quantity }}</span
          >
          <button
            class="
              w-9 h-9 grid place-items-center cursor-pointer bg-white hover:bg-secondary
              disabled:opacity-35 disabled:cursor-not-allowed
            "
            type="button"
            :aria-label="'Increase quantity of ' + item.product.name"
            :disabled="busy || item.quantity >= item.max_quantity"
            @click="update(item.product.id, item.quantity + 1)"
          >
            <UIcon
              class="size-4"
              name="i-lucide-plus"
              aria-hidden="true"
            />
          </button>
        </div>
        <button
          type="button"
          class="text-muted text-xs underline underline-offset-3 cursor-pointer hover:text-[#a03939]"
          :aria-label="'Remove ' + item.product.name + ' from cart'"
          :disabled="busy"
          @click="remove(item.product.id)"
        >
          Remove
        </button>
      </div>
    </div>
    <p
      class="
        text-[17px] font-[650] text-heading pt-5.5 whitespace-nowrap max-[1101px]:col-start-2
        max-[1101px]:pt-0 max-[481px]:text-base
      "
    >
      RM {{ Number(item.line_total).toFixed(2) }}
    </p>
  </article>
</template>
