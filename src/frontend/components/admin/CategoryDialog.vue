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

  function close(value = false) {
    if (busy.value) return
    open.value = value
  }

  function saved(category: Category) {
    open.value = false
    emit('saved', category)
  }

  async function deleteCategory() {
    if (busy.value || !props.category || hasProducts.value) return
    busy.value = true

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
      const response = error as { statusCode?: number; data?: { message?: string } }
      if (response.statusCode === 409) {
        notification.warning(
          'Category is in use',
          response.data?.message ??
            'Move this category’s products to another category before deleting it.'
        )
      } else {
        notification.error(
          'Category was not deleted',
          'We could not delete this category. Please try again.'
        )
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
    :ui="{ footer: 'justify-end' }"
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
      <dl
        v-else-if="category && mode === 'view'"
        class="grid gap-5"
      >
        <div>
          <dt class="mb-1 text-sm text-muted">Category name</dt>
          <dd class="font-semibold text-highlighted wrap-anywhere">{{ category.name }}</dd>
        </div>
        <div>
          <dt class="mb-1 text-sm text-muted">Description</dt>
          <dd class="max-h-60 overflow-y-auto whitespace-pre-wrap wrap-anywhere">
            {{ category.description || 'No description has been added yet.' }}
          </dd>
        </div>
        <div>
          <dt class="mb-1 text-sm text-muted">Products</dt>
          <dd class="text-highlighted">{{ (category.products_count ?? 0).toLocaleString() }}</dd>
        </div>
      </dl>
      <div
        v-else-if="category"
        class="flex flex-col gap-4"
      >
        <p>
          Delete <strong class="text-highlighted">{{ category.name }}</strong
          >? This action cannot be undone.
        </p>
        <UAlert
          v-if="hasProducts"
          color="warning"
          variant="subtle"
          icon="i-lucide-triangle-alert"
          :description="`This category contains ${category.products_count} ${
            category.products_count === 1 ? 'product' : 'products'
          }. Move them to another category before deleting it.`"
        />
      </div>
    </template>

    <template
      v-if="category && mode === 'view'"
      #footer
    >
      <UButton
        label="Close"
        color="neutral"
        variant="outline"
        @click="close()"
      />
      <UButton
        label="Edit category"
        icon="i-lucide-pencil"
        @click="emit('edit', category)"
      />
    </template>
    <template
      v-else-if="category && mode === 'delete'"
      #footer
    >
      <UButton
        label="Cancel"
        color="neutral"
        variant="outline"
        :disabled="busy"
        @click="close()"
      />
      <UButton
        :label="busy ? 'Deleting category...' : 'Delete category'"
        color="error"
        :loading="busy"
        :disabled="hasProducts"
        @click="deleteCategory"
      />
    </template>
  </UModal>
</template>
