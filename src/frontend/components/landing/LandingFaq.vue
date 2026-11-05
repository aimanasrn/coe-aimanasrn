<script setup lang="ts">
  import type { AccordionItem } from '@nuxt/ui'
  import LandingSectionHeading from '~/components/landing/LandingSectionHeading.vue'

  const items: AccordionItem[] = [
    {
      label: 'Who can shop at StudyMart?',
      content:
        'Anyone with a free StudyMart customer account. Create one with your name and email, and you can start browsing straight away.'
    },
    {
      label: 'Do I need an account to see products?',
      content:
        'You can preview our featured products on this page. Sign in to browse the full range, search, filter by category, and see product details.'
    },
    {
      label: 'Can I pay online?',
      content:
        'Not yet. Right now you can browse the range and save items to your cart. Online checkout is not available at the moment.'
    },
    {
      label: 'Is my cart saved?',
      content:
        'Yes. Your cart is saved to your account, so it is still there next time you log in. Prices and availability are checked whenever you change it, and items are not reserved.'
    },
    {
      label: 'What brands do you sell?',
      content:
        'StudyMart sells its own range of study essentials, from pens and notebooks to art supplies and workbooks.'
    }
  ]
</script>

<template>
  <section
    id="faq"
    aria-labelledby="faq-title"
    class="scroll-mt-20 bg-muted/60 py-24"
  >
    <UContainer class="max-w-3xl">
      <LandingSectionHeading
        id="faq-title"
        eyebrow="FAQ"
        title="Questions, answered"
      />
      <div
        v-reveal
        class="rounded-2xl border border-default bg-default px-6"
      >
        <UAccordion
          :items="items"
          :ui="{ trigger: 'py-5 text-base font-semibold', body: 'pb-5 text-muted' }"
        />
      </div>
    </UContainer>
  </section>
</template>
