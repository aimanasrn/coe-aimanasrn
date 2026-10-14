<script setup lang="ts">
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
const message = ref('')
const trigger = ref<HTMLButtonElement>()

function confirmDelete() {
  message.value = ''
  open.value = true
}

async function deleteProduct() {
  if (busy.value) return

  busy.value = true
  message.value = ''

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
    message.value = 'We could not delete this product. Please try again.'
    notification.error('Product was not deleted', message.value)
  } finally {
    busy.value = false
  }
}
</script>

<template>
  <UTooltip text="Delete product">
    <button
      ref="trigger"
      type="button"
      class="sm-admin-row-action sm-admin-row-action-danger"
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
    </button>
  </UTooltip>
  <UModal
    v-model:open="open"
    title="Delete product"
    description="Remove this product from your StudyMart catalog."
    :dismissible="!busy"
    :close="!busy"
    :ui="{ content: 'sm-admin-modal', title: 'sm-admin-modal-title' }"
    @after:leave="trigger?.focus()"
  >
    <template #body>
      <div class="sm-admin-form">
        <p>
          Delete
          <strong>{{ product.name }}</strong>
          ? This action cannot be undone.
        </p>
        <p
          v-if="message"
          class="sm-admin-notice sm-admin-notice-error"
          role="alert"
        >
          {{ message }}
        </p>
        <div class="sm-admin-form-actions">
          <button
            type="button"
            class="sm-admin-button-secondary"
            :disabled="busy"
            @click="open = false"
          >
            Cancel
          </button>
          <button
            type="button"
            class="sm-admin-button sm-admin-button-danger"
            :disabled="busy"
            @click="deleteProduct"
          >
            {{ busy ? 'Deleting product...' : 'Delete product' }}
          </button>
        </div>
      </div>
    </template>
  </UModal>
</template>
