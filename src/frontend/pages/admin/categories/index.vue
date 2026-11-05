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
  let returnFocus: HTMLElement | null = null

  function showDialog(mode: CategoryDialogMode, category?: Category, event?: MouseEvent) {
    if (event?.currentTarget instanceof HTMLElement) returnFocus = event.currentTarget
    selected.value = category
    dialogMode.value = mode
    dialogOpen.value = true
  }

  function restoreFocus() {
    if (returnFocus?.isConnected) returnFocus.focus()
  }
</script>

<template>
  <section>
    <PageHeader
      title="Categories"
      description="How your catalog is organized."
    >
      <template #actions>
        <UButton
          icon="i-lucide-plus"
          label="Add category"
          aria-haspopup="dialog"
          @click="showDialog('add', undefined, $event)"
        />
      </template>
    </PageHeader>

    <UAlert
      v-if="error && status === 'error'"
      class="mb-6"
      color="error"
      variant="subtle"
      icon="i-lucide-circle-alert"
      title="We could not load categories."
      :actions="[
        { label: 'Try again', color: 'error', variant: 'outline', onClick: () => refresh() }
      ]"
    />
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
      @saved="refresh()"
      @deleted="refresh()"
      @edit="showDialog('edit', $event)"
      @closed="restoreFocus"
    />
  </section>
</template>
