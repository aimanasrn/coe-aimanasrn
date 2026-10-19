import type { AuthUser } from '~/types/auth'

export function useAuth() {
  const user = useState<AuthUser | null>('auth-user', () => null)
  const requestFetch = useRequestFetch()
  const nuxtApp = useNuxtApp()

  function clearUser() {
    nuxtApp.runWithContext(() => {
      useState('shopping-cart-' + user.value?.id).value = null
      useState('cart-error-' + user.value?.id).value = ''
      useState('cart-busy-' + user.value?.id).value = false
      user.value = null
    })
  }

  async function fetchUser() {
    try {
      const response = await requestFetch<{ data: AuthUser }>('/api/auth/me')
      user.value = response.data
      return user.value
    } catch (error: unknown) {
      if ((error as { statusCode?: number }).statusCode !== 401) throw error
      clearUser()
      return null
    }
  }

  async function login(email: string, password: string) {
    await $fetch('/api/auth/login', { method: 'POST', body: { email, password }, retry: 0 })
    return await fetchUser()
  }

  async function register(details: {
    name: string
    email: string
    password: string
    password_confirmation: string
  }) {
    const response = await $fetch<{ data: AuthUser }>('/api/auth/register', {
      method: 'POST',
      body: details,
      retry: 0
    })
    user.value = response.data
    return user.value
  }

  async function updateProfile(details: Record<string, string>) {
    try {
      const response = await $fetch<{ data: AuthUser }>('/api/auth/profile', {
        method: 'PATCH',
        body: details,
        retry: 0
      })
      user.value = response.data
      return user.value
    } catch (error: unknown) {
      if ((error as { statusCode?: number }).statusCode === 401) {
        clearUser()
        await navigateTo('/login', { replace: true })
      }
      throw error
    }
  }

  async function updateAvatar(photo: File | null) {
    const body = photo ? new FormData() : undefined
    if (photo) body!.append('avatar', photo)
    try {
      const response = await $fetch<{ data: AuthUser }>('/api/auth/avatar', {
        method: photo ? 'POST' : 'DELETE',
        body,
        retry: 0
      })
      user.value = response.data
      return user.value
    } catch (error: unknown) {
      if ((error as { statusCode?: number }).statusCode === 401) {
        clearUser()
        await navigateTo('/login', { replace: true })
      }
      throw error
    }
  }

  async function logout() {
    await $fetch('/api/auth/logout', { method: 'POST', retry: 0 })
    clearUser()
    clearNuxtData()
    await navigateTo('/login', { replace: true })
  }

  return { user, fetchUser, login, register, updateProfile, updateAvatar, logout }
}
