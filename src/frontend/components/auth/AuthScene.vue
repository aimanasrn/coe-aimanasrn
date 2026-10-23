<script setup lang="ts">
  withDefaults(
    defineProps<{
      title: string
      description: string
      compact?: boolean
    }>(),
    { compact: false }
  )

  useHead({ link: [{ rel: 'preload', as: 'image', href: '/images/studymart-books-modern.png' }] })
</script>

<template>
  <main
    class="
      relative grid min-h-svh grid-cols-[52%_48%] items-center bg-white p-4 text-heading
      max-[761px]:flex max-[761px]:flex-col max-[761px]:items-stretch max-[761px]:px-6
      max-[761px]:pt-7 max-[761px]:pb-9 max-[361px]:px-5
    "
    :class="{ '[&_form]:gap-4': compact }"
  >
    <NuxtLink
      to="/"
      aria-label="StudyMart home"
      class="
        absolute top-16 left-15 z-1 inline-flex items-center gap-2.5 rounded-md text-brand-deep
        focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-primary
        max-[1101px]:top-10 max-[1101px]:left-10 max-[761px]:static max-[761px]:self-start
        max-[761px]:gap-2
      "
    >
      <StudyMartLogo
        class="
          text-[30px]! font-[750]! leading-none [&_img]:size-9 max-[761px]:text-[23px]!
          max-[761px]:[&_img]:size-7.5
        "
      />
    </NuxtLink>

    <section
      class="
        col-start-1 row-start-1 mx-auto w-[min(440px,calc(100%_-_80px))]
        max-[1101px]:w-[calc(100%_-_64px)] max-[761px]:m-auto max-[761px]:w-full
        max-[761px]:max-w-[440px] max-[761px]:py-16
      "
      :class="compact ? 'mt-20 pt-11 pb-7' : 'mt-16 py-9'"
      aria-labelledby="auth-title"
    >
      <header :class="compact ? 'mb-6' : 'mb-8 max-[761px]:mb-7'">
        <h1
          id="auth-title"
          class="
            m-0 text-[34px] leading-[1.2] font-[650] tracking-[-1px] text-heading
            max-[761px]:text-[30px]
          "
        >
          {{ title }}
        </h1>
        <p class="mt-2.5 text-base leading-[1.6] text-muted">{{ description }}</p>
      </header>
      <slot />
    </section>

    <aside
      class="
        relative isolate col-start-2 row-start-1 min-h-[calc(100svh_-_32px)] self-stretch
        overflow-hidden rounded-3xl bg-brand-deep max-[761px]:hidden
      "
      aria-label="A new chapter with StudyMart"
    >
      <img
        class="absolute inset-0 -z-1 size-full object-cover object-center"
        src="/images/studymart-books-modern.png"
        alt=""
        width="1122"
        height="1402"
        fetchpriority="high"
        decoding="async"
      />
      <div class="px-[10%] pt-[clamp(68px,12vh,124px)] pb-10 max-[1101px]:px-[8%]">
        <h2
          class="m-0 text-[clamp(32px,3.5vw,56px)] leading-[1.12] font-semibold tracking-[-1.6px] text-white"
        >
          <span class="block">Great things</span>
          <span class="block">start with a page.</span>
        </h2>
        <p class="mt-4.5 text-[clamp(15px,1.4vw,21px)] leading-normal text-[#cedbd5]">
          Your next chapter starts here.
        </p>
      </div>
    </aside>
  </main>
</template>
