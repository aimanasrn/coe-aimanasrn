<script setup lang="ts">
  import type { Product } from '~/types/catalog'

  const props = defineProps<{
    product: Product
    labeled?: boolean
  }>()

  const emit = defineEmits<{
    deleted: []
  }>()

  const config = useRuntimeConfig()
  const notification = useActionToast()
  const open = ref(false)
  const busy = ref(false)

  async function deleteProduct() {
    if (busy.value) return
    busy.value = true

    try {
      await $fetch(`/products/${props.product.id}`, {
        baseURL: config.public.apiBase,
        method: 'DELETE'
      })
      open.value = false
      notification.success(
        'Product deleted',
        `${props.product.name} has been removed from your catalog.`
      )
      emit('deleted')
    } catch {
      notification.error(
        'Product was not deleted',
        'We could not delete this product. Please try again.'
      )
    } finally {
      busy.value = false
    }
  }
</script>

<template>
  <UButton
    v-if="labeled"
    icon="i-lucide-trash-2"
    label="Delete"
    color="error"
    variant="soft"
    aria-haspopup="dialog"
    @click="open = true"
  />
  <UTooltip
    v-else
    text="Delete product"
  >
    <UButton
      icon="i-lucide-trash-2"
      color="error"
      variant="ghost"
      :aria-label="`Delete ${product.name}`"
      aria-haspopup="dialog"
      @click="open = true"
    />
  </UTooltip>
  <UModal
    v-model:open="open"
    title="Delete product"
    description="Remove this product from your StudyMart catalog."
    :dismissible="!busy"
    :close="!busy"
    :ui="{ footer: 'justify-end' }"
  >
    <template #body>
      <p>
        Delete <strong class="text-highlighted">{{ product.name }}</strong
        >? This action cannot be undone.
      </p>
    </template>
    <template #footer>
      <UButton
        label="Cancel"
        color="neutral"
        variant="outline"
        :disabled="busy"
        @click="open = false"
      />
      <UButton
        :label="busy ? 'Deleting product...' : 'Delete product'"
        color="error"
        :loading="busy"
        @click="deleteProduct"
      />
    </template>
  </UModal>
</template>
