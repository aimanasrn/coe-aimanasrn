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
    class="
      min-w-0 overflow-hidden border border-border rounded-xl bg-white [&_h2]:m-0
      [&_h2]:text-heading [&_h2]:text-xl [&_h2]:font-semibold [&_h2]:leading-[1.4]
      [&_h2]:tracking-[-0.4px] max-md:[&_h2]:text-lg p-4
    "
    aria-label="Product images"
  >
    <div
      class="
        grid aspect-square place-items-center overflow-hidden rounded-[7px] bg-[#f2f5f3]
        max-[1101px]:aspect-[1.2] max-[1101px]:max-h-[480px] max-md:aspect-square
      "
    >
      <img
        class="block size-full object-contain"
        v-if="images.length && !failed"
        :key="images[active]"
        :src="images[active]"
        :alt="`${name}, image ${active + 1} of ${images.length}`"
        @error="failed = true"
      />
      <div
        v-else
        class="flex flex-col items-center gap-3 px-[22px] py-8 text-center"
      >
        <UIcon
          class="size-[52px] text-[#98ada0]"
          name="i-lucide-image"
          mode="svg"
          aria-hidden="true"
        />
        <strong class="text-[17px] text-[#374f40]">
          {{ failed ? 'Image unavailable' : 'No product images yet' }}
        </strong>
        <p class="max-w-[260px] text-sm leading-[1.6] text-[#7b867f]">
          {{
            failed
              ? 'Try another image or update this product.'
              : 'Add photos to show this product in more detail.'
          }}
        </p>
        <NuxtLink
          :to="editTo"
          class="
            inline-flex items-center gap-2 rounded-[3px] text-primary text-sm font-medium leading-normal
            cursor-pointer [&_.iconify]:w-4.5 [&_.iconify]:h-4.5 [&_.iconify]:flex-none
            hover:text-primary-hover hover:underline hover:underline-offset-4
          "
        >
          {{ failed ? 'Edit images' : 'Add images' }}
        </NuxtLink>
      </div>
    </div>
    <div
      v-if="images.length > 1"
      class="flex gap-3 overflow-x-auto px-0.5 pt-3.5 pb-0.5"
    >
      <button
        class="
          size-[104px] shrink-0 cursor-pointer rounded-[7px] border border-[#dce4df] bg-white p-[3px]
          aria-pressed:border-2 aria-pressed:border-primary
        "
        v-for="(image, index) in images"
        :key="image"
        type="button"
        :aria-label="`View image ${index + 1}`"
        :aria-pressed="active === index"
        @click="active = index"
      >
        <img
          class="size-full rounded object-cover"
          :src="image"
          alt=""
          loading="lazy"
        />
      </button>
    </div>
  </section>
</template>
