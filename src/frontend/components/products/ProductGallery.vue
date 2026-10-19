<script setup lang="ts">
  const props = defineProps<{ images: string[]; name: string; editTo: string }>()
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
    class="catalog-panel p-4"
    aria-label="Product images"
  >
    <div
      class="grid aspect-square place-items-center overflow-hidden rounded-[7px] bg-[#f2f5f3] max-[1101px]:aspect-[1.2] max-[1101px]:max-h-[480px] max-md:aspect-square [&>img]:block [&>img]:size-full [&>img]:object-contain"
    >
      <img
        v-if="images.length && !failed"
        :key="images[active]"
        :src="images[active]"
        :alt="`${name}, image ${active + 1} of ${images.length}`"
        @error="failed = true"
      />
      <div
        v-else
        class="flex flex-col items-center gap-3 px-[22px] py-8 text-center [&>.iconify]:size-[52px] [&>.iconify]:text-[#98ada0] [&_strong]:text-[17px] [&_strong]:text-[#374f40] [&_p]:max-w-[260px] [&_p]:text-sm [&_p]:leading-[1.6] [&_p]:text-[#7b867f]"
      >
        <UIcon
          name="i-lucide-image"
          mode="svg"
          aria-hidden="true"
        />
        <strong>
          {{ failed ? 'Image unavailable' : 'No product images yet' }}
        </strong>
        <p>
          {{
            failed
              ? 'Try another image or update this product.'
              : 'Add photos to show this product in more detail.'
          }}
        </p>
        <NuxtLink
          :to="editTo"
          class="catalog-link"
        >
          {{ failed ? 'Edit images' : 'Add images' }}
        </NuxtLink>
      </div>
    </div>
    <div
      v-if="images.length > 1"
      class="flex gap-3 overflow-x-auto px-0.5 pt-3.5 pb-0.5 [&_button]:size-[104px] [&_button]:shrink-0 [&_button]:cursor-pointer [&_button]:rounded-[7px] [&_button]:border [&_button]:border-[#dce4df] [&_button]:bg-white [&_button]:p-[3px] [&_button]:aria-pressed:border-2 [&_button]:aria-pressed:border-primary [&_img]:size-full [&_img]:rounded [&_img]:object-cover"
    >
      <button
        v-for="(image, index) in images"
        :key="image"
        type="button"
        :aria-label="`View image ${index + 1}`"
        :aria-pressed="active === index"
        @click="active = index"
      >
        <img
          :src="image"
          alt=""
          loading="lazy"
        />
      </button>
    </div>
  </section>
</template>
