<template>
  <NuxtLink
    to="/admin/products/create"
    class="
      inline-flex items-center justify-center gap-[9px] rounded-[7px] border px-5 py-[13px] font-[inherit]
      text-[18px] leading-6 font-semibold whitespace-nowrap cursor-pointer transition-colors duration-150 max-md:px-4
      max-md:py-2.5 max-md:text-sm border-primary bg-primary text-white hover:bg-primary-hover [&_.iconify]:size-5
    "
  >
    <UIcon
      name="i-lucide-plus"
      mode="svg"
      aria-hidden="true"
    />
    Add product
  </NuxtLink>
</template>
