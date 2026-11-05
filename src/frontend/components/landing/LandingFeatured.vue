<script setup lang="ts">
  import type { AuthUser } from '~/types/auth'
  import type { LandingProduct } from '~/types/landing'
  import LandingSectionHeading from '~/components/landing/LandingSectionHeading.vue'
  import { formatMoney } from '~/utils/catalog'

  const props = defineProps<{
    products: LandingProduct[]
    user: AuthUser | null
    browseTo: string
  }>()

  function productLink(id: number) {
    if (props.user?.role === 'admin') return `/products/${id}`
    if (props.user) return `/shop/${id}`
    return { path: '/login', query: { redirect: `/shop/${id}` } }
  }
</script>

<template>
  <section
    id="featured"
    aria-labelledby="featured-title"
    class="scroll-mt-20 bg-muted/60 py-24"
  >
    <UContainer>
      <LandingSectionHeading
        id="featured-title"
        eyebrow="Featured"
        title="Fresh on the shelf"
        description="A few favourites that are in stock right now."
      />

      <ul
        v-if="products.length"
        class="grid grid-cols-2 gap-4 sm:gap-6 lg:grid-cols-4"
      >
        <li
          v-for="(product, index) in products"
          :key="product.id"
          v-reveal="(index % 4) * 80"
        >
          <NuxtLink
            :to="productLink(product.id)"
            class="landing-lift group flex h-full flex-col overflow-hidden rounded-2xl border border-default bg-default"
          >
            <div class="relative aspect-square overflow-hidden bg-elevated">
              <img
                v-if="product.image_url"
                :src="product.image_url"
                :alt="product.name"
                loading="lazy"
                class="size-full object-cover transition-transform duration-500 group-hover:scale-105 motion-reduce:transition-none"
              />
              <div
                v-else
                class="grid size-full place-items-center bg-linear-to-br from-primary/10 to-secondary/10"
              >
                <UIcon
                  name="i-lucide-book-open"
                  class="size-12 text-primary/50"
                  aria-hidden="true"
                />
              </div>
            </div>
            <div class="flex flex-1 flex-col gap-1 p-4">
              <p class="text-xs font-semibold text-primary">
                {{ product.category || 'Study essentials' }}
              </p>
              <h3 class="line-clamp-2 font-semibold text-highlighted">{{ product.name }}</h3>
              <div class="mt-auto flex items-center justify-between gap-2 pt-3">
                <span class="font-extrabold text-highlighted">{{
                  formatMoney(product.price)
                }}</span>
                <span
                  class="grid size-8 place-items-center rounded-full bg-primary/10 text-primary transition-colors group-hover:bg-primary group-hover:text-inverted"
                  aria-hidden="true"
                >
                  <UIcon
                    name="i-lucide-arrow-up-right"
                    class="size-4"
                  />
                </span>
              </div>
            </div>
          </NuxtLink>
        </li>
      </ul>
      <p
        v-else
        class="text-center text-muted"
      >
        New products are on their way. Check back soon.
      </p>

      <div
        v-reveal
        class="mt-12 flex justify-center"
      >
        <UButton
          :to="browseTo"
          :label="user ? 'See the full range' : 'Sign up to see the full range'"
          trailing-icon="i-lucide-arrow-right"
          color="neutral"
          variant="outline"
          size="lg"
        />
      </div>
    </UContainer>
  </section>
</template>
