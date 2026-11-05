<script setup lang="ts">
  import type { DropdownMenuItem } from '@nuxt/ui'
  import { useAuth } from '~/composables/useAuth'
  import { useCart } from '~/composables/useCart'

  const { user, logout } = useAuth()
  const { cart } = useCart()
  const route = useRoute()
  const notification = useActionToast()
  const loggingOut = ref(false)
  const cartCount = computed(() => cart.value?.item_count ?? 0)
  const onShop = computed(() => route.path.startsWith('/shop'))
  const onCart = computed(() => route.path === '/cart')

  async function signOut() {
    if (loggingOut.value) return
    loggingOut.value = true
    try {
      await logout()
      notification.success('Logged out', 'You have been signed out of your account.')
    } catch {
      notification.error('Could not log out', 'Please try again.')
    } finally {
      loggingOut.value = false
    }
  }

  const items = computed<DropdownMenuItem[][]>(() => [
    [{ type: 'label', label: user.value?.name || 'Your account' }],
    [{ label: 'Profile settings', icon: 'i-lucide-user-round', to: '/profile' }],
    [
      {
        label: loggingOut.value ? 'Logging out...' : 'Log out',
        icon: 'i-lucide-log-out',
        disabled: loggingOut.value,
        onSelect: () => {
          void signOut()
        }
      }
    ]
  ])
</script>

<template>
  <header class="sticky top-0 z-30 border-b border-default bg-default/90 backdrop-blur">
    <UContainer class="flex h-16 items-center gap-2 sm:gap-4">
      <NuxtLink
        to="/shop"
        aria-label="StudyMart shop"
        class="mr-auto rounded-md"
      >
        <StudyMartLogo />
      </NuxtLink>

      <nav
        aria-label="Customer navigation"
        class="flex items-center gap-1"
      >
        <UButton
          to="/shop"
          label="Shop"
          icon="i-lucide-store"
          color="neutral"
          variant="ghost"
          :active="onShop"
          active-color="primary"
          active-variant="soft"
          :aria-current="onShop ? 'page' : undefined"
          :ui="{ label: 'max-sm:hidden' }"
        />
        <UChip
          :text="cartCount"
          :show="cartCount > 0"
          size="3xl"
          inset
        >
          <UButton
            to="/cart"
            icon="i-lucide-shopping-cart"
            color="neutral"
            variant="ghost"
            :active="onCart"
            active-color="primary"
            active-variant="soft"
            :aria-current="onCart ? 'page' : undefined"
            :aria-label="`Cart, ${cartCount} ${cartCount === 1 ? 'item' : 'items'}`"
          />
        </UChip>
      </nav>

      <ColorModeToggle />

      <UDropdownMenu
        :items="items"
        :content="{ align: 'end' }"
      >
        <UButton
          color="neutral"
          variant="ghost"
          trailing-icon="i-lucide-chevron-down"
          aria-label="Account menu"
          class="rounded-full p-1"
          :disabled="loggingOut"
        >
          <UAvatar
            :src="user?.avatar_url || undefined"
            :alt="user?.name || 'Your account'"
            size="sm"
          />
        </UButton>
      </UDropdownMenu>
    </UContainer>
  </header>
</template>
