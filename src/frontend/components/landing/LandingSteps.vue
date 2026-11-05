<script setup lang="ts">
  import LandingSectionHeading from '~/components/landing/LandingSectionHeading.vue'

  const steps = [
    {
      icon: 'i-lucide-user-round-plus',
      title: 'Create your account',
      description: 'Sign up free with your name and email. It takes less than a minute.'
    },
    {
      icon: 'i-lucide-search',
      title: 'Browse and compare',
      description: 'Search by name, filter by category and sort by price to find what you need.'
    },
    {
      icon: 'i-lucide-shopping-cart',
      title: 'Build your cart',
      description: 'Add items as you go. Your cart is saved to your account for your next visit.'
    }
  ]
</script>

<template>
  <section
    id="how-it-works"
    aria-labelledby="how-it-works-title"
    class="scroll-mt-20 py-24"
  >
    <UContainer>
      <LandingSectionHeading
        id="how-it-works-title"
        eyebrow="How it works"
        title="Ready in three simple steps"
      />

      <div class="relative">
        <div
          class="absolute top-10 right-[16%] left-[16%] hidden h-px bg-linear-to-r from-transparent via-primary/40 to-transparent md:block"
          aria-hidden="true"
        />
        <ol class="relative grid gap-10 md:grid-cols-3 md:gap-6">
          <li
            v-for="(step, index) in steps"
            :key="step.title"
            v-reveal="index * 120"
            class="relative flex flex-col items-center text-center"
          >
            <span
              class="relative grid size-20 place-items-center rounded-3xl border border-default bg-default text-primary shadow-lg shadow-primary/10"
            >
              <UIcon
                :name="step.icon"
                class="size-8"
                aria-hidden="true"
              />
              <span
                class="absolute -top-2 -right-2 grid size-7 place-items-center rounded-full bg-primary text-xs font-bold text-inverted"
              >
                {{ index + 1 }}
              </span>
            </span>
            <h3 class="mt-6 text-lg font-bold text-highlighted">{{ step.title }}</h3>
            <p class="mt-2 max-w-xs text-muted">{{ step.description }}</p>
          </li>
        </ol>
      </div>
    </UContainer>
  </section>
</template>
