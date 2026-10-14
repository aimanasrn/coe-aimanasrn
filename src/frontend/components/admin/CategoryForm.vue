<script setup lang="ts">
import type { FormError, FormErrorEvent } from '@nuxt/ui'
import type { Category, CategoryResponse } from '~/types/catalog'

const props = defineProps<{ category?: Category }>()
const emit = defineEmits<{
  saved: [category: Category]
  cancel: []
  busy: [value: boolean]
}>()

const config = useRuntimeConfig()
const notification = useActionToast()
const categoryForm = useTemplateRef('categoryForm')
const form = reactive({
  name: props.category?.name ?? '',
  description: props.category?.description ?? ''
})
const saving = ref(false)
const message = ref('')

function validate(): FormError[] {
  const errors: FormError[] = []
  if (!form.name.trim())
    errors.push({ name: 'name', message: 'Please fill in this field.' })
  else if (form.name.trim().length > 100)
    errors.push({
      name: 'name',
      message: 'Please use 100 characters or fewer.'
    })
  if (form.description.trim().length > 5000)
    errors.push({
      name: 'description',
      message: 'Please use 5,000 characters or fewer.'
    })
  return errors
}

function invalid(event: FormErrorEvent) {
  notification.warning(
    'Please check the form',
    'Complete the category name and correct the highlighted errors.'
  )
  const id = event.errors[0]?.id
  if (id) document.getElementById(id)?.focus()
}

async function submit() {
  if (saving.value) return
  saving.value = true
  emit('busy', true)
  message.value = ''

  try {
    const response = await $fetch<CategoryResponse>(
      props.category ? `/categories/${props.category.id}` : '/categories',
      {
        baseURL: config.public.apiBase,
        method: props.category ? 'PUT' : 'POST',
        body: {
          name: form.name.trim(),
          description: form.description.trim() || null
        }
      }
    )
    notification.success(
      props.category ? 'Category updated' : 'Category added',
      `${response.data.name} has been ${props.category ? 'updated' : 'added to your catalog'}.`
    )
    emit('saved', response.data)
  } catch (error: unknown) {
    const response = error as { data?: { errors?: Record<string, string[]> } }
    const errors = Object.entries(response.data?.errors ?? {}).flatMap(
      ([name, messages]) =>
        messages[0] ? [{ name, message: messages[0] }] : []
    )
    categoryForm.value?.setErrors(errors)
    message.value = errors.length
      ? 'Please check the highlighted fields.'
      : 'We could not save this category. Please try again.'
    if (errors.length) {
      notification.warning('Please check the form', message.value)
    } else {
      notification.error('Category was not saved', message.value)
    }
  } finally {
    saving.value = false
    emit('busy', false)
  }
}
</script>

<template>
  <UForm
    ref="categoryForm"
    :state="form"
    :validate="validate"
    :validate-on="['blur', 'change', 'input']"
    :disabled="saving"
    class="sm-admin-form"
    novalidate
    @submit="submit"
    @error="invalid"
  >
    <p
      v-if="message"
      class="sm-admin-notice sm-admin-notice-error"
      role="alert"
    >
      {{ message }}
    </p>
    <UFormField
      name="name"
      label="Category name"
      required
      class="sm-admin-field"
    >
      <UInput
        id="category-name"
        v-model="form.name"
        required
        :maxlength="100"
        autofocus
        class="w-full"
      />
    </UFormField>
    <UFormField
      name="description"
      class="sm-admin-field"
    >
      <template #label>
        Description
        <span>(optional)</span>
      </template>
      <UTextarea
        id="category-description"
        v-model="form.description"
        :rows="4"
        :maxlength="5000"
        class="w-full"
      />
    </UFormField>
    <div class="sm-admin-form-actions">
      <button
        type="button"
        class="sm-admin-button-secondary"
        :disabled="saving"
        @click="emit('cancel')"
      >
        Cancel
      </button>
      <button
        type="submit"
        class="sm-admin-button"
        :disabled="saving"
      >
        {{
          saving
            ? 'Saving category...'
            : category
              ? 'Save changes'
              : 'Save category'
        }}
      </button>
    </div>
  </UForm>
</template>
