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
  <div class="flex h-full min-h-120 flex-col pt-7.5 px-4 pb-5">
    <NuxtLink
      to="/admin"
      class="self-start mt-1 mx-2 mb-0 rounded-md"
      @click="emit('navigate')"
    >
      <StudyMartLogo
        admin
        inverse
      />
    </NuxtLink>

    <nav
      aria-label="Admin navigation"
      class="flex flex-col gap-[9px] mt-9"
    >
      <NuxtLink
        v-for="link in links"
        :key="link.to"
        :to="link.to"
        :aria-current="isActive(link.to) ? 'page' : undefined"
        class="
          flex items-center gap-3 min-h-12 py-3 px-3.5 rounded-lg text-[#d5e3dc] text-base font-medium
          leading-normal transition-[background,border-color,color,box-shadow,transform] duration-150
          hover:text-white hover:bg-[#1a4939] aria-[current=page]:bg-[#245642]
          aria-[current=page]:text-white
        "
        @click="emit('navigate')"
      >
        <UIcon
          :name="link.icon"
          size="18"
          class="w-[21px] h-[21px] flex-none"
          aria-hidden="true"
        />
        {{ link.label }}
      </NuxtLink>
    </nav>
    <div class="mt-auto pt-9">
      <SidebarAccount @navigate="emit('navigate')" />
    </div>
  </div>
</template>
