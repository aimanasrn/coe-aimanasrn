<script setup lang="ts">
  import type { AuthUser } from '~/types/auth'
  import type { LandingData } from '~/types/landing'
  import { formatMoney } from '~/utils/catalog'

  const props = defineProps<{ user: AuthUser | null; data: LandingData | null }>()

  const primaryAction = computed(() => {
    if (props.user?.role === 'admin') return { label: 'Open dashboard', to: '/admin' }
    if (props.user) return { label: 'Continue shopping', to: '/shop' }
    return { label: 'Create free account', to: '/register' }
  })
  const spotlight = computed(() => props.data?.featured[0])
  const highlights = [
    { icon: 'i-lucide-badge-check', label: 'StudyMart own brand' },
    { icon: 'i-lucide-banknote', label: 'Prices in RM' },
    { icon: 'i-lucide-shopping-cart', label: 'Cart saved to your account' }
  ]
</script>

<template>
  <section class="relative isolate overflow-hidden pt-28 pb-20 sm:pt-36 lg:pb-28">
    <div
      class="landing-grid absolute inset-0 -z-10"
      aria-hidden="true"
    />
    <div
      class="landing-blob -top-24 -left-24 -z-10 size-96 bg-primary-300"
      aria-hidden="true"
    />
    <div
      class="landing-blob top-40 -right-32 -z-10 size-[28rem] bg-secondary-200"
      style="animation-delay: -6s"
      aria-hidden="true"
    />

    <UContainer class="grid items-center gap-14 lg:grid-cols-[1.05fr_1fr] lg:gap-10">
      <div class="max-w-2xl">
        <div
          class="landing-fade-up"
          style="--delay: 0ms"
        >
          <UBadge
            label="Study essentials, made for students"
            icon="i-lucide-sparkles"
            variant="subtle"
            size="lg"
            class="rounded-full px-3"
          />
        </div>
        <h1
          class="landing-fade-up mt-6 text-4xl leading-[1.08] font-extrabold tracking-tight text-highlighted sm:text-5xl lg:text-6xl"
          style="--delay: 80ms"
        >
          Everything you need for
          <span class="landing-gradient-text">every chapter.</span>
        </h1>
        <p
          class="landing-fade-up mt-6 max-w-xl text-lg leading-relaxed text-muted"
          style="--delay: 160ms"
        >
          Pens, notebooks, art supplies and workbooks from StudyMart's own range. Browse by
          category, check what's in stock, and keep your picks in a cart that waits for you.
        </p>
        <div
          class="landing-fade-up mt-9 flex flex-wrap gap-3"
          style="--delay: 240ms"
        >
          <UButton
            :to="primaryAction.to"
            :label="primaryAction.label"
            trailing-icon="i-lucide-arrow-right"
            size="xl"
          />
          <UButton
            to="#featured"
            label="Browse essentials"
            color="neutral"
            variant="outline"
            size="xl"
          />
        </div>
        <ul
          class="landing-fade-up mt-10 flex flex-wrap gap-x-6 gap-y-3 text-sm text-muted"
          style="--delay: 320ms"
        >
          <li
            v-for="item in highlights"
            :key="item.label"
            class="flex items-center gap-2"
          >
            <UIcon
              :name="item.icon"
              class="size-4 text-primary"
              aria-hidden="true"
            />
            {{ item.label }}
          </li>
        </ul>
      </div>

      <div
        class="landing-fade-up relative mx-auto w-full max-w-lg lg:max-w-none"
        style="--delay: 200ms"
      >
        <div
          class="relative overflow-hidden rounded-[2rem] border border-default bg-elevated shadow-2xl shadow-primary/10"
        >
          <img
            src="/images/studymart-books-modern.png"
            alt="Green StudyMart notebooks, books and a pen on a desk"
            width="1122"
            height="1402"
            class="aspect-[4/5] w-full object-cover"
            fetchpriority="high"
          />
          <div
            class="absolute inset-0 bg-linear-to-t from-primary-950/40 via-transparent to-transparent"
            aria-hidden="true"
          />
        </div>

        <div
          v-if="spotlight"
          class="landing-float absolute -bottom-6 -left-4 w-64 rounded-2xl border border-default bg-default/90 p-4 shadow-xl backdrop-blur sm:-left-10"
        >
          <div class="flex items-center gap-3">
            <span
              class="grid size-11 shrink-0 place-items-center overflow-hidden rounded-xl bg-elevated"
            >
              <img
                v-if="spotlight.image_url"
                :src="spotlight.image_url"
                alt=""
                class="size-full object-cover"
              />
              <UIcon
                v-else
                name="i-lucide-pen-line"
                class="size-5 text-primary"
                aria-hidden="true"
              />
            </span>
            <div class="min-w-0">
              <p class="truncate text-sm font-semibold text-highlighted">{{ spotlight.name }}</p>
              <p class="text-sm font-bold text-primary">{{ formatMoney(spotlight.price) }}</p>
            </div>
          </div>
          <p class="mt-3 flex items-center gap-1.5 text-xs font-medium text-success">
            <UIcon
              name="i-lucide-circle-check"
              class="size-4"
              aria-hidden="true"
            />
            In stock and ready
          </p>
        </div>

        <div
          v-if="data"
          class="landing-float-slow absolute -top-5 -right-3 rounded-2xl border border-default bg-default/90 px-4 py-3 shadow-xl backdrop-blur sm:-right-8"
        >
          <p class="text-xs text-muted">Essentials in stock</p>
          <p class="text-2xl font-extrabold text-highlighted">
            <CountUp :value="data.stats.in_stock" />
          </p>
        </div>
      </div>
    </UContainer>
  </section>
</template>
