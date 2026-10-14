<script setup lang="ts">
defineProps<{ loading: boolean; notFound?: boolean }>()
const emit = defineEmits<{ retry: [] }>()
</script>

<template>
  <div
    v-if="loading"
    class="sm-admin-loading"
  >
    <StudyMartLoader label="Loading product" />
  </div>
  <section
    v-else
    class="sm-product-state"
    role="alert"
  >
    <UIcon
      :name="notFound ? 'i-lucide-package-search' : 'i-lucide-circle-alert'"
      mode="svg"
      aria-hidden="true"
    />
    <h1>{{ notFound ? 'Product not found' : 'Unable to load product' }}</h1>
    <p>
      {{
        notFound
          ? 'This product may have been removed from the catalog.'
          : 'Please try loading this product again.'
      }}
    </p>
    <NuxtLink
      v-if="notFound"
      to="/admin/products"
      class="sm-admin-button"
    >
      Back to products
    </NuxtLink>
    <button
      v-else
      type="button"
      class="sm-admin-button"
      @click="emit('retry')"
    >
      Try again
    </button>
  </section>
</template>
