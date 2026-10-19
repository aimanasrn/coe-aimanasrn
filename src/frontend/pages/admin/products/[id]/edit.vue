<script setup lang="ts">
  import ProductEditor from '~/components/products/ProductEditor.vue'
  import ProductBreadcrumbs from '~/components/products/ProductBreadcrumbs.vue'
  import ProductLoadState from '~/components/products/ProductLoadState.vue'

  definePageMeta({ layout: 'admin' })
  const route = useRoute()
  const { data, status, error, refresh } = useProduct(() => String(route.params.id))
  const product = computed(() => data.value?.data)
  const loading = computed(() => status.value === 'idle' || status.value === 'pending')
  useHead(() => ({
    title: product.value
      ? `Edit ${product.value.name} · StudyMart Admin`
      : 'Edit product · StudyMart Admin'
  }))
</script>

<template>
  <ProductEditor
    v-if="product && !error && !loading"
    :product="product"
  />
  <section v-else>
    <ProductBreadcrumbs mode="edit" />
    <ProductLoadState
      :loading="loading"
      :not-found="error?.statusCode === 404"
      @retry="refresh()"
    />
  </section>
</template>
