<script setup lang="ts">
  import ShopProductCard from '~/components/shop/ShopProductCard.vue'
  import type { ShopPage } from '~/types/shop'
  import type { Category } from '~/types/catalog'
  definePageMeta({ layout: 'shop', colorMode: 'light' })
  useHead({ title: 'Shop · StudyMart' })
  const search = ref('')
  const query = ref('')
  const category = ref('')
  const sort = ref('newest')
  const page = ref(1)
  const { data, status, error, refresh } = await useFetch<ShopPage>('/api/shop/products', {
    query: { q: query, category: computed(() => category.value || undefined), sort, page }
  })
  const { data: categories } = await useFetch<{ data: Category[] }>('/api/shop/categories')
  useRequestNotification(error, 'the shop')
  watch([category, sort], () => {
    page.value = 1
  })
  function findProducts() {
    page.value = 1
    query.value = search.value.trim()
  }
</script>
<template>
  <section>
    <header class="shop-page-heading">
      <div>
        <h1>Your study essentials</h1>
        <p>Books, stationery, and everything for your next chapter.</p>
      </div>
      <span
        v-if="data"
        class="text-sm text-muted"
        >{{ data.total }} products</span
      >
    </header>
    <div class="shop-toolbar">
      <form
        class="shop-search"
        role="search"
        @submit.prevent="findProducts"
      >
        <UIcon
          name="i-lucide-search"
          class="size-5 text-muted"
          aria-hidden="true"
        />
        <input
          v-model="search"
          type="search"
          aria-label="Search products"
          placeholder="Search your next essential..."
          maxlength="150"
        />
        <button
          type="submit"
          class="catalog-button catalog-button--primary"
        >
          Search
        </button>
      </form>
      <select
        v-model="category"
        aria-label="Filter by category"
        class="shop-select"
      >
        <option value="">All categories</option>
        <option
          v-for="item in categories?.data"
          :key="item.id"
          :value="String(item.id)"
        >
          {{ item.name }}
        </option>
      </select>
      <select
        v-model="sort"
        aria-label="Sort products"
        class="shop-select"
      >
        <option value="newest">Newest first</option>
        <option value="price_asc">Price: low to high</option>
        <option value="price_desc">Price: high to low</option>
        <option value="name">Name: A–Z</option>
      </select>
    </div>
    <div
      v-if="error"
      class="shop-empty"
      role="alert"
    >
      <h2>We couldn’t load the shop.</h2>
      <button
        class="catalog-button catalog-button--primary"
        @click="refresh()"
      >
        Try again
      </button>
    </div>
    <div
      v-else-if="!data"
      class="shop-empty"
    >
      <StudyMartLoader label="Loading products" />
    </div>
    <div
      v-else-if="!data.data.length"
      class="shop-empty"
    >
      <UIcon
        name="i-lucide-search"
        class="size-10 text-muted"
        aria-hidden="true"
      />
      <h2>No products found</h2>
      <p>Try another search or category.</p>
    </div>
    <div
      v-else
      class="shop-grid"
      :aria-busy="status === 'pending'"
    >
      <ShopProductCard
        v-for="product in data.data"
        :key="product.id"
        :product="product"
      />
    </div>
    <nav
      v-if="data && data.last_page > 1"
      class="shop-pagination"
      aria-label="Product pages"
    >
      <button
        class="catalog-button catalog-button--secondary"
        :disabled="page <= 1 || status === 'pending'"
        @click="page--"
      >
        Previous
      </button>
      <span>Page {{ data.current_page }} of {{ data.last_page }}</span>
      <button
        class="catalog-button catalog-button--secondary"
        :disabled="page >= data.last_page || status === 'pending'"
        @click="page++"
      >
        Next
      </button>
    </nav>
  </section>
</template>
