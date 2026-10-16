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
    class="flex flex-col gap-5"
    novalidate
    @submit="submit"
    @error="invalid"
  >
    <p
      v-if="message"
      class="mb-5 rounded-[7px] border px-[18px] py-3.5 text-sm border-[#f0d5d5] bg-[#fff4f4] text-[#a03939]"
      role="alert"
    >
      {{ message }}
    </p>
    <UFormField
      name="name"
      label="Category name"
      required
      class="
        flex flex-col gap-[7px] [&_label]:text-sm [&_label]:font-[550] [&_label]:text-[#374f40]
        [&_label_span]:font-normal [&_label_span]:text-[#7b867f] [&_input]:min-h-11 [&_input]:w-full
        [&_input]:rounded-md [&_input]:border [&_input]:border-[#dce4df] [&_input]:bg-white [&_input]:px-3
        [&_input]:py-2.5 [&_input]:font-[inherit] [&_input]:text-[15px] [&_input]:text-[#37463f]
        [&_input]:focus:outline-2 [&_input]:focus:outline-primary [&_input]:focus:outline-offset-1
        [&_textarea]:min-h-11 [&_textarea]:w-full [&_textarea]:rounded-md [&_textarea]:border
        [&_textarea]:border-[#dce4df] [&_textarea]:bg-white [&_textarea]:px-3 [&_textarea]:py-2.5
        [&_textarea]:font-[inherit] [&_textarea]:text-[15px] [&_textarea]:text-[#37463f] [&_textarea]:focus:outline-2
        [&_textarea]:focus:outline-primary [&_textarea]:focus:outline-offset-1 [&_select]:min-h-11 [&_select]:w-full
        [&_select]:rounded-md [&_select]:border [&_select]:border-[#dce4df] [&_select]:bg-white [&_select]:px-3
        [&_select]:py-2.5 [&_select]:font-[inherit] [&_select]:text-[15px] [&_select]:text-[#37463f]
        [&_select]:focus:outline-2 [&_select]:focus:outline-primary [&_select]:focus:outline-offset-1
        [&_[aria-invalid=true]]:border-[#b83d3d] [&>p]:text-xs [&>p]:text-[#b83d3d]
      "
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
      class="
        flex flex-col gap-[7px] [&_label]:text-sm [&_label]:font-[550] [&_label]:text-[#374f40]
        [&_label_span]:font-normal [&_label_span]:text-[#7b867f] [&_input]:min-h-11 [&_input]:w-full
        [&_input]:rounded-md [&_input]:border [&_input]:border-[#dce4df] [&_input]:bg-white [&_input]:px-3
        [&_input]:py-2.5 [&_input]:font-[inherit] [&_input]:text-[15px] [&_input]:text-[#37463f]
        [&_input]:focus:outline-2 [&_input]:focus:outline-primary [&_input]:focus:outline-offset-1
        [&_textarea]:min-h-11 [&_textarea]:w-full [&_textarea]:rounded-md [&_textarea]:border
        [&_textarea]:border-[#dce4df] [&_textarea]:bg-white [&_textarea]:px-3 [&_textarea]:py-2.5
        [&_textarea]:font-[inherit] [&_textarea]:text-[15px] [&_textarea]:text-[#37463f] [&_textarea]:focus:outline-2
        [&_textarea]:focus:outline-primary [&_textarea]:focus:outline-offset-1 [&_select]:min-h-11 [&_select]:w-full
        [&_select]:rounded-md [&_select]:border [&_select]:border-[#dce4df] [&_select]:bg-white [&_select]:px-3
        [&_select]:py-2.5 [&_select]:font-[inherit] [&_select]:text-[15px] [&_select]:text-[#37463f]
        [&_select]:focus:outline-2 [&_select]:focus:outline-primary [&_select]:focus:outline-offset-1
        [&_[aria-invalid=true]]:border-[#b83d3d] [&>p]:text-xs [&>p]:text-[#b83d3d]
      "
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
    <div class="flex justify-end gap-3 pt-[5px]">
      <button
        type="button"
        class="
          inline-flex items-center justify-center gap-[9px] rounded-[7px] border px-5 py-[13px] font-[inherit]
          text-[18px] leading-6 font-semibold whitespace-nowrap cursor-pointer transition-colors duration-150 max-md:px-4
          max-md:py-2.5 max-md:text-sm border-[#dce4df] bg-white text-[#37463f] hover:bg-surface-muted
        "
        :disabled="saving"
        @click="emit('cancel')"
      >
        Cancel
      </button>
      <button
        type="submit"
        class="
          inline-flex items-center justify-center gap-[9px] rounded-[7px] border px-5 py-[13px] font-[inherit]
          text-[18px] leading-6 font-semibold whitespace-nowrap cursor-pointer transition-colors duration-150 max-md:px-4
          max-md:py-2.5 max-md:text-sm border-primary bg-primary text-white hover:bg-primary-hover [&_.iconify]:size-5
        "
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
