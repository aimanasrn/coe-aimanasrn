<script setup lang="ts">
  import type { Product, ProductResponse } from '~/types/catalog'
  import ProductBreadcrumbs from '~/components/products/ProductBreadcrumbs.vue'
  import ProductForm from '~/components/products/ProductForm.vue'

  const props = defineProps<{ product?: Product }>()
  const editing = computed(() => Boolean(props.product))

  async function saved(product: Product) {
    useNuxtData<ProductResponse>(`product-${product.id}`).data.value = { data: product }
    await navigateTo(`/products/${product.id}`)
  }

  function cancel() {
    return navigateTo(props.product ? `/products/${props.product.id}` : '/admin/products')
  }
</script>

<template>
  <section>
    <ProductBreadcrumbs
      :mode="editing ? 'edit' : 'add'"
      :product="product"
    />
    <PageHeader
      :title="editing ? 'Edit product' : 'Add product'"
      :description="
        editing
          ? 'Update product information, images and inventory.'
          : 'Add product information, images and inventory to your catalog.'
      "
    />
    <ProductForm
      :key="product?.id ?? 'new'"
      :product="product"
      @created="saved"
      @updated="saved"
      @cancel="cancel"
    />
  </section>
</template>
