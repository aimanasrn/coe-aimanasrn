<script setup lang="ts">
  defineProps<{
    title: string
    description: string
  }>()

  useHead({ link: [{ rel: 'preload', as: 'image', href: '/images/studymart-books-modern.png' }] })
</script>

<template>
  <main class="grid min-h-svh bg-default lg:grid-cols-2">
    <div class="flex flex-col px-6 py-6 sm:px-10 lg:px-16">
      <div class="flex items-center justify-between gap-4">
        <NuxtLink
          to="/"
          aria-label="StudyMart home"
          class="rounded-md"
        >
          <StudyMartLogo />
        </NuxtLink>
        <ColorModeToggle />
      </div>

      <section
        class="mx-auto flex w-full max-w-md flex-1 flex-col justify-center py-12"
        aria-labelledby="auth-title"
      >
        <header class="mb-8">
          <h1
            id="auth-title"
            class="text-3xl font-bold tracking-tight text-highlighted"
          >
            {{ title }}
          </h1>
          <p class="mt-2 text-muted">{{ description }}</p>
        </header>
        <slot />
      </section>
    </div>

    <aside
      class="relative isolate m-3 hidden overflow-hidden rounded-3xl bg-primary-950 lg:block"
      aria-label="A new chapter with StudyMart"
    >
      <img
        class="absolute inset-0 -z-10 size-full object-cover"
        src="/images/studymart-books-modern.png"
        alt=""
        width="1122"
        height="1402"
        fetchpriority="high"
        decoding="async"
      />
      <div
        class="absolute inset-0 -z-10 bg-linear-to-b from-primary-950/70 via-primary-950/10 to-transparent"
      />
      <div class="px-[10%] pt-[clamp(64px,12vh,120px)]">
        <h2 class="text-[clamp(32px,3.5vw,54px)] leading-[1.1] font-bold tracking-tight text-white">
          Great things<br />start with a page.
        </h2>
        <p class="mt-4 text-[clamp(15px,1.3vw,20px)] text-white/80">
          Your next chapter starts here.
        </p>
      </div>
    </aside>
  </main>
</template>
