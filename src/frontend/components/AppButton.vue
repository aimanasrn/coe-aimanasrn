<script setup lang="ts">
  import { NuxtLink } from '#components'
  import { twMerge } from 'tailwind-merge'

  defineOptions({ inheritAttrs: false })

  const element = ref<HTMLElement | { $el: HTMLElement }>()

  defineExpose({
    focus(options?: FocusOptions) {
      const target = element.value
      const node = target instanceof HTMLElement ? target : target?.$el
      node?.focus(options)
    }
  })

  const props = withDefaults(
    defineProps<{
      class?: string
      to?: string
      type?: 'button' | 'submit' | 'reset'
      variant?: 'primary' | 'secondary' | 'danger'
    }>(),
    { type: 'button', variant: 'primary' }
  )

  const variants = {
    primary: 'border-transparent bg-primary text-white hover:bg-primary-hover',
    secondary: 'border-border bg-white text-heading hover:border-[#bbcfc0] hover:bg-surface-muted',
    danger: 'border-transparent bg-danger text-white hover:bg-red-700'
  }
  const baseClass = [
    'inline-flex min-h-12 cursor-pointer items-center justify-center gap-2 rounded-lg border px-5 py-3',
    'text-base leading-[22px] font-medium whitespace-nowrap transition-colors duration-150',
    'focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-primary',
    'disabled:cursor-not-allowed disabled:opacity-50 [&_.iconify]:size-5 [&_.iconify]:flex-none'
  ].join(' ')

  const buttonClass = computed(() => twMerge(baseClass, variants[props.variant], props.class))
</script>

<template>
  <component
    ref="element"
    :is="to ? NuxtLink : 'button'"
    :to="to"
    :type="to ? undefined : type"
    v-bind="$attrs"
    :class="buttonClass"
  >
    <slot />
  </component>
</template>
