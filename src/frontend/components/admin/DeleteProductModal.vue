<script setup lang="ts">
  import { catalogDialogUi } from '~/utils/dialogUi'
  import type { Product } from '~/types/catalog'

  const props = defineProps<{
    product: Product
  }>()

  const emit = defineEmits<{
    deleted: []
  }>()

  const config = useRuntimeConfig()
  const notification = useActionToast()
  const open = ref(false)
  const busy = ref(false)
  const trigger = useTemplateRef('trigger')

  function confirmDelete() {
    open.value = true
  }

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
      const message = 'We could not delete this product. Please try again.'
      notification.error('Product was not deleted', message)
    } finally {
      busy.value = false
    }
  }
</script>

<template>
  <UTooltip text="Delete product">
    <AppIconButton
      ref="trigger"
      type="button"
      variant="danger"
      :aria-label="`Delete ${product.name}`"
      aria-haspopup="dialog"
      :aria-expanded="open"
      @click="confirmDelete"
    >
      <UIcon
        name="i-lucide-trash-2"
        mode="svg"
        aria-hidden="true"
      />
    </AppIconButton>
  </UTooltip>
  <UModal
    v-model:open="open"
    title="Delete product"
    description="Remove this product from your StudyMart catalog."
    :dismissible="!busy"
    :close="!busy"
    :ui="catalogDialogUi"
    @after:leave="trigger?.focus()"
  >
    <template #body>
      <div class="flex flex-col gap-5">
        <p>
          Delete
          <strong>{{ product.name }}</strong>
          ? This action cannot be undone.
        </p>
        <div class="flex justify-end gap-3 pt-[5px]">
          <AppButton
            type="button"
            variant="secondary"
            :disabled="busy"
            @click="open = false"
          >
            Cancel
          </AppButton>
          <AppButton
            type="button"
            variant="danger"
            :disabled="busy"
            @click="deleteProduct"
          >
            {{ busy ? 'Deleting product...' : 'Delete product' }}
          </AppButton>
        </div>
      </div>
    </template>
  </UModal>
</template>
