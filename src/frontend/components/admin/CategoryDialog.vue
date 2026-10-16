<script setup lang="ts">
import type { Category, CategoryDialogMode } from '~/types/catalog'
import CategoryForm from '~/components/admin/CategoryForm.vue'

const open = defineModel<boolean>('open', { required: true })
const props = defineProps<{
  mode: CategoryDialogMode
  category?: Category
}>()
const emit = defineEmits<{
  saved: [category: Category]
  deleted: []
  edit: [category: Category]
  closed: []
}>()
const config = useRuntimeConfig()
const notification = useActionToast()
const busy = ref(false)
const message = ref('')
const titles = {
  add: 'Add category',
  view: 'Category details',
  edit: 'Edit category',
  delete: 'Delete category'
}
const descriptions = {
  add: 'Create a new group for your products.',
  view: 'View this catalog group and its product count.',
  edit: 'Update the category name and description.',
  delete: 'Only categories without products can be deleted.'
}
const hasProducts = computed(() => (props.category?.products_count ?? 0) > 0)

watch(open, () => {
  message.value = ''
})

function close(value = false) {
  if (busy.value) return
  open.value = value
  if (value || props.mode === 'view') return

  notification.info(
    props.mode === 'delete' ? 'Deletion cancelled' : 'Changes cancelled',
    props.mode === 'delete'
      ? 'The category was not deleted.'
      : 'No category changes were saved.'
  )
}

function saved(category: Category) {
  open.value = false
  emit('saved', category)
}

async function deleteCategory() {
  if (busy.value || !props.category || hasProducts.value) return
  busy.value = true
  message.value = ''

  try {
    await $fetch(`/categories/${props.category.id}`, {
      baseURL: config.public.apiBase,
      method: 'DELETE'
    })
    notification.success(
      'Category deleted',
      `${props.category.name} has been removed from your catalog.`
    )
    open.value = false
    emit('deleted')
  } catch (error: unknown) {
    const response = error as {
      statusCode?: number
      data?: { message?: string }
    }
    if (response.statusCode === 409) {
      message.value =
        response.data?.message ??
        'Move this category’s products to another category before deleting it.'
      notification.warning('Category is in use', message.value)
    } else {
      message.value = 'We could not delete this category. Please try again.'
      notification.error('Category was not deleted', message.value)
    }
  } finally {
    busy.value = false
  }
}
</script>

<template>
  <UModal
    :open="open"
    :title="titles[mode]"
    :description="descriptions[mode]"
    :dismissible="!busy"
    :close="!busy"
    :ui="{ content: 'max-w-[560px] rounded-[10px] bg-white font-sans text-[#37463f] [&_button:focus-visible]:outline-2 [&_button:focus-visible]:outline-primary [&_button:focus-visible]:outline-offset-4 [&_button:disabled]:opacity-50 [&_button:disabled]:cursor-not-allowed', title: 'text-2xl font-[650] text-heading' }"
    @update:open="close"
    @after:leave="emit('closed')"
  >
    <template #body>
      <CategoryForm
        v-if="mode === 'add' || mode === 'edit'"
        :key="`${mode}-${category?.id ?? 'new'}`"
        :category="mode === 'edit' ? category : undefined"
        @saved="saved"
        @cancel="close()"
        @busy="busy = $event"
      />
      <div
        v-else-if="category && mode === 'view'"
        class="flex flex-col gap-5"
      >
        <dl class="grid gap-5 [&_dt]:mb-[5px] [&_dt]:text-[13px] [&_dt]:text-[#7b867f] [&_dd]:m-0 [&_dd]:max-h-60 [&_dd]:overflow-y-auto [&_dd]:text-heading [&_dd]:whitespace-pre-wrap [&_dd]:wrap-anywhere">
          <div>
            <dt>Category name</dt>
            <dd>{{ category.name }}</dd>
          </div>
          <div>
            <dt>Description</dt>
            <dd>
              {{ category.description || 'No description has been added yet.' }}
            </dd>
          </div>
          <div>
            <dt>Products</dt>
            <dd>{{ (category.products_count ?? 0).toLocaleString() }}</dd>
          </div>
        </dl>
        <div class="flex justify-end gap-3 pt-[5px]">
          <button
            type="button"
            class="
              inline-flex items-center justify-center gap-[9px] rounded-[7px] border px-5 py-[13px] font-[inherit]
              text-[18px] leading-6 font-semibold whitespace-nowrap cursor-pointer transition-colors duration-150 max-md:px-4
              max-md:py-2.5 max-md:text-sm border-[#dce4df] bg-white text-[#37463f] hover:bg-surface-muted
            "
            @click="close()"
          >
            Close
          </button>
          <button
            type="button"
            class="
              inline-flex items-center justify-center gap-[9px] rounded-[7px] border px-5 py-[13px] font-[inherit]
              text-[18px] leading-6 font-semibold whitespace-nowrap cursor-pointer transition-colors duration-150 max-md:px-4
              max-md:py-2.5 max-md:text-sm border-primary bg-primary text-white hover:bg-primary-hover [&_.iconify]:size-5
            "
            @click="emit('edit', category)"
          >
            Edit category
          </button>
        </div>
      </div>
      <div
        v-else-if="category"
        class="flex flex-col gap-5"
      >
        <p>
          Delete
          <strong>{{ category.name }}</strong>
          ? This action cannot be undone.
        </p>
        <p
          v-if="hasProducts"
          class="mb-5 rounded-[7px] border px-[18px] py-3.5 text-sm border-[#fde68a] bg-[#fffbeb] text-[#92400e]"
          role="status"
        >
          This category contains {{ category.products_count }}
          {{ category.products_count === 1 ? 'product' : 'products' }}. Move
          them to another category before deleting it.
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
            @click="close()"
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
            :disabled="busy || hasProducts"
            @click="deleteCategory"
          >
            {{ busy ? 'Deleting category...' : 'Delete category' }}
          </button>
        </div>
      </div>
    </template>
  </UModal>
</template>
