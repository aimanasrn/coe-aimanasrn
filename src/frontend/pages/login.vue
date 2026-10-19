<script setup lang="ts">
  import AuthScene from '~/components/auth/AuthScene.vue'
  import AuthField from '~/components/auth/AuthField.vue'
  import { authDestination } from '~/utils/auth'

  definePageMeta({ layout: false, colorMode: 'light' })
  useHead({ title: 'Log in · StudyMart' })

  const route = useRoute()
  const ready = ref(false)
  onMounted(() => {
    ready.value = true
  })
  const { login } = useAuth()
  const notification = useActionToast()
  const email = ref('')
  const password = ref('')
  const submitting = ref(false)

  async function submit() {
    if (submitting.value) return
    submitting.value = true
    try {
      const account = await login(email.value.trim(), password.value)
      password.value = ''
      if (account) {
        notification.success('Logged in', 'Welcome back to StudyMart.')
        await navigateTo(authDestination(route.query.redirect, account.role), { replace: true })
      }
    } catch (error: unknown) {
      const failure = error as { statusCode?: number }
      const message =
        failure.statusCode === 401
          ? 'The email or password is incorrect. Please try again.'
          : failure.statusCode === 429
            ? 'Too many login attempts. Please wait a minute and try again.'
            : 'We couldn’t log you in. Please try again.'
      notification.error('Could not log in', message)
    } finally {
      submitting.value = false
    }
  }
</script>

<template>
  <AuthScene
    title="Welcome back"
    description="Log in to your StudyMart account."
  >
    <form
      method="post"
      action="/api/auth/login"
      class="auth-form"
      :aria-busy="submitting"
      @submit.prevent="submit"
    >
      <AuthField
        id="email"
        v-model="email"
        label="Email address"
        type="email"
        autocomplete="username"
        placeholder="Enter your email"
        icon="i-lucide-mail"
        :maxlength="254"
        :disabled="submitting || !ready"
      />
      <AuthField
        id="password"
        v-model="password"
        label="Password"
        type="password"
        autocomplete="current-password"
        placeholder="Enter your password"
        icon="i-lucide-lock-keyhole"
        :disabled="submitting || !ready"
      />

      <button
        type="submit"
        :disabled="submitting || !ready"
        class="account-button auth-submit"
      >
        <UIcon
          v-if="submitting"
          name="i-lucide-loader-circle"
          class="animate-spin motion-reduce:animate-none"
          aria-hidden="true"
        />
        {{ submitting ? 'Logging in...' : 'Log in' }}
        <UIcon
          v-if="!submitting"
          name="i-lucide-arrow-right"
          aria-hidden="true"
        />
      </button>
    </form>
    <p class="auth-switch">
      New to StudyMart? <NuxtLink to="/register">Create an account</NuxtLink>
    </p>
  </AuthScene>
</template>
