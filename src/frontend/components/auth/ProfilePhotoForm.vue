<script setup lang="ts">
  const props = defineProps<{ disabled?: boolean }>()
  const emit = defineEmits<{ busy: [value: boolean] }>()
  const { user, updateAvatar } = useAuth()
  const notification = useActionToast()
  const input = ref<HTMLInputElement | null>(null)
  const inputId = useId()
  const selected = shallowRef<File | null>(null)
  const selectionError = ref('')
  const preview = ref('')
  const saving = ref(false)
  const initials = computed(() =>
    (user.value?.name || '')
      .trim()
      .split(/\s+/)
      .slice(0, 2)
      .map((part) => part[0])
      .join('')
      .toUpperCase()
  )

  function clearSelection() {
    selectionError.value = ''
    if (preview.value) URL.revokeObjectURL(preview.value)
    preview.value = ''
    selected.value = null
  }

  function choosePhoto(event: Event) {
    const field = event.target as HTMLInputElement
    const file = field.files?.[0]
    field.value = ''
    if (!file || props.disabled || saving.value) return
    clearSelection()
    if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
      selectionError.value = 'Please choose a JPG, PNG or WebP image.'
      return
    }
    if (file.size > 2 * 1024 * 1024) {
      selectionError.value = 'Your photo must be 2 MB or smaller.'
      return
    }
    selected.value = file
    preview.value = URL.createObjectURL(file)
  }

  function cancelSelection() {
    clearSelection()
  }

  async function savePhoto(remove = false) {
    if (props.disabled || saving.value || (!remove && !selected.value)) return
    saving.value = true
    emit('busy', true)
    try {
      await updateAvatar(remove ? null : selected.value!)
      clearSelection()
      notification.success(
        remove ? 'Profile photo removed' : 'Profile photo updated',
        remove ? 'Your initials will appear as your avatar.' : 'Your new photo has been saved.'
      )
    } catch (error: unknown) {
      const failure = error as { statusCode?: number; data?: { errors?: Record<string, string[]> } }
      const message =
        failure.data?.errors?.avatar?.[0] ||
        (failure.statusCode === 413
          ? 'Your photo must be 2 MB or smaller.'
          : failure.statusCode === 429
            ? 'Too many attempts. Please wait a minute and try again.'
            : 'We could not update your photo. Please try again.')
      notification.error('Profile photo was not updated', message)
    } finally {
      saving.value = false
      emit('busy', false)
    }
  }

  onBeforeUnmount(clearSelection)
</script>

<template>
  <section
    aria-labelledby="profile-photo-title"
    class="grid gap-6 rounded-2xl border border-border bg-surface p-6 md:grid-cols-[1fr_1.3fr] md:gap-10"
  >
    <div>
      <h2
        id="profile-photo-title"
        class="text-lg font-semibold text-heading"
      >
        Profile photo
      </h2>
      <p class="mt-2 text-sm leading-6 text-muted">Choose a photo for your account.</p>
    </div>
    <form
      method="post"
      action="/api/auth/avatar"
      enctype="multipart/form-data"
      class="space-y-4"
      :aria-busy="saving"
      @submit.prevent="savePhoto()"
    >
      <div class="flex flex-col items-start gap-5 xl:flex-row xl:items-center">
        <UAvatar
          :src="preview || user?.avatar_url || undefined"
          :alt="user?.name || 'Profile photo'"
          :text="initials"
          size="3xl"
          class="size-24 shrink-0 bg-primary-light text-primary"
          :ui="{ fallback: 'text-3xl font-semibold' }"
        />
        <div class="min-w-0 flex-1 space-y-3">
          <input
            :id="inputId"
            ref="input"
            name="avatar"
            type="file"
            accept="image/jpeg,image/png,image/webp"
            aria-label="Choose profile photo"
            :aria-invalid="selectionError ? true : undefined"
            :aria-describedby="selectionError ? inputId + '-error' : undefined"
            class="sr-only"
            :disabled="disabled || saving"
            @change="choosePhoto"
          />
          <div class="flex flex-wrap gap-3">
            <AppButton
              type="button"
              variant="secondary"
              :disabled="disabled || saving"
              @click="input?.click()"
            >
              <UIcon
                name="i-lucide-image-plus"
                aria-hidden="true"
              />
              {{ user?.avatar_url ? 'Change photo' : 'Choose photo' }}
            </AppButton>
            <AppButton
              v-if="user?.avatar_url && !selected"
              type="button"
              variant="secondary"
              class="text-danger"
              :disabled="disabled || saving"
              @click="savePhoto(true)"
            >
              Remove photo
            </AppButton>
          </div>
          <p class="text-xs leading-5 text-muted">
            JPG, PNG or WebP. Max 2 MB, up to 4096 x 4096 pixels.
          </p>
          <p
            v-if="selectionError"
            :id="inputId + '-error'"
            class="text-sm text-danger"
            role="alert"
          >
            {{ selectionError }}
          </p>
        </div>
      </div>
      <div
        v-if="selected"
        class="space-y-3"
      >
        <p class="break-all text-sm text-muted">{{ selected.name }}</p>
        <div class="flex flex-wrap gap-3">
          <button
            type="submit"
            class="
              inline-flex items-center justify-center gap-2.5 min-h-12 py-3 px-5.5 rounded-lg bg-primary
              text-white font-semibold cursor-pointer
              transition-[background,border-color,color,box-shadow,transform] duration-150
              hover:bg-primary-hover active:translate-y-px disabled:opacity-60 disabled:cursor-wait
              focus-visible:outline-2 focus-visible:outline-solid focus-visible:outline-primary
              focus-visible:outline-offset-4 text-sm
            "
            :disabled="disabled || saving"
          >
            {{ saving ? 'Saving photo...' : 'Save photo' }}
          </button>
          <AppButton
            type="button"
            variant="secondary"
            :disabled="disabled || saving"
            @click="cancelSelection"
          >
            Cancel
          </AppButton>
        </div>
      </div>
    </form>
  </section>
</template>
