<script setup lang="ts">
  import AuthField from '~/components/auth/AuthField.vue'
  import ProfilePhotoForm from '~/components/auth/ProfilePhotoForm.vue'
  import { authFormError } from '~/utils/auth'

  definePageMeta({ layout: false, colorMode: 'light' })
  useHead({ title: 'My profile · StudyMart' })
  const route = useRoute()
  const ready = ref(false)
  onMounted(() => {
    ready.value = true
  })
  const { user, updateProfile } = useAuth()
  const notification = useActionToast()
  const name = ref('')
  const email = ref('')
  const emailPassword = ref('')
  const currentPassword = ref('')
  const password = ref('')
  const confirmation = ref('')
  const saving = ref<'details' | 'password' | 'photo' | null>(null)
  const detailsErrors = ref<Record<string, string[]>>({})
  const passwordErrors = ref<Record<string, string[]>>({})
  const changingEmail = computed(() => email.value.trim().toLowerCase() !== user.value?.email)
  const isAdmin = computed(() => user.value?.role === 'admin')

  watch(
    () => user.value?.name,
    (value) => {
      name.value = value || ''
    },
    { immediate: true }
  )
  watch(
    () => user.value?.email,
    (value) => {
      email.value = value || ''
    },
    { immediate: true }
  )

  async function saveDetails() {
    if (saving.value) return
    saving.value = 'details'
    detailsErrors.value = {}
    try {
      const details: Record<string, string> = { name: name.value.trim(), email: email.value.trim() }
      if (changingEmail.value) details.current_password = emailPassword.value
      await updateProfile(details)
      emailPassword.value = ''
      notification.success('Profile updated', 'Your personal information has been saved.')
    } catch (error: unknown) {
      const failure = authFormError(error)
      notification.error('Profile was not updated', failure.message)
      detailsErrors.value = failure.errors
    } finally {
      saving.value = null
    }
  }

  async function savePassword() {
    if (saving.value) return
    saving.value = 'password'
    passwordErrors.value = {}
    try {
      await updateProfile({
        current_password: currentPassword.value,
        password: password.value,
        password_confirmation: confirmation.value
      })
      currentPassword.value = ''
      password.value = ''
      confirmation.value = ''
      notification.success('Password updated', 'Your other sessions have been signed out.')
    } catch (error: unknown) {
      const failure = authFormError(error)
      notification.error('Password was not updated', failure.message)
      passwordErrors.value = failure.errors
    } finally {
      saving.value = null
    }
  }
</script>

<template>
  <div class="account-profile">
    <section>
      <div class="mb-8 flex flex-wrap items-start justify-between gap-4">
        <div>
          <h1 class="text-[32px] font-semibold tracking-tight text-heading">My profile</h1>
          <p class="mt-2 text-sm leading-6 text-muted">
            Manage your personal details and account security.
          </p>
        </div>
        <span
          class="mt-2 inline-flex items-center gap-1.5 rounded-full border border-[#cfe7d5] bg-[#edf8f0] px-3 py-1 text-xs font-medium text-primary"
          ><UIcon
            :name="isAdmin ? 'i-lucide-shield-check' : 'i-lucide-user-round'"
            class="size-3.5"
            aria-hidden="true"
          />{{ isAdmin ? 'Admin' : 'Customer' }}</span
        >
      </div>
      <p
        v-if="route.query.access === 'restricted'"
        role="status"
        class="mb-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm leading-6 text-amber-900"
      >
        Catalog management is available to admins. You can manage your account here.
      </p>

      <ProfilePhotoForm
        class="mb-6"
        :disabled="!!saving || !ready"
        @busy="saving = $event ? 'photo' : null"
      />

      <section
        aria-labelledby="details-title"
        class="grid gap-6 rounded-2xl border border-border bg-surface p-6 sm:p-6 md:grid-cols-[1fr_1.3fr] md:gap-10"
      >
        <div>
          <h2
            id="details-title"
            class="text-lg font-semibold text-heading"
          >
            Personal information
          </h2>
          <p class="mt-2 text-sm leading-6 text-muted">
            Keep your name and email address up to date.
          </p>
        </div>
        <form
          method="post"
          class="space-y-5"
          :aria-busy="saving === 'details'"
          @submit.prevent="saveDetails"
        >
          <AuthField
            id="profile-name"
            v-model="name"
            label="Full name"
            autocomplete="name"
            :maxlength="100"
            :disabled="!!saving || !ready"
            :error="detailsErrors.name?.[0]"
          />
          <AuthField
            id="profile-email"
            v-model="email"
            label="Email address"
            type="email"
            autocomplete="email"
            :maxlength="254"
            :disabled="!!saving || !ready"
            :error="detailsErrors.email?.[0]"
          />
          <AuthField
            v-if="changingEmail"
            id="email-current-password"
            v-model="emailPassword"
            label="Current password"
            type="password"
            autocomplete="current-password"
            hint="Confirm your password to change your email address."
            :disabled="!!saving || !ready"
            :error="detailsErrors.current_password?.[0]"
          />

          <button
            type="submit"
            :disabled="!!saving || !ready"
            class="account-button text-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-primary"
          >
            {{ saving === 'details' ? 'Saving…' : 'Save changes' }}
          </button>
        </form>
      </section>

      <section
        aria-labelledby="password-title"
        class="mt-6 grid gap-6 rounded-2xl border border-border bg-surface p-6 sm:p-6 md:grid-cols-[1fr_1.3fr] md:gap-10"
      >
        <div>
          <h2
            id="password-title"
            class="text-lg font-semibold text-heading"
          >
            Change password
          </h2>
          <p class="mt-2 text-sm leading-6 text-muted">
            Choose a password with at least 8 characters. Changing it signs out your other sessions.
          </p>
        </div>
        <form
          method="post"
          class="space-y-5"
          :aria-busy="saving === 'password'"
          @submit.prevent="savePassword"
        >
          <AuthField
            id="current-password"
            v-model="currentPassword"
            label="Current password"
            type="password"
            autocomplete="current-password"
            :disabled="!!saving || !ready"
            :error="passwordErrors.current_password?.[0]"
          />
          <AuthField
            id="new-password"
            v-model="password"
            label="New password"
            type="password"
            autocomplete="new-password"
            :minlength="8"
            :maxlength="72"
            :disabled="!!saving || !ready"
            :error="passwordErrors.password?.[0]"
          />
          <AuthField
            id="confirm-new-password"
            v-model="confirmation"
            label="Confirm new password"
            type="password"
            autocomplete="new-password"
            :maxlength="72"
            :disabled="!!saving || !ready"
            :error="passwordErrors.password_confirmation?.[0]"
          />

          <button
            type="submit"
            :disabled="!!saving || !ready"
            class="account-button text-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-primary"
          >
            {{ saving === 'password' ? 'Updating…' : 'Update password' }}
          </button>
        </form>
      </section>
    </section>
  </div>
</template>
