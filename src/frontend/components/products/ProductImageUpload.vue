<script setup lang="ts">
  import type { ProductImageEntry } from '~/types/catalog'

  const entries = defineModel<ProductImageEntry[]>({ required: true })
  const props = defineProps<{ disabled?: boolean }>()
  const emit = defineEmits<{ error: [message: string] }>()
  const inputId = useId()
  const objectUrls = new Set<string>()
  const dragging = ref(false)

  function addFiles(files: FileList | null) {
    if (!files?.length || props.disabled) return
    const selected = Array.from(files)
    if (entries.value.length + selected.length > 6) {
      emit('error', 'You can add up to 6 product images.')
      return
    }
    for (const file of selected) {
      if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
        emit('error', 'Please choose JPG, PNG or WebP images.')
        return
      }
      if (file.size > 5 * 1024 * 1024) {
        emit('error', 'Each image must be 5 MB or smaller.')
        return
      }
    }
    const additions = selected.map((file) => {
      const url = URL.createObjectURL(file)
      objectUrls.add(url)
      return { key: url, url, file }
    })
    entries.value = [...entries.value, ...additions]
    emit('error', '')
  }

  function changed(event: Event) {
    const input = event.target as HTMLInputElement
    addFiles(input.files)
    input.value = ''
  }

  function dropped(event: DragEvent) {
    dragging.value = false
    addFiles(event.dataTransfer?.files ?? null)
  }

  function remove(key: string) {
    if (props.disabled) return
    const entry = entries.value.find((image) => image.key === key)
    if (entry && objectUrls.delete(entry.url)) URL.revokeObjectURL(entry.url)
    entries.value = entries.value.filter((image) => image.key !== key)
    emit('error', '')
  }

  onBeforeUnmount(() => {
    objectUrls.forEach((url) => URL.revokeObjectURL(url))
  })
</script>

<template>
  <div class="flex flex-col gap-4">
    <label
      :for="inputId"
      class="flex min-h-52 cursor-pointer flex-col items-center justify-center gap-3 rounded-lg border border-dashed border-accented px-4 py-6 text-center transition-colors hover:border-primary hover:bg-primary/5 focus-within:border-primary focus-within:bg-primary/5"
      :class="{
        'border-primary bg-primary/5': dragging,
        'cursor-not-allowed opacity-50': disabled
      }"
      @dragover.prevent="dragging = !disabled"
      @dragleave.prevent="dragging = false"
      @drop.prevent="dropped"
    >
      <input
        :id="inputId"
        type="file"
        accept="image/jpeg,image/png,image/webp"
        multiple
        class="sr-only"
        aria-label="Upload product images"
        :disabled="disabled"
        @change="changed"
      />
      <span class="grid size-12 place-items-center rounded-full bg-primary/10 text-primary">
        <UIcon
          name="i-lucide-image-plus"
          class="size-6"
          aria-hidden="true"
        />
      </span>
      <strong class="text-sm font-semibold text-highlighted"
        >Click to upload or drag and drop</strong
      >
      <span class="text-xs text-muted">JPG, PNG or WebP. Up to 6 images, 5 MB each.</span>
    </label>
    <ul
      v-if="entries.length"
      class="grid grid-cols-3 gap-3"
    >
      <li
        v-for="(image, index) in entries"
        :key="image.key"
        class="relative aspect-square overflow-hidden rounded-lg border border-default bg-elevated"
      >
        <img
          class="size-full object-cover"
          :src="image.url"
          :alt="`Product image ${index + 1} preview`"
        />
        <UBadge
          v-if="index === 0"
          label="Cover"
          color="neutral"
          variant="solid"
          size="sm"
          class="absolute bottom-1.5 left-1.5"
        />
        <UButton
          icon="i-lucide-x"
          color="neutral"
          variant="solid"
          size="xs"
          class="absolute top-1.5 right-1.5 rounded-full"
          :aria-label="`Remove image ${index + 1}`"
          :disabled="disabled"
          @click="remove(image.key)"
        />
      </li>
    </ul>
  </div>
</template>
