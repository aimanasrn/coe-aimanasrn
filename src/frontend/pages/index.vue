<script setup lang="ts">
  import LandingHeader from '~/components/landing/LandingHeader.vue'
  import LandingHero from '~/components/landing/LandingHero.vue'
  import LandingStats from '~/components/landing/LandingStats.vue'
  import LandingCategories from '~/components/landing/LandingCategories.vue'
  import LandingFeatured from '~/components/landing/LandingFeatured.vue'
  import LandingSteps from '~/components/landing/LandingSteps.vue'
  import LandingFeatures from '~/components/landing/LandingFeatures.vue'
  import LandingFaq from '~/components/landing/LandingFaq.vue'
  import LandingCta from '~/components/landing/LandingCta.vue'
  import LandingFooter from '~/components/landing/LandingFooter.vue'
  import type { LandingData } from '~/types/landing'

  definePageMeta({ layout: false })

  const { user, fetchUser } = useAuth()
  // Signed-in visitors see shortcuts to the shop or dashboard instead of sign-up buttons.
  await useAsyncData('landing-user', async () => {
    try {
      return { signedIn: Boolean(await fetchUser()) }
    } catch {
      return { signedIn: false }
    }
  })
  const { data } = await useFetch<LandingData>('/api/landing', { key: 'landing' })

  const browseTo = computed(() => {
    if (user.value?.role === 'admin') return '/admin/products'
    return user.value ? '/shop' : '/register'
  })

  const description =
    'StudyMart sells its own range of study essentials: pens, notebooks, art supplies and workbooks, with clear prices in RM and live stock levels.'
  useSeoMeta({
    title: 'StudyMart · Study essentials for every chapter',
    description,
    ogTitle: 'StudyMart · Study essentials for every chapter',
    ogDescription: description,
    ogType: 'website',
    ogImage: '/images/studymart-books-modern.png',
    twitterCard: 'summary_large_image'
  })
</script>

<template>
  <div class="landing-page min-h-svh overflow-x-clip bg-default">
    <a
      href="#main"
      class="sr-only z-50 rounded-md bg-primary px-4 py-2 font-semibold text-inverted focus:not-sr-only focus:fixed focus:top-3 focus:left-3"
    >
      Skip to content
    </a>
    <LandingHeader :user="user" />
    <main id="main">
      <LandingHero
        :user="user"
        :data="data ?? null"
      />
      <LandingStats
        v-if="data"
        :data="data"
      />
      <LandingCategories
        v-if="data?.categories.length"
        :categories="data.categories"
        :browse-to="browseTo"
      />
      <LandingFeatured
        v-if="data"
        :products="data.featured"
        :user="user"
        :browse-to="browseTo"
      />
      <LandingSteps />
      <LandingFeatures />
      <LandingFaq />
      <LandingCta :user="user" />
    </main>
    <LandingFooter />
  </div>
</template>
