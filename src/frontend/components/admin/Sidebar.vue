<script setup lang="ts">
import SidebarNavigation from '~/components/admin/SidebarNavigation.vue'

const isMenuOpen = ref(false)
</script>

<template>
  <aside
    aria-label="Admin sidebar"
    class="fixed inset-y-0 left-0 hidden w-[272px] overflow-y-auto border-r border-border bg-surface md:block"
  >
    <SidebarNavigation />
  </aside>

  <UDrawer
    v-model:open="isMenuOpen"
    direction="left"
    title="StudyMart Admin"
    description="Admin navigation"
    :handle="false"
    :ui="{ content: 'w-72 max-w-[90vw] bg-surface' }"
  >
    <button
      type="button"
      aria-label="Open navigation"
      class="fixed left-4 top-4 z-40 inline-flex items-center gap-2 rounded-lg border border-border bg-surface px-3 py-2 text-sm font-medium text-heading shadow-sm hover:bg-secondary focus-visible:outline-2 focus-visible:outline-ring md:hidden"
    >
      <UIcon
        name="i-lucide-menu"
        size="20"
        aria-hidden="true"
      />
      Menu
    </button>

    <template #content>
      <div class="h-full overflow-y-auto">
        <div class="flex justify-end px-5 pt-4">
          <button
            type="button"
            aria-label="Close navigation"
            class="rounded-lg p-2 text-heading hover:bg-surface-muted focus-visible:outline-2 focus-visible:outline-ring"
            @click="isMenuOpen = false"
          >
            <UIcon
              name="i-lucide-x"
              size="20"
              aria-hidden="true"
            />
          </button>
        </div>

        <SidebarNavigation @navigate="isMenuOpen = false" />
      </div>
    </template>
  </UDrawer>
</template>
