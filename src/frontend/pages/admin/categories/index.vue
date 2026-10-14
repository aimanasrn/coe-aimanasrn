<script setup lang="ts">
import type { Category, CategoryDialogMode } from '~/types/catalog'
import CategoryDialog from '~/components/admin/CategoryDialog.vue'

definePageMeta({ layout: 'admin' })
useHead({ title: 'Categories · StudyMart Admin' })
const { data, status, error, refresh } = useCategories()
const dialogOpen = ref(false)
const dialogMode = ref<CategoryDialogMode>('add')
const selected = ref<Category>()
const addButton = useTemplateRef('addButton')
let returnFocus: HTMLElement | null = null

function showDialog(
  mode: CategoryDialogMode,
  category?: Category,
  event?: MouseEvent
) {
  if (event?.currentTarget instanceof HTMLElement)
    returnFocus = event.currentTarget
  selected.value = category
  dialogMode.value = mode
  dialogOpen.value = true
}

async function changed() {
  await refresh()
}

function restoreFocus() {
  if (returnFocus?.isConnected) returnFocus.focus()
  else addButton.value?.focus()
}
</script>

<template>
  <section>
    <header class="sm-admin-header">
      <div>
        <h1>Categories</h1>
        <p>How your catalog is organized.</p>
      </div>
      <button
        ref="addButton"
        type="button"
        class="sm-admin-button"
        @click="showDialog('add', undefined, $event)"
      >
        <UIcon
          name="i-lucide-plus"
          mode="svg"
          aria-hidden="true"
        />
        Add category
      </button>
    </header>
    <div
      v-if="error"
      class="sm-admin-notice sm-admin-notice-error"
      role="alert"
    >
      We could not load categories.
      <button
        class="sm-admin-link"
        @click="refresh()"
      >
        Try again
      </button>
    </div>
    <div
      v-else-if="!data"
      class="sm-admin-loading"
    >
      <StudyMartLoader label="Loading categories" />
    </div>
    <section
      v-else
      class="sm-admin-panel"
      aria-labelledby="category-catalog-title"
      :aria-busy="status === 'pending'"
    >
      <div class="sm-admin-panel-heading">
        <h2 id="category-catalog-title">Catalog groups</h2>
        <span class="sm-admin-muted">
          {{ data?.data.length || 0 }}
          {{ data?.data.length === 1 ? 'category' : 'categories' }}
        </span>
      </div>
      <div
        v-if="data?.data.length"
        class="sm-category-table-scroll"
      >
        <table class="sm-admin-table sm-admin-category-table">
          <thead>
            <tr>
              <th scope="col">Category</th>
              <th scope="col">Products</th>
              <th scope="col">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="category in data.data"
              :key="category.id"
            >
              <td>
                <span class="sm-admin-category-name">
                  <UIcon
                    name="i-lucide-tags"
                    aria-hidden="true"
                  />
                  {{ category.name }}
                </span>
                <p class="sm-category-description">
                  {{ category.description || 'No description added.' }}
                </p>
              </td>
              <td>{{ (category.products_count ?? 0).toLocaleString() }}</td>
              <td>
                <div class="sm-admin-row-actions">
                  <UTooltip text="View category">
                    <button
                      type="button"
                      class="sm-admin-row-action"
                      :aria-label="`View ${category.name}`"
                      aria-haspopup="dialog"
                      @click="showDialog('view', category, $event)"
                    >
                      <UIcon
                        name="i-lucide-eye"
                        mode="svg"
                        aria-hidden="true"
                      />
                    </button>
                  </UTooltip>
                  <UTooltip text="Edit category">
                    <button
                      type="button"
                      class="sm-admin-row-action sm-admin-row-action-info"
                      :aria-label="`Edit ${category.name}`"
                      aria-haspopup="dialog"
                      @click="showDialog('edit', category, $event)"
                    >
                      <UIcon
                        name="i-lucide-pencil"
                        mode="svg"
                        aria-hidden="true"
                      />
                    </button>
                  </UTooltip>
                  <UTooltip text="Delete category">
                    <button
                      type="button"
                      class="sm-admin-row-action sm-admin-row-action-danger"
                      :aria-label="`Delete ${category.name}`"
                      aria-haspopup="dialog"
                      @click="showDialog('delete', category, $event)"
                    >
                      <UIcon
                        name="i-lucide-trash-2"
                        mode="svg"
                        aria-hidden="true"
                      />
                    </button>
                  </UTooltip>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div
        v-else
        class="sm-admin-empty"
      >
        <UIcon
          name="i-lucide-tags"
          size="32"
          aria-hidden="true"
        />
        <h3>No categories yet</h3>
        <p>Add your first category to start organizing products.</p>
      </div>
    </section>
    <CategoryDialog
      v-model:open="dialogOpen"
      :mode="dialogMode"
      :category="selected"
      @saved="changed"
      @deleted="changed"
      @edit="showDialog('edit', $event)"
      @closed="restoreFocus"
    />
  </section>
</template>
