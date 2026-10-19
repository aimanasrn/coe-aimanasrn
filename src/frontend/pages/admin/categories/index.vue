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

  function showDialog(mode: CategoryDialogMode, category?: Category, event?: MouseEvent) {
    if (event?.currentTarget instanceof HTMLElement) returnFocus = event.currentTarget
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
    <header class="page-header">
      <div>
        <h1>Categories</h1>
        <p>How your catalog is organized.</p>
      </div>
      <button
        ref="addButton"
        type="button"
        class="catalog-button catalog-button--primary"
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
      class="mb-5 rounded-[7px] border px-[18px] py-3.5 text-sm border-[#f0d5d5] bg-[#fff4f4] text-[#a03939]"
      role="alert"
    >
      We could not load categories.
      <button
        class="catalog-link"
        @click="refresh()"
      >
        Try again
      </button>
    </div>
    <div
      v-else-if="!data"
      class="grid min-h-[420px] place-items-center"
    >
      <StudyMartLoader label="Loading categories" />
    </div>
    <section
      v-else
      class="catalog-panel"
      aria-labelledby="category-catalog-title"
      :aria-busy="status === 'pending'"
    >
      <div class="catalog-panel__header">
        <h2 id="category-catalog-title">Catalog groups</h2>
        <span class="text-sm leading-[1.5] text-[#7b867f]">
          {{ data?.data.length || 0 }}
          {{ data?.data.length === 1 ? 'category' : 'categories' }}
        </span>
      </div>
      <div
        v-if="data?.data.length"
        class="overflow-x-auto"
      >
        <table class="catalog-table catalog-table--categories">
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
                <span
                  class="inline-flex items-center gap-3.5 font-[550] wrap-anywhere [&_.iconify]:size-5 [&_.iconify]:shrink-0 [&_.iconify]:text-primary"
                >
                  <UIcon
                    name="i-lucide-tags"
                    aria-hidden="true"
                  />
                  {{ category.name }}
                </span>
                <p
                  class="mt-[7px] line-clamp-2 max-w-[540px] text-sm leading-[1.5] text-[#7b867f] wrap-anywhere"
                >
                  {{ category.description || 'No description added.' }}
                </p>
              </td>
              <td>{{ (category.products_count ?? 0).toLocaleString() }}</td>
              <td>
                <div class="flex items-center gap-2 whitespace-nowrap">
                  <UTooltip text="View category">
                    <button
                      type="button"
                      class="catalog-action"
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
                      class="catalog-action"
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
                      class="catalog-action catalog-action--danger"
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
        class="flex min-h-[225px] flex-col items-center justify-center gap-3 px-7 py-[50px] text-center text-[#7b867f] [&_h3]:text-[17px] [&_h3]:font-semibold [&_h3]:text-[#374f40] [&_p]:max-w-[320px] [&_p]:text-sm"
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
