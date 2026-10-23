<script setup lang="ts">
  import StudyMartLogo from '~/components/StudyMartLogo.vue'
  import { useAuth } from '~/composables/useAuth'
  import { useCart } from '~/composables/useCart'
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
  <header class="bg-white border-b border-border">
    <div
      class="
        max-w-320 m-auto py-5.5 px-8 flex items-center gap-8 min-[1600px]:max-w-350 max-[761px]:py-4
        max-[761px]:px-5 max-[761px]:gap-5 max-[761px]:flex-wrap max-[481px]:gap-y-2
        max-[481px]:gap-x-4 max-[481px]:grid max-[481px]:grid-cols-[minmax(0,_1fr)_auto]
      "
    >
      <NuxtLink
        to="/shop"
        aria-label="StudyMart shop"
        class="
          mr-auto rounded-lg max-[761px]:flex-1 max-[761px]:[&_img]:max-w-[175px]
          max-[481px]:basis-[calc(100%_-_70px)] max-[481px]:order-0 max-[481px]:col-start-1
          max-[481px]:row-start-1 max-[481px]:mr-0
        "
        ><StudyMartLogo
      /></NuxtLink>
      <nav
        aria-label="Customer navigation"
        class="
          flex items-center gap-7.5 text-base font-semibold max-[761px]:gap-5 max-[481px]:order-2
          max-[481px]:w-full max-[481px]:gap-7 max-[481px]:col-span-full max-[481px]:row-start-2
          max-[481px]:mt-1
        "
      >
        <NuxtLink
          class="flex items-center gap-2 min-h-11 text-muted aria-[current=page]:text-primary"
          to="/shop"
          :aria-current="route.path.startsWith('/shop') ? 'page' : undefined"
          >Shop</NuxtLink
        >
        <NuxtLink
          to="/cart"
          :aria-current="route.path === '/cart' ? 'page' : undefined"
          class="
            justify-center min-w-11 flex items-center gap-2 min-h-11 text-muted
            aria-[current=page]:text-primary
          "
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
          class="
            flex items-center gap-2 p-1 rounded-[30px] cursor-pointer max-[761px]:ml-auto
            max-[481px]:order-1 max-[481px]:col-start-2 max-[481px]:row-start-1 max-[481px]:ml-0
            max-[481px]:justify-self-end
          "
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
