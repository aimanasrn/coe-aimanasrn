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
    class="auth-scene"
    :class="{ 'auth-scene--compact': compact }"
  >
    <NuxtLink
      to="/"
      aria-label="StudyMart home"
      class="auth-brand"
    >
      <StudyMartLogo />
    </NuxtLink>

    <section
      class="auth-panel"
      aria-labelledby="auth-title"
    >
      <div class="auth-panel__content">
        <header class="auth-panel__header">
          <h1 id="auth-title">{{ title }}</h1>
          <p>{{ description }}</p>
        </header>
        <slot />
      </div>
    </section>

    <aside
      class="auth-story"
      aria-label="A new chapter with StudyMart"
    >
      <img
        class="auth-story__photo"
        src="/images/studymart-books-modern.png"
        alt=""
        width="1122"
        height="1402"
        fetchpriority="high"
        decoding="async"
      />
      <div class="auth-story__message">
        <h2><span>Great things</span><span>start with a page.</span></h2>
        <p>Your next chapter starts here.</p>
      </div>
    </aside>
  </main>
</template>
