<script setup lang="ts">
  import ProductBreadcrumbs from '~/components/products/ProductBreadcrumbs.vue'
  import ProductGallery from '~/components/products/ProductGallery.vue'
  import ProductLoadState from '~/components/products/ProductLoadState.vue'
  import StockBadge from '~/components/products/StockBadge.vue'
  import DeleteProductModal from '~/components/admin/DeleteProductModal.vue'
  import { formatMoney } from '~/utils/catalog'

  definePageMeta({
    layout: 'admin',
    colorMode: 'light',
    alias: ['/admin/products/:id']
  })
  const route = useRoute()
  const { data, status, error, refresh } = useProduct(() => String(route.params.id))
  const product = computed(() => data.value?.data)
  const loading = computed(() => status.value === 'idle' || status.value === 'pending')
  const editTo = computed(() => `/admin/products/${product.value?.id}/edit`)
  const unitLabels = {
    item: 'Item',
    pack: 'Pack',
    set: 'Set',
    box: 'Box',
    download: 'Digital download'
  }

  function formatDate(value?: string) {
    if (!value) return 'Not provided'
    return new Intl.DateTimeFormat('en-MY', {
      day: 'numeric',
      month: 'short',
      year: 'numeric',
      timeZone: 'Asia/Kuala_Lumpur'
    }).format(new Date(value))
  }

  const details = computed(() =>
    product.value
      ? [
          { label: 'SKU', value: product.value.sku },
          {
            label: 'Category',
            value: product.value.category?.name ?? 'Uncategorised'
          },
          {
            label: 'Brand / publisher',
            value: product.value.brand || 'Not provided'
          },
          {
            label: 'Barcode / ISBN',
            value: product.value.barcode || 'Not provided'
          },
          {
            label: 'Selling unit',
            value: unitLabels[product.value.unit ?? 'item']
          },
          { label: 'Added', value: formatDate(product.value.created_at) },
          { label: 'Last updated', value: formatDate(product.value.updated_at) }
        ]
      : []
  )

  useHead(() => ({
    title: product.value
      ? `${product.value.name} · StudyMart Admin`
      : 'Product details · StudyMart Admin'
  }))
</script>

<template>
  <section>
    <ProductBreadcrumbs
      mode="view"
      :product="product"
    />
    <ProductLoadState
      v-if="loading || error || !product"
      :loading="loading"
      :not-found="error?.statusCode === 404"
      @retry="refresh()"
    />
    <template v-else>
      <header
        class="
          flex items-start justify-between gap-5 mb-7 [&_>_:is(button,_a)]:flex-none max-md:flex-wrap
          max-md:gap-4.5 max-md:mb-6
        "
      >
        <div>
          <h1
            class="
              m-0 text-heading text-[34px] font-semibold leading-[1.2] tracking-[-0.9px] wrap-anywhere
              max-md:text-[30px]
            "
          >
            {{ product.name }}
          </h1>
          <p class="mt-[9px] text-muted text-base leading-[1.6] max-md:text-sm">
            {{ product.sku }}
            <span aria-hidden="true">·</span>
            {{ product.category?.name ?? 'Uncategorised' }}
          </p>
        </div>
        <div class="flex shrink-0 items-center gap-3 max-md:w-full max-md:justify-end">
          <DeleteProductModal
            :product="product"
            @deleted="navigateTo('/admin/products')"
          />
          <AppButton :to="editTo">
            <UIcon
              name="i-lucide-pencil"
              mode="svg"
              aria-hidden="true"
            />
            Edit product
          </AppButton>
        </div>
      </header>
      <div class="grid grid-cols-2 items-start gap-6 max-[1101px]:grid-cols-1">
        <ProductGallery
          :images="product.image_urls ?? []"
          :name="product.name"
          :edit-to="editTo"
        />
        <article
          class="min-w-0 p-6.5 border border-border rounded-xl bg-white max-md:py-5 max-md:px-4.5"
        >
          <p class="text-heading text-[36px] font-semibold leading-[1.2] tracking-[-0.8px]">
            {{ formatMoney(product.price) }}
          </p>
          <div
            class="mt-[18px] flex flex-wrap items-center gap-3.5 text-sm leading-[1.5] text-[#6b7871]"
          >
            <StockBadge
              :quantity="product.stock_quantity"
              :threshold="5"
            />
            <span>
              {{ product.stock_quantity }}
              {{ product.stock_quantity === 1 ? 'unit' : 'units' }} available
            </span>
          </div>
          <section
            class="mt-[26px] border-t border-[#e5eae7] pt-[26px]"
            aria-labelledby="product-details-heading"
          >
            <h2
              class="mt-0 mb-5 text-lg leading-[1.4] font-[650] text-heading"
              id="product-details-heading"
            >
              Product details
            </h2>
            <dl class="flex flex-col gap-[15px] text-sm leading-[1.5]">
              <div
                class="
                  grid grid-cols-[minmax(110px,1fr)_minmax(0,1.2fr)] gap-4
                  max-md:grid-cols-[minmax(100px,1fr)_minmax(0,1.2fr)]
                "
                v-for="detail in details"
                :key="detail.label"
              >
                <dt class="text-[#7b867f]">{{ detail.label }}</dt>
                <dd class="text-[#37463f] wrap-anywhere">{{ detail.value }}</dd>
              </div>
            </dl>
          </section>
          <section
            class="mt-[26px] border-t border-[#e5eae7] pt-[26px]"
            aria-labelledby="product-description-heading"
          >
            <h2
              class="mt-0 mb-5 text-lg leading-[1.4] font-[650] text-heading"
              id="product-description-heading"
            >
              Description
            </h2>
            <p class="text-[15px] leading-[1.8] text-[#66746c] whitespace-pre-wrap wrap-anywhere">
              {{ product.description || 'No description has been added yet.' }}
            </p>
          </section>
        </article>
      </div>
    </template>
  </section>
</template>
