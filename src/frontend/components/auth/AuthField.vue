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
    }>(),
    { type: 'text', required: true }
  )
  const visible = ref(false)
  const inputType = computed(() =>
    props.type === 'password' && visible.value ? 'text' : props.type
  )
</script>

<template>
  <div
    class="account-field"
    :class="{ 'account-field--icon': icon }"
  >
    <label
      :for="id"
      class="account-field__label mb-2 block text-sm font-medium text-heading"
      >{{ label }}</label
    >
    <div class="relative">
      <UIcon
        v-if="icon"
        :name="icon"
        class="account-field__icon"
        aria-hidden="true"
      />
      <input
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
        :aria-invalid="error ? true : undefined"
        :aria-describedby="error ? `${id}-error` : hint ? `${id}-hint` : undefined"
        class="account-field__input h-12 w-full rounded-lg border bg-white px-3.5 text-base text-heading focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/15 disabled:opacity-60"
        :class="[error ? 'border-red-400' : 'border-border', type === 'password' ? 'pr-12' : '']"
      />
      <button
        v-if="type === 'password'"
        type="button"
        :aria-label="`${visible ? 'Hide' : 'Show'} ${label.toLowerCase()}`"
        :aria-pressed="visible"
        :disabled="disabled"
        class="account-field__reveal absolute inset-y-1 right-1 flex w-10 items-center justify-center rounded-lg text-muted hover:bg-surface-muted focus-visible:outline-2 focus-visible:outline-primary"
        @click="visible = !visible"
      >
        <UIcon
          :name="visible ? 'i-lucide-eye-off' : 'i-lucide-eye'"
          class="size-[18px]"
          aria-hidden="true"
        />
      </button>
    </div>
    <p
      v-if="error"
      :id="`${id}-error`"
      class="mt-1.5 text-xs leading-5 text-red-700"
    >
      {{ error }}
    </p>
    <p
      v-else-if="hint"
      :id="`${id}-hint`"
      class="mt-1.5 text-xs leading-5 text-muted"
    >
      {{ hint }}
    </p>
  </div>
</template>
