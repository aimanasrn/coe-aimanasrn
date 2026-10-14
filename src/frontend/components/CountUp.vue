<script setup lang="ts">
const props = withDefaults(
  defineProps<{
    value: number
    duration?: number
    format?: (value: number) => string
  }>(),
  { duration: 2000 }
)

const target = computed(() => (Number.isFinite(props.value) ? props.value : 0))
const displayed = ref(0)
const formatValue = (value: number) =>
  props.format ? props.format(value) : Math.round(value).toLocaleString()

let frame: number | undefined
let mounted = false
let motionPreference: MediaQueryList | undefined

function stopAnimation() {
  if (frame !== undefined) {
    cancelAnimationFrame(frame)
    frame = undefined
  }
}

function animate() {
  stopAnimation()

  const destination = target.value
  const duration = Number.isFinite(props.duration)
    ? Math.max(0, props.duration)
    : 2500

  if (motionPreference?.matches || duration === 0) {
    displayed.value = destination
    return
  }

  const from = displayed.value
  if (from === destination) return

  const startedAt = performance.now()

  function tick(now: number) {
    const progress = Math.min((now - startedAt) / duration, 1)
    const eased = 1 - Math.pow(1 - progress, 3)

    displayed.value = from + (destination - from) * eased

    if (progress < 1) {
      frame = requestAnimationFrame(tick)
    } else {
      displayed.value = destination
      frame = undefined
    }
  }

  frame = requestAnimationFrame(tick)
}

function handleMotionChange() {
  if (motionPreference?.matches) {
    stopAnimation()
    displayed.value = target.value
  }
}

watch(target, () => {
  if (mounted) animate()
})

onMounted(() => {
  mounted = true
  motionPreference = window.matchMedia('(prefers-reduced-motion: reduce)')
  motionPreference.addEventListener('change', handleMotionChange)
  animate()
})

onBeforeUnmount(() => {
  mounted = false
  stopAnimation()
  motionPreference?.removeEventListener('change', handleMotionChange)
})
</script>

<template>
  <span class="count-up">
    <span
      class="count-up-value"
      aria-hidden="true"
    >
      {{ formatValue(displayed) }}
    </span>
    <!-- Reserve the final size so counting does not move surrounding content. -->
    <span
      class="count-up-sizer"
      aria-hidden="true"
    >
      {{ formatValue(target) }}
    </span>
    <span class="sr-only">{{ formatValue(target) }}</span>
  </span>
</template>

<style scoped>
.count-up {
  display: inline-grid;
  max-width: 100%;
  vertical-align: top;
  font-variant-numeric: tabular-nums;
}

.count-up-value,
.count-up-sizer {
  grid-area: 1 / 1;
}

.count-up-sizer {
  visibility: hidden;
}
</style>
