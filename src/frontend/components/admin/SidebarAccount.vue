<script setup lang="ts">
  import type { DropdownMenuItem } from '@nuxt/ui'

  const { user, logout } = useAuth()
  const notification = useActionToast()
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
  <UDropdownMenu
    v-if="user"
    :items="items"
    :disabled="loggingOut"
    :content="{ side: 'top', align: 'start', sideOffset: 8 }"
    :ui="{ content: 'w-(--reka-dropdown-menu-trigger-width) min-w-56' }"
  >
    <UButton
      color="neutral"
      variant="ghost"
      block
      class="justify-start gap-3 px-2 py-2"
      aria-label="Account menu"
      :loading="loggingOut"
    >
      <UAvatar
        :src="user.avatar_url || undefined"
        :alt="user.name"
        :text="initials"
        size="md"
      />
      <span class="min-w-0 flex-1 text-left">
        <span class="block truncate text-sm font-semibold text-highlighted">{{ user.name }}</span>
        <span class="block truncate text-xs font-normal text-muted">{{ user.email }}</span>
      </span>
      <UIcon
        name="i-lucide-chevrons-up-down"
        class="size-4 flex-none text-dimmed"
        aria-hidden="true"
      />
    </UButton>
  </UDropdownMenu>
</template>
