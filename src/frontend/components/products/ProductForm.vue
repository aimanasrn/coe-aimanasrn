<script setup lang="ts">
  import type { FormError, FormErrorEvent } from '@nuxt/ui'
  import type { Product, ProductImageEntry, ProductResponse } from '~/types/catalog'
  import ProductImageUpload from '~/components/products/ProductImageUpload.vue'

  const props = defineProps<{
    product?: Product
  }>()

  const emit = defineEmits<{
    created: [product: Product]
    updated: [product: Product]
    cancel: []
    busy: [value: boolean]
  }>()

  const config = useRuntimeConfig()
  const notification = useActionToast()
  const { data: categories, status, error, refresh } = useCategories()
  const productForm = useTemplateRef('productForm')
  const isEditing = computed(() => Boolean(props.product))
  const form = reactive({
    name: props.product?.name ?? '',
    category_id: props.product?.category ? String(props.product.category.id) : undefined,
    price: (props.product?.price ?? '') as string | number,
    stock_quantity: (props.product?.stock_quantity ?? 0) as string | number,
    description: props.product?.description ?? '',
    brand: props.product?.brand ?? '',
    barcode: props.product?.barcode ?? '',
    unit: props.product?.unit ?? 'item'
  })
  const images = ref<ProductImageEntry[]>(
    (props.product?.image_urls ?? []).map((url, index) => ({
      key: `existing-${index}`,
      url,
      existingIndex: index
    }))
  )
  const imageError = ref('')
  const unitItems = [
    { label: 'Item', value: 'item' },
    { label: 'Pack', value: 'pack' },
    { label: 'Set', value: 'set' },
    { label: 'Box', value: 'box' },
    { label: 'Digital download', value: 'download' }
  ]
  const categoryItems = computed(
    () =>
      categories.value?.data.map((category) => ({
        label: category.name,
        value: String(category.id)
      })) ?? []
  )
  const saving = ref(false)

  watch(images, () => {
    imageError.value = ''
    void productForm.value?.validate({ name: 'images', silent: true })
  })

  function validate(): FormError[] {
    const errors: FormError[] = []
    if (imageError.value) errors.push({ name: 'images', message: imageError.value })
    if (form.brand.length > 150)
      errors.push({ name: 'brand', message: 'Please use 150 characters or fewer.' })
    if (form.barcode.length > 64)
      errors.push({ name: 'barcode', message: 'Please use 64 characters or fewer.' })

    if (!form.name.trim()) errors.push({ name: 'name', message: 'Please fill in this field.' })
    else if (form.name.length > 150)
      errors.push({ name: 'name', message: 'Please use 150 characters or fewer.' })

    if (!form.category_id)
      errors.push({ name: 'category_id', message: 'Please select a category.' })
    else if (!categoryItems.value.some((category) => category.value === form.category_id)) {
      errors.push({ name: 'category_id', message: 'Please select a valid category.' })
    }

    for (const name of ['price', 'stock_quantity'] as const) {
      const value = form[name]
      const number = Number(value)
      const max = name === 'price' ? 9999999999.99 : 2147483647

      if (String(value).trim() === '') errors.push({ name, message: 'Please fill in this field.' })
      else if (!Number.isFinite(number) || number < 0 || number > max) {
        errors.push({ name, message: `Please enter a number between 0 and ${max}.` })
      } else if (name === 'stock_quantity' && !Number.isInteger(number)) {
        errors.push({ name, message: 'Please enter a whole number.' })
      } else if (name === 'price' && !/^\d+(\.\d{1,2})?$/.test(String(number))) {
        errors.push({ name, message: 'Please enter a price with at most 2 decimal places.' })
      }
    }

    return errors
  }

  function focusFirstError(event: FormErrorEvent) {
    const id = event.errors[0]?.id
    if (id) document.getElementById(id)?.focus()
  }

  async function submit() {
    if (saving.value) return

    saving.value = true
    emit('busy', true)

    try {
      const body = new FormData()
      for (const [name, value] of Object.entries(form)) body.append(name, String(value).trim())
      body.append(
        'retained_images',
        JSON.stringify(
          images.value
            .filter((image) => image.existingIndex !== undefined)
            .map((image) => image.existingIndex)
        )
      )
      for (const image of images.value) {
        if (image.file) body.append('images[]', image.file)
      }
      if (isEditing.value) body.append('_method', 'PUT')

      const response = await $fetch<ProductResponse>(
        props.product ? `/products/${props.product.id}` : '/products',
        {
          baseURL: config.public.apiBase,
          method: 'POST',
          body
        }
      )

      notification.success(
        isEditing.value ? 'Product updated' : 'Product added',
        isEditing.value
          ? `${response.data.name} has been updated successfully.`
          : `${response.data.name} has been added to your catalog.`
      )

      if (isEditing.value) emit('updated', response.data)
      else emit('created', response.data)
    } catch (error: unknown) {
      const response = error as { data?: { errors?: Record<string, string[]> } }
      const errors = Object.entries(response.data?.errors ?? {}).flatMap(([name, messages]) =>
        messages[0]
          ? [
              {
                name: /^(images|retained_images)(\.|$)/.test(name) ? 'images' : name,
                message: messages[0]
              }
            ]
          : []
      )

      productForm.value?.setErrors(errors)
      if (errors.length) {
        notification.warning('Please check the form', 'Please check the highlighted fields.')
      } else {
        notification.error(
          'Product was not saved',
          'We could not save this product. Please try again.'
        )
      }
    } finally {
      saving.value = false
      emit('busy', false)
    }
  }
</script>

<template>
  <div
    v-if="status === 'pending' || status === 'idle'"
    class="grid min-h-[235px] place-items-center"
  >
    <StudyMartLoader label="Loading categories" />
  </div>
  <UAlert
    v-else-if="error"
    color="error"
    variant="subtle"
    icon="i-lucide-circle-alert"
    title="Could not load categories."
    :actions="[
      { label: 'Try again', color: 'error', variant: 'outline', onClick: () => refresh() }
    ]"
  />
  <UAlert
    v-else-if="!categories?.data.length"
    color="info"
    variant="subtle"
    icon="i-lucide-info"
    title="Create a category before adding a product."
    :actions="[
      { label: 'Go to categories', color: 'info', variant: 'outline', to: '/admin/categories' }
    ]"
  />
  <UForm
    v-else
    ref="productForm"
    :state="form"
    :validate="validate"
    :validate-on="['blur', 'change', 'input']"
    :disabled="saving"
    class="flex flex-col gap-6"
    novalidate
    @submit="submit"
    @error="focusFirstError"
  >
    <div class="grid items-start gap-6 lg:grid-cols-[minmax(0,0.85fr)_minmax(0,1.4fr)]">
      <UCard
        as="section"
        aria-labelledby="product-images-heading"
      >
        <template #header>
          <h2
            id="product-images-heading"
            class="text-lg font-semibold text-highlighted"
          >
            Product images
          </h2>
          <p class="mt-1 text-sm text-muted">The first image is used as the cover.</p>
        </template>
        <UFormField
          name="images"
          :error="imageError || undefined"
        >
          <ProductImageUpload
            v-model="images"
            :disabled="saving"
            @error="imageError = $event"
          />
        </UFormField>
      </UCard>

      <UCard
        as="section"
        aria-labelledby="product-information-heading"
      >
        <template #header>
          <h2
            id="product-information-heading"
            class="text-lg font-semibold text-highlighted"
          >
            Product information
          </h2>
        </template>
        <div class="flex flex-col gap-5">
          <UFormField
            name="name"
            label="Product name"
            required
          >
            <UInput
              id="product-name"
              v-model="form.name"
              placeholder="Enter product name"
              required
              :maxlength="150"
              size="lg"
              class="w-full"
            />
          </UFormField>
          <div class="grid gap-5 sm:grid-cols-2">
            <UFormField
              label="SKU"
              :help="
                product
                  ? 'SKUs are assigned automatically and cannot be changed.'
                  : 'Created when you save, from the category and product number.'
              "
            >
              <UInput
                id="product-sku"
                :model-value="product?.sku ?? ''"
                placeholder="Generated automatically"
                icon="i-lucide-lock"
                readonly
                variant="soft"
                size="lg"
                class="w-full"
              />
            </UFormField>
            <UFormField
              name="category_id"
              label="Category"
              required
            >
              <USelect
                id="product-category"
                v-model="form.category_id"
                :items="categoryItems"
                placeholder="Select category"
                required
                size="lg"
                class="w-full"
              />
            </UFormField>
            <UFormField
              name="price"
              label="Price"
              required
            >
              <UInput
                id="product-price"
                v-model="form.price"
                type="number"
                placeholder="0.00"
                required
                :min="0"
                :max="9999999999.99"
                :step="0.01"
                size="lg"
                class="w-full"
                :ui="{ base: 'ps-12' }"
              >
                <template #leading>
                  <span class="text-sm text-muted">RM</span>
                </template>
              </UInput>
            </UFormField>
            <UFormField
              name="stock_quantity"
              label="Stock quantity"
              required
            >
              <UInput
                id="product-stock"
                v-model="form.stock_quantity"
                type="number"
                required
                :min="0"
                :max="2147483647"
                :step="1"
                size="lg"
                class="w-full"
              />
            </UFormField>
          </div>
          <UFormField
            name="description"
            label="Description"
            hint="Optional"
          >
            <UTextarea
              id="product-description"
              v-model="form.description"
              placeholder="Enter product description..."
              :rows="4"
              autoresize
              class="w-full"
            />
          </UFormField>

          <USeparator
            label="Additional details (optional)"
            class="my-1"
          />

          <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
            <UFormField
              name="brand"
              label="Brand / publisher"
            >
              <UInput
                id="product-brand"
                v-model="form.brand"
                placeholder="Enter brand or publisher"
                :maxlength="150"
                size="lg"
                class="w-full"
              />
            </UFormField>
            <UFormField
              name="barcode"
              label="Barcode / ISBN"
            >
              <UInput
                id="product-barcode"
                v-model="form.barcode"
                placeholder="Enter barcode or ISBN"
                :maxlength="64"
                size="lg"
                class="w-full"
              />
            </UFormField>
            <UFormField
              name="unit"
              label="Selling unit"
            >
              <USelect
                id="product-unit"
                v-model="form.unit"
                :items="unitItems"
                size="lg"
                class="w-full"
              />
            </UFormField>
          </div>
        </div>
      </UCard>
    </div>

    <div
      class="sticky bottom-0 z-10 -mx-4 flex justify-end gap-2 border-t border-default bg-muted/90 px-4 py-4 backdrop-blur sm:mx-0 sm:rounded-xl sm:border sm:bg-default/90 sm:px-5"
    >
      <UButton
        label="Cancel"
        color="neutral"
        variant="outline"
        size="lg"
        :disabled="saving"
        @click="emit('cancel')"
      />
      <UButton
        type="submit"
        size="lg"
        :loading="saving"
        :label="saving ? 'Saving product...' : isEditing ? 'Save changes' : 'Save product'"
      />
    </div>
  </UForm>
</template>
