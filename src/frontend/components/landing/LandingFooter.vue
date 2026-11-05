<script setup lang="ts">
  const columns = [
    {
      title: 'Explore',
      links: [
        { label: 'Categories', to: '#categories' },
        { label: 'Featured', to: '#featured' },
        { label: 'How it works', to: '#how-it-works' },
        { label: 'FAQ', to: '#faq' }
      ]
    },
    {
      title: 'Account',
      links: [
        { label: 'Create account', to: '/register' },
        { label: 'Log in', to: '/login' },
        { label: 'Shop', to: '/shop' }
      ]
    }
  ]
  const year = new Date().getFullYear()
</script>

<template>
  <footer class="border-t border-default bg-default">
    <UContainer class="grid gap-10 py-14 md:grid-cols-[2fr_1fr_1fr]">
      <div>
        <StudyMartLogo />
        <p class="mt-4 max-w-sm text-sm text-muted">
          Study essentials for every chapter, from StudyMart's own range.
        </p>
      </div>
      <nav
        v-for="column in columns"
        :key="column.title"
        :aria-label="column.title"
      >
        <h2 class="text-sm font-semibold text-highlighted">{{ column.title }}</h2>
        <ul class="mt-4 flex flex-col gap-2.5 text-sm">
          <li
            v-for="link in column.links"
            :key="link.label"
          >
            <ULink
              :to="link.to"
              class="text-muted hover:text-primary"
            >
              {{ link.label }}
            </ULink>
          </li>
        </ul>
      </nav>
    </UContainer>
    <div class="border-t border-default">
      <UContainer class="flex flex-wrap items-center justify-between gap-3 py-6 text-sm text-muted">
        <p>© {{ year }} StudyMart. All rights reserved.</p>
        <p>A little more ready for every day of learning.</p>
      </UContainer>
    </div>
  </footer>
</template>
