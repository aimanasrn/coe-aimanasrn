<script setup lang="ts">
  import type { AuthUser } from '~/types/auth'

  const props = defineProps<{ user: AuthUser | null }>()

  const action = computed(() => {
    if (props.user?.role === 'admin') return { label: 'Open dashboard', to: '/admin' }
    if (props.user) return { label: 'Go to the shop', to: '/shop' }
    return { label: 'Create free account', to: '/register' }
  })
</script>

<template>
  <section
    aria-labelledby="cta-title"
    class="py-24"
  >
    <UContainer>
      <div
        v-reveal
        class="relative isolate overflow-hidden rounded-[2rem] bg-primary-900 px-6 py-16 text-center shadow-2xl sm:px-16"
      >
        <div
          class="landing-blob -top-20 -left-16 -z-10 size-72 bg-primary-500"
          aria-hidden="true"
        />
        <div
          class="landing-blob -right-16 -bottom-24 -z-10 size-80 bg-secondary-400"
          style="animation-delay: -8s"
          aria-hidden="true"
        />
        <h2
          id="cta-title"
          class="mx-auto max-w-2xl text-3xl font-extrabold tracking-tight text-white sm:text-4xl"
        >
          Ready for your next chapter?
        </h2>
        <p class="mx-auto mt-4 max-w-xl text-lg text-white/75">
          Join StudyMart and get everything you need for school, revision and creative projects in
          one place.
        </p>
        <div class="mt-9 flex flex-wrap justify-center gap-3">
          <UButton
            :to="action.to"
            :label="action.label"
            trailing-icon="i-lucide-arrow-right"
            size="xl"
            color="neutral"
            variant="solid"
            class="bg-white text-primary-900 hover:bg-white/90"
          />
          <UButton
            v-if="!user"
            to="/login"
            label="I already have an account"
            size="xl"
            color="neutral"
            variant="ghost"
            class="text-white hover:bg-white/10"
          />
        </div>
      </div>
    </UContainer>
  </section>
</template>
