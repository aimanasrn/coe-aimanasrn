<script setup lang="ts">
  defineProps<{ loading: boolean; notFound?: boolean }>()
  const emit = defineEmits<{ retry: [] }>()
</script>

<template>
  <div
    v-if="loading"
    class="grid min-h-[420px] place-items-center"
  >
    <StudyMartLoader label="Loading product" />
  </div>
  <section
    v-else
    class="flex flex-col items-center gap-[18px] px-5 py-[70px] text-center [&>.iconify]:size-[52px] [&>.iconify]:text-[#89928c] [&_h1]:text-[28px] [&_h1]:font-[650] [&_p]:text-[#7b867f]"
    role="alert"
  >
    <UIcon
      :name="notFound ? 'i-lucide-package-search' : 'i-lucide-circle-alert'"
      mode="svg"
      aria-hidden="true"
    />
    <h1>{{ notFound ? 'Product not found' : 'Unable to load product' }}</h1>
    <p>
      {{
        notFound
          ? 'This product may have been removed from the catalog.'
          : 'Please try loading this product again.'
      }}
    </p>
    <NuxtLink
      v-if="notFound"
      to="/admin/products"
      class="catalog-button catalog-button--primary"
    >
      Back to products
    </NuxtLink>
    <button
      v-else
      type="button"
      class="catalog-button catalog-button--primary"
      @click="emit('retry')"
    >
      Try again
    </button>
  </section>
</template>
