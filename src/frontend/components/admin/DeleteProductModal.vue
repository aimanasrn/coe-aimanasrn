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
      class="
        inline-flex size-10 shrink-0 items-center justify-center rounded-md border border-[#dce4df] bg-white p-2.5
        text-[#374f40] cursor-pointer transition-colors duration-150 hover:border-danger hover:bg-danger
        hover:text-white focus-visible:border-danger focus-visible:bg-danger focus-visible:text-white
        focus-visible:outline-2 focus-visible:outline-danger focus-visible:outline-offset-2 [&_.iconify]:size-[18px]
      "
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
    :ui="{ content: 'max-w-[560px] rounded-[10px] bg-white font-sans text-[#37463f] [&_button:focus-visible]:outline-2 [&_button:focus-visible]:outline-primary [&_button:focus-visible]:outline-offset-4 [&_button:disabled]:opacity-50 [&_button:disabled]:cursor-not-allowed', title: 'text-2xl font-[650] text-heading' }"
    @after:leave="trigger?.focus()"
  >
    <template #body>
      <div class="flex flex-col gap-5">
        <p>
          Delete
          <strong>{{ product.name }}</strong>
          ? This action cannot be undone.
        </p>
        <p
          v-if="message"
          class="mb-5 rounded-[7px] border px-[18px] py-3.5 text-sm border-[#f0d5d5] bg-[#fff4f4] text-[#a03939]"
          role="alert"
        >
          {{ message }}
        </p>
        <div class="flex justify-end gap-3 pt-[5px]">
          <button
            type="button"
            class="
              inline-flex items-center justify-center gap-[9px] rounded-[7px] border px-5 py-[13px] font-[inherit]
              text-[18px] leading-6 font-semibold whitespace-nowrap cursor-pointer transition-colors duration-150 max-md:px-4
              max-md:py-2.5 max-md:text-sm border-[#dce4df] bg-white text-[#37463f] hover:bg-surface-muted
            "
            :disabled="busy"
            @click="open = false"
          >
            Cancel
          </button>
          <button
            type="button"
            class="
              inline-flex items-center justify-center gap-[9px] rounded-[7px] border px-5 py-[13px] font-[inherit]
              text-[18px] leading-6 font-semibold whitespace-nowrap cursor-pointer transition-colors duration-150 max-md:px-4
              max-md:py-2.5 max-md:text-sm border-primary bg-danger text-white hover:bg-red-700 [&_.iconify]:size-5
            "
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
