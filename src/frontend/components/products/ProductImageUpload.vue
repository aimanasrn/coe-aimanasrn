<script setup lang="ts">
  import type { ProductImageEntry } from '~/types/catalog'

  const entries = defineModel<ProductImageEntry[]>({ required: true })
  const props = defineProps<{ disabled?: boolean }>()
  const emit = defineEmits<{ error: [message: string] }>()
  const inputId = useId()
  const objectUrls = new Set<string>()
  const dragging = ref(false)

  function rejectFiles(message: string) {
    emit('error', message)
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
  <div class="flex flex-col gap-[18px]">
    <label
      :for="inputId"
      class="
        relative flex min-h-62.5 flex-col items-center justify-center gap-4 py-6 px-4 border
        border-dashed border-[#cdd8d1] rounded-[10px] text-center cursor-pointer
        transition-[background,border-color,color,box-shadow,transform] duration-150
        hover:border-primary hover:bg-[#f7fbf8] focus-within:border-primary
        focus-within:bg-[#f7fbf8] [&_>_span:last-child]:text-muted [&_>_span:last-child]:text-xs
        [&_>_span:last-child]:leading-[1.6] data-[dragging=true]:border-primary
        data-[dragging=true]:bg-[#f7fbf8]
      "
      :data-dragging="dragging"
      :class="{ 'opacity-50 cursor-not-allowed!': disabled }"
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
        class="w-10 h-10 text-[#74867a]"
        name="i-lucide-image-plus"
        mode="svg"
        aria-hidden="true"
      />
      <strong class="text-body text-sm font-medium">Click to upload or drag and drop</strong>
      <span>JPG, PNG or WebP. Up to 6 images, 5 MB each.</span>
    </label>
    <div
      v-if="entries.length"
      class="grid grid-cols-3 gap-3"
    >
      <figure
        class="min-w-0"
        v-for="(image, index) in entries"
        :key="image.key"
      >
        <div class="relative aspect-square rounded-md border border-[#dce4df] bg-[#f2f5f3]">
          <img
            class="size-full rounded-md object-cover"
            :src="image.url"
            :alt="`Product image ${index + 1} preview`"
          />
          <button
            class="
              absolute top-[5px] right-[5px] grid size-[25px] cursor-pointer place-items-center
              rounded-full border border-[#dce4df] bg-white text-[#374f40] hover:bg-[#fff4f4]
              hover:text-danger
            "
            type="button"
            :aria-label="`Remove image ${index + 1}`"
            :disabled="disabled"
            @click="remove(image.key)"
          >
            <UIcon
              class="size-3.5"
              name="i-lucide-x"
              mode="svg"
              aria-hidden="true"
            />
          </button>
        </div>
        <figcaption
          class="pt-[7px] text-center text-xs text-[#66746c]"
          v-if="index === 0"
        >
          Cover
        </figcaption>
      </figure>
    </div>
    <p class="text-sm text-[#7b867f]">The first image is used as the cover.</p>
  </div>
</template>
