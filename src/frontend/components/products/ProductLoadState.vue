<script setup lang="ts">
  withDefaults(defineProps<{ loading: boolean; notFound?: boolean; backTo?: string }>(), {
    backTo: '/admin/products'
  })
  const emit = defineEmits<{ retry: [] }>()
</script>

<template>
  <div
    v-if="loading"
    class="grid min-h-[420px] place-items-center"
  >
    <StudyMartLoader label="Loading product" />
  </div>
  <EmptyState
    v-else
    role="alert"
    heading-tag="h1"
    :icon="notFound ? 'i-lucide-package-search' : 'i-lucide-circle-alert'"
    :title="notFound ? 'Product not found' : 'Unable to load product'"
    :description="
      notFound
        ? 'This product may have been removed from the catalog.'
        : 'Please try loading this product again.'
    "
  >
    <UButton
      v-if="notFound"
      :to="backTo"
      label="Back to products"
      icon="i-lucide-arrow-left"
    />
    <UButton
      v-else
      label="Try again"
      icon="i-lucide-refresh-cw"
      @click="emit('retry')"
    />
  </EmptyState>
</template>
