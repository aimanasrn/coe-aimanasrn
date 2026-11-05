<script setup lang="ts">
  import type { LandingCategory } from '~/types/landing'
  import LandingSectionHeading from '~/components/landing/LandingSectionHeading.vue'
  import { categoryIcon } from '~/utils/categoryIcons'

  defineProps<{ categories: LandingCategory[]; browseTo: string }>()
</script>

<template>
  <section
    id="categories"
    aria-labelledby="categories-title"
    class="scroll-mt-20 py-24"
  >
    <UContainer>
      <LandingSectionHeading
        id="categories-title"
        eyebrow="Shop by category"
        title="Find the right tool for every subject"
        description="From the first line of notes to the final art project, everything is grouped so you can find it fast."
      />

      <ul class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        <li
          v-for="(category, index) in categories"
          :key="category.id"
          v-reveal="(index % 4) * 70"
        >
          <NuxtLink
            :to="browseTo"
            class="landing-lift group flex h-full flex-col rounded-2xl border border-default bg-default p-6 hover:border-primary/40"
          >
            <div class="flex items-center justify-between">
              <span
                class="grid size-12 place-items-center rounded-xl bg-primary/10 text-primary transition-colors duration-300 group-hover:bg-primary group-hover:text-inverted"
              >
                <UIcon
                  :name="categoryIcon(category.name)"
                  class="size-6"
                  aria-hidden="true"
                />
              </span>
              <UBadge
                :label="`${category.products_count} ${category.products_count === 1 ? 'item' : 'items'}`"
                color="neutral"
                variant="subtle"
                class="rounded-full"
              />
            </div>
            <h3 class="mt-5 font-bold text-highlighted">{{ category.name }}</h3>
            <p class="mt-2 line-clamp-3 text-sm text-muted">{{ category.description }}</p>
            <span
              class="mt-auto flex items-center gap-1 pt-5 text-sm font-semibold text-primary"
              aria-hidden="true"
            >
              Explore
              <UIcon
                name="i-lucide-arrow-right"
                class="size-4 transition-transform duration-300 group-hover:translate-x-1"
              />
            </span>
          </NuxtLink>
        </li>
      </ul>
    </UContainer>
  </section>
</template>
