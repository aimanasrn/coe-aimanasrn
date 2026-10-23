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
      class="grid gap-5.5"
      :aria-busy="submitting"
      @submit.prevent="submit"
    >
      <AuthField
        variant="auth"
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
        variant="auth"
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
        variant="auth"
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
        variant="auth"
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
        class="
          inline-flex items-center justify-center gap-2.5 py-3 px-5.5 rounded-lg bg-primary text-white
          font-semibold cursor-pointer transition-[background,border-color,color,box-shadow,transform]
          duration-150 hover:bg-primary-hover active:translate-y-px disabled:opacity-60
          disabled:cursor-wait focus-visible:outline-2 focus-visible:outline-solid
          focus-visible:outline-primary focus-visible:outline-offset-4 w-full min-h-12.5 mt-1
          text-base max-[761px]:min-h-12
        "
      >
        <UIcon
          v-if="submitting"
          name="i-lucide-loader-circle"
          class="animate-spin motion-reduce:animate-none w-5 h-5"
          aria-hidden="true"
        />
        {{ submitting ? 'Creating account...' : 'Create account' }}
      </button>
    </form>
    <p class="mt-6.5 text-muted text-sm leading-[1.6] max-[761px]:text-sm">
      Already have an account?
      <NuxtLink
        class="
          ml-[5px] text-primary font-semibold underline underline-offset-3 hover:text-brand-deep
          max-[361px]:ml-0.5
        "
        to="/login"
        >Log in</NuxtLink
      >
    </p>
  </AuthScene>
</template>
