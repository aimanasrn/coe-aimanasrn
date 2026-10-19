<script setup lang="ts">
  defineProps<{
    admin?: boolean
    caption?: string
    inverse?: boolean
  }>()
</script>

<template>
  <span
    class="studymart-logo"
    :class="{ 'studymart-logo--inverse': inverse }"
  >
    <img
      :src="inverse ? '/studymart-mark-inverse.svg' : '/studymart-mark.svg'"
      alt=""
      width="34"
      height="34"
      class="studymart-logo__mark"
    />
    <span class="studymart-logo__text">
      <span>StudyMart</span>
      <span
        v-if="admin"
        class="studymart-logo__caption"
        >{{ caption || 'Admin' }}</span
      >
    </span>
  </span>
</template>
