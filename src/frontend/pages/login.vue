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
      class="grid gap-5.5"
      :aria-busy="submitting"
      @submit.prevent="submit"
    >
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
      />
      <AuthField
        variant="auth"
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
        {{ submitting ? 'Logging in...' : 'Log in' }}
        <UIcon
          class="w-5 h-5"
          v-if="!submitting"
          name="i-lucide-arrow-right"
          aria-hidden="true"
        />
      </button>
    </form>
    <p class="mt-6.5 text-muted text-sm leading-[1.6] max-[761px]:text-sm">
      New to StudyMart?
      <NuxtLink
        class="
          ml-[5px] text-primary font-semibold underline underline-offset-3 hover:text-brand-deep
          max-[361px]:ml-0.5
        "
        to="/register"
        >Create an account</NuxtLink
      >
    </p>
  </AuthScene>
</template>
