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
    <header class="mb-[31px] flex items-start justify-between gap-6 max-md:mb-7 max-md:flex-col max-md:gap-5 [&_h1]:m-0 [&_h1]:text-[46px] [&_h1]:leading-[1.15] [&_h1]:tracking-[-1.5px] [&_h1]:font-bold [&_h1]:text-heading [&_h1]:max-md:text-[34px] [&_h1]:max-md:tracking-[-1px] [&_p]:mt-1 [&_p]:text-xl [&_p]:leading-[26px] [&_p]:text-[#6b7871] [&_p]:max-md:mt-[9px] [&_p]:max-md:text-[15px] [&>button]:mt-2 [&>button]:max-md:mt-0">
      <div>
        <h1>Categories</h1>
        <p>How your catalog is organized.</p>
      </div>
      <button
        ref="addButton"
        type="button"
        class="
          inline-flex items-center justify-center gap-[9px] rounded-[7px] border px-5 py-[13px] font-[inherit]
          text-[18px] leading-6 font-semibold whitespace-nowrap cursor-pointer transition-colors duration-150 max-md:px-4
          max-md:py-2.5 max-md:text-sm border-primary bg-primary text-white hover:bg-primary-hover [&_.iconify]:size-5
        "
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
        class="
          inline-flex items-center gap-[9px] text-[15px] font-[550] text-primary cursor-pointer hover:text-primary-hover
          hover:underline hover:underline-offset-4 [&_.iconify]:size-[18px] [&_.iconify]:shrink-0
        "
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
      class="
        overflow-hidden rounded-lg border border-[#e0e6e2] bg-white [&_h2]:m-0 [&_h2]:text-[26px] [&_h2]:leading-8
        [&_h2]:font-[650] [&_h2]:tracking-[-0.6px] [&_h2]:text-heading [&_h2]:max-md:text-[22px]
      "
      aria-labelledby="category-catalog-title"
      :aria-busy="status === 'pending'"
    >
      <div class="flex items-center justify-between gap-5 px-6 pt-[21px] pb-[25px] max-md:flex-col max-md:items-start max-md:gap-2.5 max-md:px-5 max-md:py-[22px]">
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
        <table class="w-full border-collapse text-left text-[15px] max-md:min-w-[680px] [&_th]:border-b [&_th]:border-[#e5eae7] [&_th]:px-2.5 [&_th]:py-[13px] [&_th]:text-[15px] [&_th]:font-medium [&_th]:text-[#7b867f] [&_th]:whitespace-nowrap [&_th]:first:pl-0 [&_th]:last:pr-0 [&_td]:border-b [&_td]:border-[#e5eae7] [&_td]:px-2.5 [&_td]:py-4 [&_td]:align-middle [&_td]:first:pl-0 [&_td]:last:pr-0 min-w-[580px] max-md:min-w-[580px] [&_th]:px-7! [&_td]:px-7! [&_td:last-child]:w-48">
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
                <span class="inline-flex items-center gap-3.5 font-[550] wrap-anywhere [&_.iconify]:size-5 [&_.iconify]:shrink-0 [&_.iconify]:text-primary">
                  <UIcon
                    name="i-lucide-tags"
                    aria-hidden="true"
                  />
                  {{ category.name }}
                </span>
                <p class="mt-[7px] line-clamp-2 max-w-[540px] text-[13px] leading-[1.5] text-[#7b867f] wrap-anywhere">
                  {{ category.description || 'No description added.' }}
                </p>
              </td>
              <td>{{ (category.products_count ?? 0).toLocaleString() }}</td>
              <td>
                <div class="flex items-center gap-2 whitespace-nowrap">
                  <UTooltip text="View category">
                    <button
                      type="button"
                      class="
                        inline-flex size-10 shrink-0 items-center justify-center rounded-md border border-[#dce4df] bg-white p-2.5
                        text-[#374f40] cursor-pointer transition-colors duration-150 hover:border-primary hover:bg-primary
                        hover:text-white focus-visible:border-primary focus-visible:bg-primary focus-visible:text-white
                        focus-visible:outline-2 focus-visible:outline-primary focus-visible:outline-offset-2 [&_.iconify]:size-[18px]
                      "
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
                      class="
                        inline-flex size-10 shrink-0 items-center justify-center rounded-md border border-[#dce4df] bg-white p-2.5
                        text-[#374f40] cursor-pointer transition-colors duration-150 hover:border-info hover:bg-info hover:text-white
                        focus-visible:border-info focus-visible:bg-info focus-visible:text-white focus-visible:outline-2
                        focus-visible:outline-info focus-visible:outline-offset-2 [&_.iconify]:size-[18px]
                      "
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
                      class="
                        inline-flex size-10 shrink-0 items-center justify-center rounded-md border border-[#dce4df] bg-white p-2.5
                        text-[#374f40] cursor-pointer transition-colors duration-150 hover:border-danger hover:bg-danger
                        hover:text-white focus-visible:border-danger focus-visible:bg-danger focus-visible:text-white
                        focus-visible:outline-2 focus-visible:outline-danger focus-visible:outline-offset-2 [&_.iconify]:size-[18px]
                      "
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
        class="
          flex min-h-[225px] flex-col items-center justify-center gap-3 px-7 py-[50px] text-center text-[#7b867f]
          [&_h3]:text-[17px] [&_h3]:font-semibold [&_h3]:text-[#374f40] [&_p]:max-w-[320px] [&_p]:text-sm
        "
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
