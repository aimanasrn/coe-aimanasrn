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
    class="workspace-account"
  >
    <UDropdownMenu
      v-model:open="menuOpen"
      :items="items"
      :disabled="loggingOut"
      :content="{ side: 'top', align: 'start', sideOffset: 8, collisionPadding: 12 }"
      :ui="{
        content: 'workspace-account-menu',
        item: 'workspace-account-menu__item',
        itemLeadingIcon: 'workspace-account-menu__icon',
        separator: 'my-1 bg-border'
      }"
    >
      <button
        type="button"
        aria-label="Account menu"
        :aria-busy="loggingOut"
        class="workspace-account__profile"
      >
        <UAvatar
          :src="user.avatar_url || undefined"
          :alt="user.name"
          :text="initials"
          size="xl"
          class="workspace-account__avatar"
          :ui="{ fallback: 'text-white font-semibold' }"
        />
        <span class="workspace-account__details">
          <span class="workspace-account__name">{{ user.name }} <span>Admin</span></span>
          <span
            class="workspace-account__email"
            :title="user.email"
            >{{ user.email }}</span
          >
        </span>
        <UIcon
          :name="loggingOut ? 'i-lucide-loader-circle' : 'i-lucide-chevrons-up-down'"
          class="workspace-account__chevron"
          :class="{ 'animate-spin motion-reduce:animate-none': loggingOut }"
          aria-hidden="true"
        />
      </button>
    </UDropdownMenu>
  </div>
</template>
