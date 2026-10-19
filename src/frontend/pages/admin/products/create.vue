<script setup lang="ts">
  import ProductEditor from '~/components/products/ProductEditor.vue'
  definePageMeta({ layout: 'admin' })
  useHead({ title: 'Add product · StudyMart Admin' })
</script>

<template>
  <ProductEditor />
</template>
