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
      variant?: 'default' | 'danger'
    }>(),
    { variant: 'default' }
  )
  const baseClass = [
    'inline-flex size-9.5 flex-none cursor-pointer items-center justify-center rounded-[7px]',
    'border border-border bg-white p-2.25 text-[#5b6d61] transition-colors duration-150',
    'focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-primary',
    'disabled:cursor-not-allowed disabled:opacity-50 [&_.iconify]:size-4.5'
  ].join(' ')

  const iconButtonClass = computed(() =>
    twMerge(
      baseClass,
      props.variant === 'danger'
        ? 'hover:border-[#f3c6c2] hover:bg-[#fff3f2] hover:text-danger'
        : 'hover:border-[#b5cbbb] hover:bg-secondary hover:text-primary',
      props.class
    )
  )
</script>

<template>
  <component
    ref="element"
    :is="to ? NuxtLink : 'button'"
    :to="to"
    :type="to ? undefined : 'button'"
    v-bind="$attrs"
    :class="iconButtonClass"
  >
    <slot />
  </component>
</template>
