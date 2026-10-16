<script setup lang="ts">
import type { Product, ProductResponse } from '~/types/catalog'
import ProductBreadcrumbs from '~/components/products/ProductBreadcrumbs.vue'
import ProductForm from '~/components/products/ProductForm.vue'

const props = defineProps<{ product?: Product }>()
const editing = computed(() => Boolean(props.product))
const notification = useActionToast()

async function saved(product: Product) {
  useNuxtData<ProductResponse>(`product-${product.id}`).data.value = {
    data: product
  }
  await navigateTo(`/products/${product.id}`)
}

function cancel() {
  notification.info(
    editing.value ? 'Editing cancelled' : 'Product creation cancelled',
    'No changes were saved.'
  )
  return navigateTo(
    props.product ? `/products/${props.product.id}` : '/admin/products'
  )
}
</script>

<template>
  <section>
    <ProductBreadcrumbs
      :mode="editing ? 'edit' : 'add'"
      :product="product"
    />
    <header class="mb-[30px] flex items-start justify-between gap-6 max-md:flex-col max-md:gap-5 [&_h1]:text-[clamp(26px,2.4vw,36px)] [&_h1]:leading-[1.25] [&_h1]:font-bold [&_h1]:tracking-[-1px] [&_h1]:text-heading [&_h1]:wrap-anywhere [&_h1]:max-md:text-[28px] [&_p]:mt-2 [&_p]:text-base [&_p]:leading-[1.6] [&_p]:text-[#6b7871]">
      <div>
        <h1>{{ editing ? 'Edit product' : 'Add product' }}</h1>
        <p>
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
