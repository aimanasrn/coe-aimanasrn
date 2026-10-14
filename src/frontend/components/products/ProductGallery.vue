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
    class="sm-product-gallery"
    aria-label="Product images"
  >
    <div class="sm-product-gallery-stage">
      <img
        v-if="images.length && !failed"
        :key="images[active]"
        :src="images[active]"
        :alt="`${name}, image ${active + 1} of ${images.length}`"
        @error="failed = true"
      />
      <div
        v-else
        class="sm-product-no-image"
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
          class="sm-admin-link"
        >
          {{ failed ? 'Edit images' : 'Add images' }}
        </NuxtLink>
      </div>
    </div>
    <div
      v-if="images.length > 1"
      class="sm-product-gallery-thumbnails"
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
