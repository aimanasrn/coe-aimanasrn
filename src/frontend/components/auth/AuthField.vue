<script setup lang="ts">
  const model = defineModel<string>({ default: '' })
  const props = withDefaults(
    defineProps<{
      id: string
      label: string
      type?: string
      autocomplete?: string
      required?: boolean
      disabled?: boolean
      maxlength?: number
      minlength?: number
      error?: string
      hint?: string
      placeholder?: string
      icon?: string
      size?: 'md' | 'lg' | 'xl'
    }>(),
    { type: 'text', required: true, size: 'lg' }
  )
  const visible = ref(false)
  const inputType = computed(() =>
    props.type === 'password' && visible.value ? 'text' : props.type
  )
</script>

<template>
  <UFormField
    :label="label"
    :name="id"
    :error="error"
    :help="hint"
  >
    <UInput
      :id="id"
      v-model="model"
      :name="id"
      :type="inputType"
      :placeholder="placeholder"
      :autocomplete="autocomplete"
      :required="required"
      :disabled="disabled"
      :maxlength="maxlength"
      :minlength="minlength"
      :icon="icon"
      :size="size"
      class="w-full"
      :ui="type === 'password' ? { trailing: 'pe-1' } : undefined"
    >
      <template
        v-if="type === 'password'"
        #trailing
      >
        <UButton
          color="neutral"
          variant="link"
          size="sm"
          :icon="visible ? 'i-lucide-eye-off' : 'i-lucide-eye'"
          :aria-label="(visible ? 'Hide' : 'Show') + ' ' + label.toLowerCase()"
          :aria-pressed="visible"
          :disabled="disabled"
          @click="visible = !visible"
        />
      </template>
    </UInput>
  </UFormField>
</template>
