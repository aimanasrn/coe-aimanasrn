<script setup lang="ts">
  import type { NavigationMenuItem } from '@nuxt/ui'
  import SidebarAccount from '~/components/admin/SidebarAccount.vue'

  const route = useRoute()

  function isActive(to: string) {
    if (to === '/admin') return route.path === '/admin'
    if (to === '/admin/products' && route.path.startsWith('/products/')) return true
    return route.path === to || route.path.startsWith(`${to}/`)
  }

  const items = computed<NavigationMenuItem[]>(() =>
    [
      { label: 'Dashboard', to: '/admin', icon: 'i-lucide-layout-dashboard' },
      { label: 'Products', to: '/admin/products', icon: 'i-lucide-package' },
      { label: 'Categories', to: '/admin/categories', icon: 'i-lucide-tags' }
    ].map((item) => ({ ...item, active: isActive(item.to) }))
  )
</script>

<template>
  <div class="flex h-full flex-col gap-8 p-4">
    <NuxtLink
      to="/admin"
      aria-label="StudyMart admin home"
      class="self-start rounded-md px-2 pt-2"
    >
      <StudyMartLogo caption="Admin" />
    </NuxtLink>

    <UNavigationMenu
      :items="items"
      orientation="vertical"
      aria-label="Admin navigation"
      :ui="{ link: 'px-3 py-2.5 text-sm', linkLeadingIcon: 'size-5' }"
    />

    <div class="mt-auto flex flex-col gap-3 border-t border-default pt-4">
      <div class="flex items-center justify-between px-2">
        <span class="text-xs font-medium text-muted">Appearance</span>
        <ColorModeToggle />
      </div>
      <SidebarAccount />
    </div>
  </div>
</template>
