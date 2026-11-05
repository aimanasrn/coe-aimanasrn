<script setup lang="ts">
  import LandingSectionHeading from '~/components/landing/LandingSectionHeading.vue'

  const features = [
    {
      icon: 'i-lucide-badge-check',
      title: 'Our own range',
      description: 'Every item is chosen and stocked by StudyMart, so quality stays consistent.'
    },
    {
      icon: 'i-lucide-wallet',
      title: 'Student-friendly prices',
      description: 'Clear prices in RM, from everyday pencils to complete geometry sets.'
    },
    {
      icon: 'i-lucide-package-check',
      title: 'Live stock levels',
      description: 'See what’s available before you add it, with no surprises later.'
    },
    {
      icon: 'i-lucide-moon-star',
      title: 'Easy on the eyes',
      description: 'A clean design with light and dark modes for late-night study sessions.'
    }
  ]
</script>

<template>
  <section
    aria-labelledby="features-title"
    class="py-24"
  >
    <UContainer>
      <LandingSectionHeading
        id="features-title"
        eyebrow="Why StudyMart"
        title="Built around the way students study"
      />

      <div class="grid items-stretch gap-6 lg:grid-cols-2">
        <div
          v-reveal
          class="relative min-h-80 overflow-hidden rounded-3xl border border-default"
        >
          <img
            src="/images/studymart-study-desk.png"
            alt="A warm study desk with books, pencils and a mug"
            width="1586"
            height="992"
            loading="lazy"
            class="absolute inset-0 size-full object-cover"
          />
          <div
            class="absolute inset-0 bg-linear-to-t from-black/70 via-black/10 to-transparent"
            aria-hidden="true"
          />
          <div class="absolute inset-x-0 bottom-0 p-8">
            <p class="max-w-sm text-2xl font-bold text-white">
              A calm desk, the right tools, and nothing standing between you and your work.
            </p>
          </div>
        </div>

        <ul class="grid gap-4 sm:grid-cols-2">
          <li
            v-for="(feature, index) in features"
            :key="feature.title"
            v-reveal="index * 90"
            class="landing-lift rounded-2xl border border-default bg-default p-6"
          >
            <span class="grid size-11 place-items-center rounded-xl bg-primary/10 text-primary">
              <UIcon
                :name="feature.icon"
                class="size-5"
                aria-hidden="true"
              />
            </span>
            <h3 class="mt-4 font-bold text-highlighted">{{ feature.title }}</h3>
            <p class="mt-2 text-sm text-muted">{{ feature.description }}</p>
          </li>
        </ul>
      </div>
    </UContainer>
  </section>
</template>
