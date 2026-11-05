<script setup lang="ts">
  import type { LandingData } from '~/types/landing'
  import { categoryIcon } from '~/utils/categoryIcons'

  const props = defineProps<{ data: LandingData }>()

  const stats = computed(() => [
    { label: 'Study essentials', value: props.data.stats.products },
    { label: 'Categories', value: props.data.stats.categories },
    { label: 'Ready in stock', value: props.data.stats.in_stock }
  ])
  // The list is repeated so the scrolling strip loops without a gap.
  const marquee = computed(() => [...props.data.categories, ...props.data.categories])
</script>

<template>
  <section
    aria-label="StudyMart at a glance"
    class="border-y border-default bg-muted/60"
  >
    <UContainer>
      <dl class="grid grid-cols-3 divide-x divide-default py-10">
        <div
          v-for="(stat, index) in stats"
          :key="stat.label"
          v-reveal="index * 80"
          class="flex flex-col-reverse px-2 py-3 text-center sm:px-4"
        >
          <dt class="mt-1 text-sm text-muted">{{ stat.label }}</dt>
          <dd class="text-3xl font-extrabold tracking-tight text-highlighted sm:text-5xl">
            <CountUp :value="stat.value" />
          </dd>
        </div>
      </dl>
    </UContainer>

    <div
      v-if="data.categories.length"
      class="landing-marquee-track overflow-hidden border-t border-default py-4"
      aria-hidden="true"
    >
      <ul class="landing-marquee gap-3 pr-3">
        <li
          v-for="(category, index) in marquee"
          :key="`${category.id}-${index}`"
          class="flex shrink-0 items-center gap-2 rounded-full border border-default bg-default px-4 py-2 text-sm font-medium text-toned"
        >
          <UIcon
            :name="categoryIcon(category.name)"
            class="size-4 text-primary"
          />
          {{ category.name }}
        </li>
      </ul>
    </div>
  </section>
</template>
