<script setup lang="ts">
  import AuthScene from '~/components/auth/AuthScene.vue'
  import AuthField from '~/components/auth/AuthField.vue'
  import { authFormError } from '~/utils/auth'

  definePageMeta({ layout: false })
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
    description="Join StudyMart to shop for your study essentials."
  >
    <form
      method="post"
      action="/api/auth/register"
      class="flex flex-col gap-4"
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
        placeholder="you@example.com"
        icon="i-lucide-mail"
        :maxlength="254"
        :disabled="submitting || !ready"
        :error="errors.email?.[0]"
      />
      <div class="grid gap-4 sm:grid-cols-2">
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
          hint="At least 8 characters."
          :disabled="submitting || !ready"
          :error="errors.password?.[0]"
        />
        <AuthField
          id="password_confirmation"
          v-model="confirmation"
          label="Confirm password"
          type="password"
          autocomplete="new-password"
          placeholder="Repeat password"
          icon="i-lucide-lock-keyhole"
          :maxlength="72"
          :disabled="submitting || !ready"
          :error="errors.password_confirmation?.[0]"
        />
      </div>
      <UButton
        type="submit"
        size="xl"
        block
        class="mt-2"
        :loading="submitting"
        :disabled="!ready"
        :label="submitting ? 'Creating account...' : 'Create account'"
      />
    </form>
    <p class="mt-8 text-sm text-muted">
      Already have an account?
      <ULink
        to="/login"
        class="font-semibold text-primary hover:underline"
        >Log in</ULink
      >
    </p>
  </AuthScene>
</template>
