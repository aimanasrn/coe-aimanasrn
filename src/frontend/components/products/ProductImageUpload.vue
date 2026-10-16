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
  <div class="flex flex-col gap-[18px]">
    <label
      :for="inputId"
      class="
        relative flex min-h-[220px] cursor-pointer flex-col items-center justify-center gap-3.5 rounded-[7px] border-2
        border-dashed border-[#dce4df] px-[18px] py-7 text-center hover:border-primary hover:bg-[#f4fbf6]
        focus-within:border-primary focus-within:bg-[#f4fbf6] max-md:min-h-[190px] [&>.iconify]:size-10
        [&>.iconify]:text-primary [&_strong]:text-sm [&_strong]:font-[550] [&_strong]:text-[#374f40]
        [&>span:last-child]:text-xs [&>span:last-child]:leading-[1.6] [&>span:last-child]:text-[#89928c]
      "
      :class="{ 'border-primary! bg-[#f4fbf6]': dragging, 'opacity-50 cursor-not-allowed!': disabled }"
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
      class="
        grid grid-cols-3 gap-3 [&_figure]:min-w-0 [&_figure>div]:relative [&_figure>div]:aspect-square
        [&_figure>div]:rounded-md [&_figure>div]:border [&_figure>div]:border-[#dce4df] [&_figure>div]:bg-[#f2f5f3]
        [&_img]:size-full [&_img]:rounded-md [&_img]:object-cover [&_button]:absolute [&_button]:top-[5px]
        [&_button]:right-[5px] [&_button]:grid [&_button]:size-[25px] [&_button]:cursor-pointer
        [&_button]:place-items-center [&_button]:rounded-full [&_button]:border [&_button]:border-[#dce4df]
        [&_button]:bg-white [&_button]:text-[#374f40] [&_button]:hover:bg-[#fff4f4] [&_button]:hover:text-danger
        [&_button_.iconify]:size-3.5 [&_figcaption]:pt-[7px] [&_figcaption]:text-center [&_figcaption]:text-xs
        [&_figcaption]:text-[#66746c]
      "
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
    <p class="text-sm text-[#7b867f]">The first image is used as the cover.</p>
  </div>
</template>
