<script setup lang="ts">
  import AuthScene from '~/components/auth/AuthScene.vue'
  import AuthField from '~/components/auth/AuthField.vue'
  import { authFormError } from '~/utils/auth'

  definePageMeta({ layout: false, colorMode: 'light' })
  useHead({ title: 'Create an account · StudyMart' })
  const { register } = useAuth()
  const notification = useActionToast()
  const ready = ref(false)
  onMounted(() => {
    ready.value = true
  })
  const name = ref('')
  const email = ref('')
  const password = ref('')
  const confirmation = ref('')
  const submitting = ref(false)
  const errors = ref<Record<string, string[]>>({})

  async function submit() {
    if (submitting.value) return
    submitting.value = true
    errors.value = {}
    try {
      await register({
        name: name.value.trim(),
        email: email.value.trim(),
        password: password.value,
        password_confirmation: confirmation.value
      })
      password.value = ''
      confirmation.value = ''
      notification.success('Account created', 'Welcome to StudyMart. Your account is ready.')
      await navigateTo('/shop', { replace: true })
    } catch (error: unknown) {
      const failure = authFormError(error)
      notification.error('Account was not created', failure.message)
      errors.value = failure.errors
    } finally {
      submitting.value = false
    }
  }
</script>

<template>
  <AuthScene
    title="Create your account"
    description="A place for your StudyMart details."
    compact
  >
    <form
      method="post"
      action="/api/auth/register"
      class="auth-form"
      :aria-busy="submitting"
      @submit.prevent="submit"
    >
      <AuthField
        id="name"
        v-model="name"
        label="Full name"
        autocomplete="name"
        placeholder="Enter your name"
        icon="i-lucide-user-round"
        :maxlength="100"
        :disabled="submitting || !ready"
        :error="errors.name?.[0]"
      />
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
        :error="errors.email?.[0]"
      />
      <AuthField
        id="password"
        v-model="password"
        label="Password"
        type="password"
        autocomplete="new-password"
        placeholder="Create a password"
        icon="i-lucide-lock-keyhole"
        :minlength="8"
        :maxlength="72"
        hint="Use at least 8 characters."
        :disabled="submitting || !ready"
        :error="errors.password?.[0]"
      />
      <AuthField
        id="password_confirmation"
        v-model="confirmation"
        label="Confirm password"
        type="password"
        autocomplete="new-password"
        placeholder="Enter your password again"
        icon="i-lucide-lock-keyhole"
        :maxlength="72"
        :disabled="submitting || !ready"
        :error="errors.password_confirmation?.[0]"
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
        {{ submitting ? 'Creating account...' : 'Create account' }}
      </button>
    </form>
    <p class="auth-switch">Already have an account? <NuxtLink to="/login">Log in</NuxtLink></p>
  </AuthScene>
</template>
