<script setup lang="ts">
import { ref } from 'vue'
import StudyMartLogo from '~/components/StudyMartLogo.vue'

const isMenuOpen = ref(false)

const links = [
  { label: 'Dashboard', to: '/admin' },
  { label: 'Products', to: '/admin/products' },
  { label: 'Categories', to: '/admin/categories' }
]
</script>

<template>
  <header
    class="flex items-center justify-between gap-3 border-b border-border bg-surface p-4 shadow-sm"
  >
    <!-- Brand -->
    <NuxtLink
      to="/admin"
      class="rounded-lg focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-ring"
    >
      <StudyMartLogo admin />
    </NuxtLink>

    <!-- Desktop navigation -->
    <nav
      aria-label="Admin navigation"
      class="hidden items-center gap-2 sm:flex"
    >
      <NuxtLink
        v-for="link in links"
        :key="link.to"
        :to="link.to"
        class="rounded-lg px-3 py-2 text-sm font-medium text-body transition-colors hover:bg-primary-light hover:text-primary focus-visible:outline-2 focus-visible:outline-ring"
      >
        {{ link.label }}
      </NuxtLink>
    </nav>

    <!-- Mobile drawer -->
    <UDrawer
      v-model:open="isMenuOpen"
      direction="right"
      title="StudyMart Admin"
      description="Admin navigation"
      :handle="false"
      :ui="{ content: 'w-72 max-w-[85vw] bg-surface' }"
    >
      <!-- Nuxt UI opens the drawer when this button is clicked -->
      <button
        type="button"
        aria-label="Open navigation"
        class="flex items-center justify-center rounded-lg p-2 text-heading hover:bg-surface-muted focus-visible:outline-2 focus-visible:outline-ring sm:hidden"
      >
        <UIcon
          name="i-lucide-menu"
          size="24"
          aria-hidden="true"
        />
      </button>

      <template #content>
        <div class="flex h-full w-full flex-col p-6">
          <!-- Drawer header -->
          <div
            class="flex items-center justify-between gap-3 border-b border-border pb-4"
          >
            <div class="flex items-center gap-2">
              <!-- <UIcon
                name="i-lucide-book-open"
                size="20"
                class="text-primary"
                aria-hidden="true"
              /> -->

              <h2 class="text-lg font-semibold text-primary">Menu</h2>
            </div>

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

          <!-- Drawer links -->
          <nav
            aria-label="Mobile admin navigation"
            class="mt-6 flex flex-col gap-2"
          >
            <NuxtLink
              v-for="link in links"
              :key="link.to"
              :to="link.to"
              class="rounded-lg px-4 py-3 font-medium text-body transition-colors hover:bg-primary-light hover:text-primary focus-visible:outline-2 focus-visible:outline-ring"
              @click="isMenuOpen = false"
            >
              {{ link.label }}
            </NuxtLink>
          </nav>
        </div>
      </template>
    </UDrawer>
  </header>
</template>
