<script setup lang="ts">
  import StudyMartLogo from '~/components/StudyMartLogo.vue'
  import SidebarAccount from '~/components/admin/SidebarAccount.vue'

  const emit = defineEmits<{
    navigate: []
  }>()

  const route = useRoute()
  const links = [
    { label: 'Dashboard', to: '/admin', icon: 'i-lucide-layout-dashboard' },
    { label: 'Products', to: '/admin/products', icon: 'i-lucide-package' },
    { label: 'Categories', to: '/admin/categories', icon: 'i-lucide-tags' }
  ]

  const isActive = (to: string) =>
    route.path === to ||
    (to !== '/admin' && route.path.startsWith(`${to}/`)) ||
    (to === '/admin/products' && route.path.startsWith('/products/'))
</script>

<template>
  <div class="workspace-navigation">
    <NuxtLink
      to="/admin"
      class="workspace-navigation__brand"
      @click="emit('navigate')"
    >
      <StudyMartLogo
        admin
        inverse
      />
    </NuxtLink>

    <nav
      aria-label="Admin navigation"
      class="workspace-nav"
    >
      <NuxtLink
        v-for="link in links"
        :key="link.to"
        :to="link.to"
        :aria-current="isActive(link.to) ? 'page' : undefined"
        class="workspace-nav__link"
        :class="isActive(link.to) ? 'workspace-nav__link--active' : ''"
        @click="emit('navigate')"
      >
        <UIcon
          :name="link.icon"
          size="18"
          class="shrink-0"
          aria-hidden="true"
        />
        {{ link.label }}
      </NuxtLink>
    </nav>
    <div class="workspace-navigation__account">
      <SidebarAccount @navigate="emit('navigate')" />
    </div>
  </div>
</template>
