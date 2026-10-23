<script setup lang="ts">
  import SidebarNavigation from '~/components/admin/SidebarNavigation.vue'
  const isMenuOpen = ref(false)
</script>

<template>
  <aside
    aria-label="Admin sidebar"
    class="
      fixed z-30 top-3 right-auto bottom-3 left-3 block w-62 overflow-y-auto rounded-[20px]
      bg-brand-deep text-[#d5e3dc] [&_:is(a,_button):focus-visible]:outline-2
      [&_:is(a,_button):focus-visible]:outline-solid
      [&_:is(a,_button):focus-visible]:outline-[#a7d5ba]
      [&_:is(a,_button):focus-visible]:outline-offset-3 max-md:hidden
    "
  >
    <SidebarNavigation />
  </aside>
  <header
    class="
      hidden max-md:fixed max-md:z-35 max-md:top-0 max-md:right-0 max-md:bottom-auto max-md:left-0
      max-md:flex max-md:items-center max-md:h-19 max-md:py-4.5 max-md:px-5 max-md:border-b
      max-md:border-border max-md:bg-white max-md:[&_a_>_span]:text-[24px] max-md:[&_img]:w-7.5
      max-md:[&_img]:h-7.5
    "
  >
    <NuxtLink
      to="/admin"
      aria-label="StudyMart admin home"
      ><StudyMartLogo
    /></NuxtLink>
  </header>
  <UDrawer
    v-model:open="isMenuOpen"
    direction="left"
    title="StudyMart Admin"
    description="Admin navigation"
    :handle="false"
    :ui="{
      content: [
        '[&_:is(a,_button):focus-visible]:outline-2',
        '[&_:is(a,_button):focus-visible]:outline-solid',
        '[&_:is(a,_button):focus-visible]:outline-[#a7d5ba]',
        '[&_:is(a,_button):focus-visible]:outline-offset-3 flex-col w-75 max-w-[90vw]',
        'bg-brand-deep text-[#d5e3dc]'
      ].join(' '),
      overlay: 'bg-[#10251c]/35'
    }"
  >
    <button
      type="button"
      aria-label="Open navigation"
      class="
        fixed top-4.5 right-5 z-40 hidden items-center justify-center w-10 h-10 border border-border
        rounded-lg bg-white text-heading cursor-pointer max-md:inline-flex
      "
    >
      <UIcon
        name="i-lucide-menu"
        class="size-5"
        aria-hidden="true"
      />
    </button>
    <template #content>
      <div class="flex h-full w-full flex-col overflow-y-auto">
        <div class="flex justify-end px-4 pt-3">
          <button
            type="button"
            aria-label="Close navigation"
            class="p-2 rounded-md text-[#d5e3dc] cursor-pointer"
            @click="isMenuOpen = false"
          >
            <UIcon
              name="i-lucide-x"
              class="size-5"
              aria-hidden="true"
            />
          </button>
        </div>
        <div class="min-h-0 flex-1"><SidebarNavigation @navigate="isMenuOpen = false" /></div>
      </div>
    </template>
  </UDrawer>
</template>
