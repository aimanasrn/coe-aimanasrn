<script setup lang="ts">
  import type { DropdownMenuItem } from '@nuxt/ui'

  const emit = defineEmits<{ navigate: [] }>()
  const { user, logout } = useAuth()
  const notification = useActionToast()
  const menuOpen = ref(false)
  const loggingOut = ref(false)
  const initials = computed(() =>
    (user.value?.name || 'Admin')
      .split(/\s+/)
      .slice(0, 2)
      .map((part) => part[0])
      .join('')
      .toUpperCase()
  )

  const items = computed<DropdownMenuItem[][]>(() => [
    [
      {
        label: 'Profile settings',
        icon: 'i-lucide-user-round',
        to: '/profile',
        onSelect: () => {
          emit('navigate')
        }
      }
    ],
    [
      {
        label: loggingOut.value ? 'Logging out...' : 'Log out',
        icon: 'i-lucide-log-out',
        loading: loggingOut.value,
        disabled: loggingOut.value,
        onSelect: () => {
          void signOut()
        }
      }
    ]
  ])

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
</script>

<template>
  <div
    v-if="user"
    class="pt-5 px-1 pb-0 border-t border-[#385b4c]"
  >
    <UDropdownMenu
      v-model:open="menuOpen"
      :items="items"
      :disabled="loggingOut"
      :content="{ side: 'top', align: 'start', sideOffset: 8, collisionPadding: 12 }"
      :ui="{
        content:
          'z-60 w-[var(--reka-dropdown-menu-trigger-width)] min-w-55 max-w-[calc(100vw_-_24px)] p-1.5 border border-border rounded-xl bg-white text-body shadow-[0_8px_28px_rgb(16_37_28_/_15%)]',
        item: [
          'flex items-center gap-2.5 min-h-11 py-2.5 px-3 rounded-[7px] text-body text-sm',
          'leading-normal cursor-pointer data-[highlighted]:bg-secondary',
          'data-[highlighted]:text-primary hover:bg-secondary hover:text-primary'
        ].join(' '),
        itemLeadingIcon: 'w-4.5 h-4.5 flex-none text-inherit',
        separator: 'my-1 bg-border'
      }"
    >
      <button
        type="button"
        aria-label="Account menu"
        :aria-busy="loggingOut"
        class="
          flex w-full min-w-0 items-center gap-2.5 py-2 px-1.5 rounded-lg text-left cursor-pointer
          transition-[background,border-color,color,box-shadow,transform] duration-150
          hover:bg-[#245642] aria-expanded:bg-[#245642]
        "
      >
        <UAvatar
          :src="user.avatar_url || undefined"
          :alt="user.name"
          :text="initials"
          size="xl"
          class="
            grid w-10 h-10 flex-none place-items-center rounded-full bg-[#2c654e] text-white text-sm
            font-semibold
          "
          :ui="{ fallback: 'text-white font-semibold' }"
        />
        <span class="flex-1 min-w-0">
          <span
            class="block overflow-hidden text-[#f5faf7] text-sm font-medium text-ellipsis whitespace-nowrap"
            >{{ user.name }}
            <span class="ml-[5px] text-[#bdcfc5] text-[12px] font-normal">Admin</span></span
          >
          <span
            class="block overflow-hidden mt-[5px] text-[#b9cdc1] text-[12px] text-ellipsis whitespace-nowrap"
            :title="user.email"
            >{{ user.email }}</span
          >
        </span>
        <UIcon
          :name="loggingOut ? 'i-lucide-loader-circle' : 'i-lucide-chevrons-up-down'"
          class="w-4 h-4 flex-none text-[#b9cdc1]"
          :class="{ 'animate-spin motion-reduce:animate-none': loggingOut }"
          aria-hidden="true"
        />
      </button>
    </UDropdownMenu>
  </div>
</template>
