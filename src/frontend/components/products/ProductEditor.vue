<script setup lang="ts">
  import type { Product, ProductResponse } from '~/types/catalog'
  import ProductBreadcrumbs from '~/components/products/ProductBreadcrumbs.vue'
  import ProductForm from '~/components/products/ProductForm.vue'

  const props = defineProps<{ product?: Product }>()
  const editing = computed(() => Boolean(props.product))

  async function saved(product: Product) {
    useNuxtData<ProductResponse>(`product-${product.id}`).data.value = {
      data: product
    }
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
    <header
      class="
        flex items-start justify-between gap-5 mb-7 [&_>_:is(button,_a)]:flex-none max-md:flex-wrap
        max-md:gap-4.5 max-md:mb-6
      "
    >
      <div>
        <h1
          class="
            m-0 text-heading text-[34px] font-semibold leading-[1.2] tracking-[-0.9px] wrap-anywhere
            max-md:text-[30px]
          "
        >
          {{ editing ? 'Edit product' : 'Add product' }}
        </h1>
        <p class="mt-[9px] text-muted text-base leading-[1.6] max-md:text-sm">
          {{
            editing
              ? 'Update product information, images and inventory.'
              : 'Add product information, images and inventory to your catalog.'
          }}
        </p>
      </div>
    </header>
    <ProductForm
      :key="product?.id ?? 'new'"
      :product="product"
      @created="saved"
      @updated="saved"
      @cancel="cancel"
    />
  </section>
</template>
