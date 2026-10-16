<script setup lang="ts">
import CountUp from '~/components/CountUp.vue'
import type { DashboardData } from '~/types/catalog'
import { formatMoney } from '~/utils/catalog'
defineProps<{ summary: DashboardData['summary'] }>()
</script>

<template>
  <dl class="mb-[26px] grid grid-cols-4 rounded-lg border border-[#e0e6e2] bg-white py-7 max-md:grid-cols-2 max-md:py-0">
    <div class="border-r border-[#e4e9e6] px-[38px] last:border-0 max-xl:px-5 max-md:px-[18px] max-md:py-[22px] max-md:nth-2:border-r-0 max-md:nth-[-n+2]:border-b">
      <dt class="text-base leading-6 text-[#66746c] max-md:text-[13px]">Total products</dt>
      <dd class="mt-[9px] mb-2 text-[40px] leading-[1.2] tracking-[-1.4px] font-[650] text-heading max-xl:text-[30px] max-md:my-2 max-md:tracking-[-1px] whitespace-nowrap max-md:text-[26px]"><CountUp :value="summary.total_products" /></dd>
      <p class="m-0 text-base leading-[21px] text-[#79847e] max-xl:text-[13px] max-md:text-[11px]">Products in your catalog</p>
    </div>
    <div class="border-r border-[#e4e9e6] px-[38px] last:border-0 max-xl:px-5 max-md:px-[18px] max-md:py-[22px] max-md:nth-2:border-r-0 max-md:nth-[-n+2]:border-b">
      <dt class="text-base leading-6 text-[#66746c] max-md:text-[13px]">Categories</dt>
      <dd class="mt-[9px] mb-2 text-[40px] leading-[1.2] tracking-[-1.4px] font-[650] text-heading max-xl:text-[30px] max-md:my-2 max-md:tracking-[-1px] whitespace-nowrap max-md:text-[26px]"><CountUp :value="summary.total_categories" /></dd>
      <p class="m-0 text-base leading-[21px] text-[#79847e] max-xl:text-[13px] max-md:text-[11px]">Catalog groups</p>
    </div>
    <div class="border-r border-[#e4e9e6] px-[38px] last:border-0 max-xl:px-5 max-md:px-[18px] max-md:py-[22px] max-md:nth-2:border-r-0 max-md:nth-[-n+2]:border-b">
      <dt class="text-base leading-6 text-[#66746c] max-md:text-[13px]">Units in stock</dt>
      <dd class="mt-[9px] mb-2 text-[40px] leading-[1.2] tracking-[-1.4px] font-[650] text-heading max-xl:text-[30px] max-md:my-2 max-md:tracking-[-1px] whitespace-nowrap max-md:text-[26px]"><CountUp :value="summary.stock_units" /></dd>
      <p class="m-0 text-base leading-[21px] text-[#79847e] max-xl:text-[13px] max-md:text-[11px]">Available units</p>
    </div>
    <div class="border-r border-[#e4e9e6] px-[38px] last:border-0 max-xl:px-5 max-md:px-[18px] max-md:py-[22px] max-md:nth-2:border-r-0 max-md:nth-[-n+2]:border-b">
      <dt class="text-base leading-6 text-[#66746c] max-md:text-[13px]">Stock value</dt>
      <dd class="mt-[9px] mb-2 text-[40px] leading-[1.2] tracking-[-1.4px] font-[650] text-heading max-xl:text-[30px] max-md:my-2 max-md:tracking-[-1px] whitespace-normal wrap-anywhere max-md:text-[22px] max-md:leading-[1.42]">
        <CountUp
          :value="Number(summary.stock_value)"
          :format="formatMoney"
        />
      </dd>
      <p class="m-0 text-base leading-[21px] text-[#79847e] max-xl:text-[13px] max-md:text-[11px]">At listed prices</p>
    </div>
  </dl>
</template>
