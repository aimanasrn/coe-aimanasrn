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
    :ui="{ content: 'sm-admin-modal', title: 'sm-admin-modal-title' }"
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
        class="sm-admin-form"
      >
        <dl class="sm-category-details">
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
        <div class="sm-admin-form-actions">
          <button
            type="button"
            class="sm-admin-button-secondary"
            @click="close()"
          >
            Close
          </button>
          <button
            type="button"
            class="sm-admin-button"
            @click="emit('edit', category)"
          >
            Edit category
          </button>
        </div>
      </div>
      <div
        v-else-if="category"
        class="sm-admin-form"
      >
        <p>
          Delete
          <strong>{{ category.name }}</strong>
          ? This action cannot be undone.
        </p>
        <p
          v-if="hasProducts"
          class="sm-admin-notice sm-category-warning"
          role="status"
        >
          This category contains {{ category.products_count }}
          {{ category.products_count === 1 ? 'product' : 'products' }}. Move
          them to another category before deleting it.
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
            @click="close()"
          >
            Cancel
          </button>
          <button
            type="button"
            class="sm-admin-button sm-admin-button-danger"
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
