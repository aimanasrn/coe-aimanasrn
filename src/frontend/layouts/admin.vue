<script setup lang="ts">
  import SidebarNavigation from '~/components/admin/SidebarNavigation.vue'

  const route = useRoute()
  const menuOpen = ref(false)
  watch(
    () => route.fullPath,
    () => {
      menuOpen.value = false
    }
  )
</script>

<template>
  <div class="min-h-svh bg-muted">
    <aside
      aria-label="Admin sidebar"
      class="fixed inset-y-0 left-0 z-30 hidden w-64 border-r border-default bg-default lg:block"
    >
      <SidebarNavigation />
    </aside>

    <header
      class="sticky top-0 z-30 flex h-16 items-center justify-between gap-3 border-b border-default bg-default/90 px-4 backdrop-blur lg:hidden"
    >
      <NuxtLink
        to="/admin"
        aria-label="StudyMart admin home"
      >
        <StudyMartLogo />
      </NuxtLink>
      <div class="flex items-center gap-1">
        <ColorModeToggle />
        <USlideover
          v-model:open="menuOpen"
          side="left"
          title="StudyMart Admin"
          description="Admin navigation"
          :ui="{ content: 'max-w-72' }"
        >
          <UButton
            icon="i-lucide-menu"
            color="neutral"
            variant="ghost"
            aria-label="Open navigation"
          />
          <template #content>
            <div class="relative h-full">
              <UButton
                icon="i-lucide-x"
                color="neutral"
                variant="ghost"
                aria-label="Close navigation"
                class="absolute top-4 right-3 z-10"
                @click="menuOpen = false"
              />
              <SidebarNavigation />
            </div>
          </template>
        </USlideover>
      </div>
    </header>

    <main class="lg:pl-64">
      <div class="mx-auto w-full max-w-7xl px-4 py-8 sm:px-6 lg:px-10 lg:py-10">
        <slot />
      </div>
    </main>
  </div>
</template>
