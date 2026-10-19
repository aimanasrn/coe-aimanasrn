<script setup lang="ts">
  import SidebarNavigation from '~/components/admin/SidebarNavigation.vue'
  const isMenuOpen = ref(false)
</script>

<template>
  <aside
    aria-label="Admin sidebar"
    class="workspace-sidebar"
  >
    <SidebarNavigation />
  </aside>
  <header class="workspace-mobilebar">
    <NuxtLink
      to="/admin"
      aria-label="StudyMart admin home"
      ><StudyMartLogo
    /></NuxtLink>
  </header>
  <UDrawer
    v-model:open="isMenuOpen"
    direction="left"
    title="StudyMart Admin"
    description="Admin navigation"
    :handle="false"
    :ui="{ content: 'workspace-drawer', overlay: 'bg-[#10251c]/35' }"
  >
    <button
      type="button"
      aria-label="Open navigation"
      class="workspace-menu"
    >
      <UIcon
        name="i-lucide-menu"
        class="size-5"
        aria-hidden="true"
      />
    </button>
    <template #content>
      <div class="flex h-full w-full flex-col overflow-y-auto">
        <div class="flex justify-end px-4 pt-3">
          <button
            type="button"
            aria-label="Close navigation"
            class="workspace-drawer__close"
            @click="isMenuOpen = false"
          >
            <UIcon
              name="i-lucide-x"
              class="size-5"
              aria-hidden="true"
            />
          </button>
        </div>
        <div class="min-h-0 flex-1"><SidebarNavigation @navigate="isMenuOpen = false" /></div>
      </div>
    </template>
  </UDrawer>
</template>
