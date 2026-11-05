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
  <UCard
    as="section"
    aria-labelledby="profile-photo-title"
  >
    <div class="grid gap-6 md:grid-cols-[1fr_1.4fr] md:gap-10">
      <div>
        <h2
          id="profile-photo-title"
          class="font-semibold text-highlighted"
        >
          Profile photo
        </h2>
        <p class="mt-1 text-sm text-muted">Choose a photo for your account.</p>
      </div>
      <form
        method="post"
        action="/api/auth/avatar"
        enctype="multipart/form-data"
        class="flex flex-col gap-4"
        :aria-busy="saving"
        @submit.prevent="savePhoto()"
      >
        <div class="flex flex-col items-start gap-5 sm:flex-row sm:items-center">
          <UAvatar
            :src="preview || user?.avatar_url || undefined"
            :alt="user?.name || 'Profile photo'"
            :text="initials"
            size="3xl"
            class="size-20 shrink-0 ring-4 ring-primary/10"
            :ui="{ fallback: 'text-2xl font-bold text-primary' }"
          />
          <div class="flex min-w-0 flex-1 flex-col gap-2">
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
            <div class="flex flex-wrap gap-2">
              <UButton
                icon="i-lucide-image-plus"
                :label="user?.avatar_url ? 'Change photo' : 'Choose photo'"
                color="neutral"
                variant="outline"
                :disabled="disabled || saving"
                @click="input?.click()"
              />
              <UButton
                v-if="user?.avatar_url && !selected"
                label="Remove photo"
                color="error"
                variant="ghost"
                :disabled="disabled || saving"
                @click="savePhoto(true)"
              />
            </div>
            <p class="text-xs text-muted">JPG, PNG or WebP. Max 2 MB, up to 4096 x 4096 pixels.</p>
            <p
              v-if="selectionError"
              :id="inputId + '-error'"
              class="text-sm text-error"
              role="alert"
            >
              {{ selectionError }}
            </p>
          </div>
        </div>
        <div
          v-if="selected"
          class="flex flex-col gap-3 rounded-lg border border-default bg-muted p-3"
        >
          <p class="text-sm break-all text-muted">{{ selected.name }}</p>
          <div class="flex flex-wrap gap-2">
            <UButton
              type="submit"
              :loading="saving"
              :disabled="disabled"
              :label="saving ? 'Saving photo...' : 'Save photo'"
            />
            <UButton
              label="Cancel"
              color="neutral"
              variant="outline"
              :disabled="disabled || saving"
              @click="clearSelection"
            />
          </div>
        </div>
      </form>
    </div>
  </UCard>
</template>
