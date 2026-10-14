<script setup lang="ts">
import StudyMartLogo from '~/components/StudyMartLogo.vue'

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
  <div class="px-5 pb-6 pt-7">
    <NuxtLink
      to="/admin"
      class="inline-flex max-w-full rounded-lg focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-ring"
      @click="emit('navigate')"
    >
      <StudyMartLogo admin />
    </NuxtLink>

    <nav
      aria-label="Admin navigation"
      class="mt-10 flex flex-col gap-3"
    >
      <NuxtLink
        v-for="link in links"
        :key="link.to"
        :to="link.to"
        :aria-current="isActive(link.to) ? 'page' : undefined"
        class="flex items-center gap-5 rounded-lg border px-4 py-[13px] text-[18px] leading-6 font-medium transition-colors focus-visible:outline-2 focus-visible:outline-ring"
        :class="
          isActive(link.to)
            ? 'border-[#cfe7d5] bg-[#edf8f0] text-primary'
            : 'border-transparent text-body hover:bg-surface-muted hover:text-primary'
        "
        @click="emit('navigate')"
      >
        <UIcon
          :name="link.icon"
          size="24"
          class="shrink-0"
          aria-hidden="true"
        />
        {{ link.label }}
      </NuxtLink>
    </nav>
  </div>
</template>
