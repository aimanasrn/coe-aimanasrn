<script setup lang="ts">
  import CountUp from '~/components/CountUp.vue'
  import type { DashboardData } from '~/types/catalog'
  import { formatMoney } from '~/utils/catalog'

  const props = defineProps<{ summary: DashboardData['summary'] }>()

  const stats = computed(() => [
    {
      label: 'Total products',
      value: props.summary.total_products,
      hint: 'Products in your catalog',
      icon: 'i-lucide-package'
    },
    {
      label: 'Categories',
      value: props.summary.total_categories,
      hint: 'Catalog groups',
      icon: 'i-lucide-tags'
    },
    {
      label: 'Units in stock',
      value: props.summary.stock_units,
      hint: 'Available units',
      icon: 'i-lucide-boxes'
    },
    {
      label: 'Stock value',
      value: Number(props.summary.stock_value),
      hint: 'At listed prices',
      icon: 'i-lucide-wallet',
      format: formatMoney
    }
  ])
</script>

<template>
  <dl class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <UCard
      v-for="stat in stats"
      :key="stat.label"
    >
      <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
          <dt class="text-sm text-muted">{{ stat.label }}</dt>
          <dd class="mt-2 text-2xl font-bold tracking-tight text-highlighted wrap-anywhere">
            <CountUp
              :value="stat.value"
              :format="stat.format"
            />
          </dd>
          <p class="mt-1 text-xs text-dimmed">{{ stat.hint }}</p>
        </div>
        <span
          class="grid size-10 flex-none place-items-center rounded-lg bg-primary/10 text-primary"
        >
          <UIcon
            :name="stat.icon"
            class="size-5"
            aria-hidden="true"
          />
        </span>
      </div>
    </UCard>
  </dl>
</template>
