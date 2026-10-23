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
    class="flex flex-col items-center gap-[18px] px-5 py-[70px] text-center"
    role="alert"
  >
    <UIcon
      class="size-[52px] text-[#89928c]"
      :name="notFound ? 'i-lucide-package-search' : 'i-lucide-circle-alert'"
      mode="svg"
      aria-hidden="true"
    />
    <h1 class="text-[28px] font-[650]">
      {{ notFound ? 'Product not found' : 'Unable to load product' }}
    </h1>
    <p class="text-[#7b867f]">
      {{
        notFound
          ? 'This product may have been removed from the catalog.'
          : 'Please try loading this product again.'
      }}
    </p>
    <AppButton
      v-if="notFound"
      to="/admin/products"
    >
      Back to products
    </AppButton>
    <AppButton
      v-else
      type="button"
      @click="emit('retry')"
    >
      Try again
    </AppButton>
  </section>
</template>
