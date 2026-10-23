<script setup lang="ts">
  import StudyMartLogo from '~/components/StudyMartLogo.vue'
  const { user, logout } = useAuth()
  const { cart } = useCart()
  const cartCount = computed(() => cart.value?.item_count ?? 0)
  const route = useRoute()
  const notification = useActionToast()
  const loggingOut = ref(false)
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
  const items = computed(() => [
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
  <header class="shop-header">
    <div class="shop-header__inner">
      <NuxtLink
        to="/shop"
        aria-label="StudyMart shop"
        class="shop-brand"
        ><StudyMartLogo
      /></NuxtLink>
      <nav
        aria-label="Customer navigation"
        class="shop-links"
      >
        <NuxtLink
          to="/shop"
          :aria-current="route.path.startsWith('/shop') ? 'page' : undefined"
          >Shop</NuxtLink
        >
        <NuxtLink
          to="/cart"
          :aria-current="route.path === '/cart' ? 'page' : undefined"
          class="shop-cart-link"
          :aria-label="'Cart, ' + cartCount + (cartCount === 1 ? ' item' : ' items')"
        >
          <UChip
            :text="cartCount"
            color="primary"
            size="3xl"
            :ui="{ base: 'h-5 min-w-5 px-1 text-[11px] font-semibold' }"
          >
            <UIcon
              name="i-lucide-shopping-cart"
              class="size-5"
              aria-hidden="true"
            />
          </UChip>
        </NuxtLink>
      </nav>
      <UDropdownMenu
        :items="items"
        :content="{ align: 'end' }"
      >
        <button
          type="button"
          aria-label="Account menu"
          class="shop-account"
          :disabled="loggingOut"
        >
          <UAvatar
            :src="user?.avatar_url || undefined"
            :alt="user?.name || 'Your account'"
            size="xl"
            class="bg-primary-light text-primary"
          />
          <UIcon
            name="i-lucide-chevron-down"
            aria-hidden="true"
            class="size-4"
          />
        </button>
      </UDropdownMenu>
    </div>
  </header>
</template>
