<script setup lang="ts">
  const props = defineProps<{ images: string[]; name: string; editTo?: string }>()
  const active = ref(0)
  const failed = ref(false)

  watch(
    () => props.images,
    () => {
      active.value = 0
      failed.value = false
    }
  )
  watch(active, () => {
    failed.value = false
  })
</script>

<template>
  <section
    class="min-w-0"
    aria-label="Product images"
  >
    <div
      class="grid aspect-square place-items-center overflow-hidden rounded-xl border border-default bg-elevated"
    >
      <img
        v-if="images.length && !failed"
        :key="images[active]"
        class="block size-full object-contain"
        :src="images[active]"
        :alt="`${name}, image ${active + 1} of ${images.length}`"
        @error="failed = true"
      />
      <EmptyState
        v-else
        icon="i-lucide-image"
        heading-tag="h3"
        :title="failed ? 'Image unavailable' : 'No product images yet'"
        :description="
          editTo
            ? failed
              ? 'Try another image or update this product.'
              : 'Add photos to show this product in more detail.'
            : undefined
        "
      >
        <UButton
          v-if="editTo"
          :to="editTo"
          :label="failed ? 'Edit images' : 'Add images'"
          color="neutral"
          variant="outline"
        />
      </EmptyState>
    </div>
    <div
      v-if="images.length > 1"
      class="mt-3 flex gap-2.5 overflow-x-auto p-0.5"
    >
      <button
        v-for="(image, index) in images"
        :key="image"
        type="button"
        class="size-20 shrink-0 cursor-pointer overflow-hidden rounded-lg border-2 border-transparent bg-elevated transition-colors hover:border-accented aria-pressed:border-primary"
        :aria-label="`View image ${index + 1}`"
        :aria-pressed="active === index"
        @click="active = index"
      >
        <img
          class="size-full object-cover"
          :src="image"
          alt=""
          loading="lazy"
        />
      </button>
    </div>
  </section>
</template>
