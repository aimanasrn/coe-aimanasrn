<script setup lang="ts">
  import type { Category, CategoryDialogMode } from '~/types/catalog'
  import CategoryDialog from '~/components/admin/CategoryDialog.vue'
  import CategoryTable from '~/components/admin/CategoryTable.vue'

  definePageMeta({ layout: 'admin' })
  useHead({ title: 'Categories · StudyMart Admin' })
  const { data, status, error, refresh, search, products, page, itemsPerPage, sort } =
    useCategoryCatalog()
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
    <header
      class="
        flex items-start justify-between gap-5 mb-7 [&_>_:is(button,_a)]:flex-none max-md:flex-wrap
        max-md:gap-4.5 max-md:mb-6
      "
    >
      <div>
        <h1
          class="
            m-0 text-heading text-[34px] font-semibold leading-[1.2] tracking-[-0.9px] wrap-anywhere
            max-md:text-[30px]
          "
        >
          Categories
        </h1>
        <p class="mt-[9px] text-muted text-base leading-[1.6] max-md:text-sm">
          How your catalog is organized.
        </p>
      </div>
      <AppButton
        ref="addButton"
        type="button"
        @click="showDialog('add', undefined, $event)"
      >
        <UIcon
          name="i-lucide-plus"
          mode="svg"
          aria-hidden="true"
        />
        Add category
      </AppButton>
    </header>
    <div
      v-if="error && status === 'error'"
      class="mb-5 rounded-[7px] border px-[18px] py-3.5 text-sm border-[#f0d5d5] bg-[#fff4f4] text-[#a03939]"
      role="alert"
    >
      We could not load categories.
      <button
        class="
          inline-flex items-center gap-2 rounded-[3px] text-primary text-sm font-medium leading-normal
          cursor-pointer [&_.iconify]:w-4.5 [&_.iconify]:h-4.5 [&_.iconify]:flex-none
          hover:text-primary-hover hover:underline hover:underline-offset-4
        "
        @click="refresh()"
      >
        Try again
      </button>
    </div>
    <div
      v-else-if="!data && (status === 'idle' || status === 'pending')"
      class="grid min-h-[420px] place-items-center"
    >
      <StudyMartLoader label="Loading categories" />
    </div>
    <CategoryTable
      v-if="data"
      v-model:search="search"
      v-model:filter="products"
      v-model:page="page"
      v-model:items-per-page="itemsPerPage"
      v-model:sort="sort"
      :categories="data.data"
      :total="data.total"
      :last-page="data.last_page"
      :loading="status === 'pending'"
      @action="showDialog"
    />
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
