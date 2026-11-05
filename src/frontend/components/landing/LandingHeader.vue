<script setup lang="ts">
  import type { AuthUser } from '~/types/auth'

  const props = defineProps<{ user: AuthUser | null }>()

  const links = [
    { label: 'Categories', to: '#categories' },
    { label: 'Featured', to: '#featured' },
    { label: 'How it works', to: '#how-it-works' },
    { label: 'FAQ', to: '#faq' }
  ]
  const appLink = computed(() =>
    props.user?.role === 'admin'
      ? { label: 'Open dashboard', to: '/admin', icon: 'i-lucide-layout-dashboard' }
      : { label: 'Go to shop', to: '/shop', icon: 'i-lucide-store' }
  )

  const scrolled = ref(false)
  const menuOpen = ref(false)

  function onScroll() {
    scrolled.value = window.scrollY > 12
  }

  onMounted(() => {
    onScroll()
    window.addEventListener('scroll', onScroll, { passive: true })
  })
  onBeforeUnmount(() => window.removeEventListener('scroll', onScroll))
</script>

<template>
  <header
    class="fixed inset-x-0 top-0 z-40 transition-[background-color,border-color,backdrop-filter] duration-300"
    :class="
      scrolled
        ? 'border-b border-default bg-default/80 backdrop-blur-lg'
        : 'border-b border-transparent bg-transparent'
    "
  >
    <UContainer class="flex h-16 items-center gap-4">
      <NuxtLink
        to="/"
        aria-label="StudyMart home"
        class="mr-auto rounded-md"
      >
        <StudyMartLogo />
      </NuxtLink>

      <nav
        aria-label="Landing page sections"
        class="hidden items-center gap-1 md:flex"
      >
        <UButton
          v-for="link in links"
          :key="link.to"
          :to="link.to"
          :label="link.label"
          color="neutral"
          variant="ghost"
        />
      </nav>

      <div class="flex items-center gap-1 sm:gap-2">
        <ColorModeToggle />
        <template v-if="user">
          <UButton
            :to="appLink.to"
            :label="appLink.label"
            :trailing-icon="appLink.icon"
            class="max-sm:hidden"
          />
        </template>
        <template v-else>
          <UButton
            to="/login"
            label="Log in"
            color="neutral"
            variant="ghost"
            class="max-sm:hidden"
          />
          <UButton
            to="/register"
            label="Get started"
            class="max-sm:hidden"
          />
        </template>

        <USlideover
          v-model:open="menuOpen"
          side="right"
          title="StudyMart"
          description="Landing page navigation"
          :ui="{ content: 'max-w-xs' }"
        >
          <UButton
            icon="i-lucide-menu"
            color="neutral"
            variant="ghost"
            aria-label="Open menu"
            class="md:hidden"
          />
          <template #body>
            <nav
              aria-label="Mobile landing navigation"
              class="flex flex-col gap-1"
            >
              <UButton
                v-for="link in links"
                :key="link.to"
                :to="link.to"
                :label="link.label"
                color="neutral"
                variant="ghost"
                size="lg"
                block
                class="justify-start"
                @click="menuOpen = false"
              />
            </nav>
            <USeparator class="my-5" />
            <div class="flex flex-col gap-2">
              <UButton
                v-if="user"
                :to="appLink.to"
                :label="appLink.label"
                :trailing-icon="appLink.icon"
                size="lg"
                block
              />
              <template v-else>
                <UButton
                  to="/register"
                  label="Create free account"
                  size="lg"
                  block
                />
                <UButton
                  to="/login"
                  label="Log in"
                  color="neutral"
                  variant="outline"
                  size="lg"
                  block
                />
              </template>
            </div>
          </template>
        </USlideover>
      </div>
    </UContainer>
  </header>
</template>
