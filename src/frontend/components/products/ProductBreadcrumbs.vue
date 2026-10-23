<script setup lang="ts">
  import type { BreadcrumbItem } from '@nuxt/ui'
  import type { Product } from '~/types/catalog'

  const props = defineProps<{
    mode: 'add' | 'view' | 'edit'
    product?: Product
  }>()

  const items = computed<BreadcrumbItem[]>(() => {
    const crumbs: BreadcrumbItem[] = [
      { label: 'Dashboard', to: '/admin' },
      { label: 'Products', to: '/admin/products' }
    ]
    if (props.mode === 'add') return [...crumbs, { label: 'Add product' }]
    crumbs.push({
      label: props.product?.name ?? 'Product details',
      to: props.mode === 'edit' && props.product ? `/products/${props.product.id}` : undefined
    })
    if (props.mode === 'edit') crumbs.push({ label: 'Edit' })
    return crumbs
  })
</script>

<template>
  <UBreadcrumb
    :items="items"
    class="
      text-xs mb-6 max-md:mb-5 [&_ol]:flex-wrap [&_ol]:gap-y-2 [&_li]:max-md:max-w-full
      [&_a]:max-md:whitespace-normal [&_a]:max-md:wrap-anywhere [&_span]:max-md:whitespace-normal
      [&_span]:max-md:wrap-anywhere
    "
  />
</template>
