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
const { data, status, error, refresh } = useProduct(() =>
  String(route.params.id)
)
const product = computed(() => data.value?.data)
const loading = computed(
  () => status.value === 'idle' || status.value === 'pending'
)
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
      <header class="mb-[30px] flex items-start justify-between gap-6 max-md:flex-col max-md:gap-5 [&_h1]:text-[clamp(26px,2.4vw,36px)] [&_h1]:leading-[1.25] [&_h1]:font-bold [&_h1]:tracking-[-1px] [&_h1]:text-heading [&_h1]:wrap-anywhere [&_h1]:max-md:text-[28px] [&_p]:mt-2 [&_p]:text-base [&_p]:leading-[1.6] [&_p]:text-[#6b7871]">
        <div>
          <h1>{{ product.name }}</h1>
          <p>
            {{ product.sku }}
            <span aria-hidden="true">·</span>
            {{ product.category?.name ?? 'Uncategorised' }}
          </p>
        </div>
        <div class="flex shrink-0 items-center gap-3 max-md:w-full max-md:justify-end [&>a]:px-[18px]! [&>a]:py-[11px]! [&>a]:text-[15px]! [&>button]:size-[46px]">
          <DeleteProductModal
            :product="product"
            @deleted="navigateTo('/admin/products')"
          />
          <NuxtLink
            :to="editTo"
            class="
              inline-flex items-center justify-center gap-[9px] rounded-[7px] border px-5 py-[13px] font-[inherit]
              text-[18px] leading-6 font-semibold whitespace-nowrap cursor-pointer transition-colors duration-150 max-md:px-4
              max-md:py-2.5 max-md:text-sm border-primary bg-primary text-white hover:bg-primary-hover [&_.iconify]:size-5
            "
          >
            <UIcon
              name="i-lucide-pencil"
              mode="svg"
              aria-hidden="true"
            />
            Edit product
          </NuxtLink>
        </div>
      </header>
      <div class="grid grid-cols-2 items-start gap-6 max-[1101px]:grid-cols-1">
        <ProductGallery
          :images="product.image_urls ?? []"
          :name="product.name"
          :edit-to="editTo"
        />
        <article class="min-w-0 rounded-[10px] border border-[#e0e6e2] bg-white p-[30px] max-md:p-[18px]">
          <p class="text-[clamp(32px,3vw,42px)] leading-[1.2] font-bold tracking-[-1px] text-heading">{{ formatMoney(product.price) }}</p>
          <div class="mt-[18px] flex flex-wrap items-center gap-3.5 text-sm leading-[1.5] text-[#6b7871]">
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
            class="
              mt-[26px] border-t border-[#e5eae7] pt-[26px] [&_h2]:mt-0 [&_h2]:mb-5 [&_h2]:text-xl [&_h2]:leading-[1.4]
              [&_h2]:font-[650] [&_h2]:text-heading [&_dl]:flex [&_dl]:flex-col [&_dl]:gap-[15px] [&_dl]:text-sm
              [&_dl]:leading-[1.5] [&_dl>div]:grid [&_dl>div]:grid-cols-[minmax(110px,1fr)_minmax(0,1.2fr)] [&_dl>div]:gap-4
              [&_dl>div]:max-md:grid-cols-[minmax(100px,1fr)_minmax(0,1.2fr)] [&_dt]:text-[#7b867f] [&_dd]:text-[#37463f]
              [&_dd]:wrap-anywhere
            "
            aria-labelledby="product-details-heading"
          >
            <h2 id="product-details-heading">Product details</h2>
            <dl>
              <div
                v-for="detail in details"
                :key="detail.label"
              >
                <dt>{{ detail.label }}</dt>
                <dd>{{ detail.value }}</dd>
              </div>
            </dl>
          </section>
          <section
            class="
              mt-[26px] border-t border-[#e5eae7] pt-[26px] [&_h2]:mt-0 [&_h2]:mb-5 [&_h2]:text-xl [&_h2]:leading-[1.4]
              [&_h2]:font-[650] [&_h2]:text-heading [&_p]:text-[15px] [&_p]:leading-[1.8] [&_p]:text-[#66746c]
              [&_p]:whitespace-pre-wrap [&_p]:wrap-anywhere
            "
            aria-labelledby="product-description-heading"
          >
            <h2 id="product-description-heading">Description</h2>
            <p>
              {{ product.description || 'No description has been added yet.' }}
            </p>
          </section>
        </article>
      </div>
    </template>
  </section>
</template>
