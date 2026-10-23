<script setup lang="ts">
  import CountUp from '~/components/CountUp.vue'
  import type { DashboardData } from '~/types/catalog'
  import { formatMoney } from '~/utils/catalog'
  defineProps<{ summary: DashboardData['summary'] }>()
</script>

<template>
  <dl
    class="
      grid grid-cols-4 mb-6.5 py-6.5 px-0 border border-border rounded-xl bg-white
      [&_>_div:last-child]:border-0 max-md:grid-cols-2 max-md:p-0
      max-md:[&_>_div:nth-child(2)]:border-r-0 max-md:[&_>_div:nth-child(-n_+_2)]:border-b
      max-md:[&_>_div:nth-child(-n_+_2)]:border-border max-md:[&_>_div:last-child_dd]:text-[24px]
      md:max-[1101px]:grid-cols-2 md:max-[1101px]:p-0
      md:max-[1101px]:[&_>_div:nth-child(2)]:border-r-0
      md:max-[1101px]:[&_>_div:nth-child(-n_+_2)]:border-b
      md:max-[1101px]:[&_>_div:nth-child(-n_+_2)]:border-border
    "
  >
    <div
      class="
        min-w-0 py-0 px-6 border-r border-border max-[1481px]:py-0 max-[1481px]:px-5 max-md:py-5
        max-md:px-4 md:max-[1101px]:p-6
      "
    >
      <dt class="text-muted text-[14px] leading-normal">Total products</dt>
      <dd
        class="
          mt-3 mx-0 mb-[9px] text-heading text-[clamp(28px,_2.2vw,_36px)] font-semibold leading-[1.15]
          tracking-[-0.8px] tabular-nums wrap-anywhere max-md:mt-2.5 max-md:mx-0 max-md:mb-2
          max-md:text-[28px]
        "
      >
        <CountUp :value="summary.total_products" />
      </dd>
      <p class="text-muted text-xs leading-normal">Products in your catalog</p>
    </div>
    <div
      class="
        min-w-0 py-0 px-6 border-r border-border max-[1481px]:py-0 max-[1481px]:px-5 max-md:py-5
        max-md:px-4 md:max-[1101px]:p-6
      "
    >
      <dt class="text-muted text-[14px] leading-normal">Categories</dt>
      <dd
        class="
          mt-3 mx-0 mb-[9px] text-heading text-[clamp(28px,_2.2vw,_36px)] font-semibold leading-[1.15]
          tracking-[-0.8px] tabular-nums wrap-anywhere max-md:mt-2.5 max-md:mx-0 max-md:mb-2
          max-md:text-[28px]
        "
      >
        <CountUp :value="summary.total_categories" />
      </dd>
      <p class="text-muted text-xs leading-normal">Catalog groups</p>
    </div>
    <div
      class="
        min-w-0 py-0 px-6 border-r border-border max-[1481px]:py-0 max-[1481px]:px-5 max-md:py-5
        max-md:px-4 md:max-[1101px]:p-6
      "
    >
      <dt class="text-muted text-[14px] leading-normal">Units in stock</dt>
      <dd
        class="
          mt-3 mx-0 mb-[9px] text-heading text-[clamp(28px,_2.2vw,_36px)] font-semibold leading-[1.15]
          tracking-[-0.8px] tabular-nums wrap-anywhere max-md:mt-2.5 max-md:mx-0 max-md:mb-2
          max-md:text-[28px]
        "
      >
        <CountUp :value="summary.stock_units" />
      </dd>
      <p class="text-muted text-xs leading-normal">Available units</p>
    </div>
    <div
      class="
        min-w-0 py-0 px-6 border-r border-border max-[1481px]:py-0 max-[1481px]:px-5 max-md:py-5
        max-md:px-4 md:max-[1101px]:p-6
      "
    >
      <dt class="text-muted text-[14px] leading-normal">Stock value</dt>
      <dd
        class="
          mt-3 mx-0 mb-[9px] text-heading text-[clamp(28px,_2.2vw,_36px)] font-semibold leading-[1.15]
          tracking-[-0.8px] tabular-nums wrap-anywhere max-md:mt-2.5 max-md:mx-0 max-md:mb-2
          max-md:text-[28px]
        "
      >
        <CountUp
          :value="Number(summary.stock_value)"
          :format="formatMoney"
        />
      </dd>
      <p class="text-muted text-xs leading-normal">At listed prices</p>
    </div>
  </dl>
</template>
