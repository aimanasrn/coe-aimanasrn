<script setup lang="ts">
  import type { BreadcrumbItem } from '@nuxt/ui'
  import type { Product } from '~/types/catalog'

  const props = defineProps<{
    mode: 'add' | 'view' | 'edit'
    product?: Product
  }>()

  const items = computed<BreadcrumbItem[]>(() => {
    const crumbs: BreadcrumbItem[] = [
      { label: 'Dashboard', to: '/admin', icon: 'i-lucide-layout-dashboard' },
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
    class="mb-5"
    :ui="{ list: 'flex-wrap gap-y-1', link: 'wrap-anywhere whitespace-normal' }"
  />
</template>
