<script setup lang="ts">
  import CountUp from '~/components/CountUp.vue'
  import type { DashboardData } from '~/types/catalog'
  import { formatMoney } from '~/utils/catalog'
  defineProps<{ summary: DashboardData['summary'] }>()
</script>

<template>
  <dl class="dashboard-summary">
    <div>
      <dt>Total products</dt>
      <dd><CountUp :value="summary.total_products" /></dd>
      <p>Products in your catalog</p>
    </div>
    <div>
      <dt>Categories</dt>
      <dd><CountUp :value="summary.total_categories" /></dd>
      <p>Catalog groups</p>
    </div>
    <div>
      <dt>Units in stock</dt>
      <dd><CountUp :value="summary.stock_units" /></dd>
      <p>Available units</p>
    </div>
    <div>
      <dt>Stock value</dt>
      <dd>
        <CountUp
          :value="Number(summary.stock_value)"
          :format="formatMoney"
        />
      </dd>
      <p>At listed prices</p>
    </div>
  </dl>
</template>
