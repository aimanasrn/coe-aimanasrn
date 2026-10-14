<script setup lang="ts">
import type { ProductImageEntry } from '~/types/catalog'

const entries = defineModel<ProductImageEntry[]>({ required: true })
const props = defineProps<{ disabled?: boolean }>()
const emit = defineEmits<{ error: [message: string] }>()
const notification = useActionToast()
const inputId = useId()
const objectUrls = new Set<string>()
const dragging = ref(false)

function rejectFiles(message: string) {
  emit('error', message)
  notification.warning('Images could not be added', message)
}

function addFiles(files: FileList | null) {
  if (!files?.length || props.disabled) return
  const selected = Array.from(files)
  if (entries.value.length + selected.length > 6) {
    rejectFiles('You can add up to 6 product images.')
    return
  }
  for (const file of selected) {
    if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
      rejectFiles('Please choose JPG, PNG or WebP images.')
      return
    }
    if (file.size > 5 * 1024 * 1024) {
      rejectFiles('Each image must be 5 MB or smaller.')
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
  notification.info(
    selected.length === 1 ? 'Image selected' : 'Images selected',
    'Save the product to upload your selected images.'
  )
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
  notification.info('Image removed', 'Save the product to apply this change.')
}

onBeforeUnmount(() => {
  objectUrls.forEach((url) => URL.revokeObjectURL(url))
})
</script>

<template>
  <div class="sm-product-upload">
    <label
      :for="inputId"
      class="sm-product-dropzone"
      :class="{ 'is-dragging': dragging, 'is-disabled': disabled }"
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
      <UIcon
        name="i-lucide-image-plus"
        mode="svg"
        aria-hidden="true"
      />
      <strong>Click to upload or drag and drop</strong>
      <span>JPG, PNG or WebP. Up to 6 images, 5 MB each.</span>
    </label>
    <div
      v-if="entries.length"
      class="sm-product-image-previews"
    >
      <figure
        v-for="(image, index) in entries"
        :key="image.key"
      >
        <div>
          <img
            :src="image.url"
            :alt="`Product image ${index + 1} preview`"
          />
          <button
            type="button"
            :aria-label="`Remove image ${index + 1}`"
            :disabled="disabled"
            @click="remove(image.key)"
          >
            <UIcon
              name="i-lucide-x"
              mode="svg"
              aria-hidden="true"
            />
          </button>
        </div>
        <figcaption v-if="index === 0">Cover</figcaption>
      </figure>
    </div>
    <p class="sm-admin-muted">The first image is used as the cover.</p>
  </div>
</template>
