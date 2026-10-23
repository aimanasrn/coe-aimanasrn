<script setup lang="ts">
  import { catalogFormUi } from '~/utils/formUi'
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
    sku: props.product?.sku ?? '',
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
      errors.push({
        name: 'brand',
        message: 'Please use 150 characters or fewer.'
      })
    if (form.barcode.length > 64)
      errors.push({
        name: 'barcode',
        message: 'Please use 64 characters or fewer.'
      })

    for (const [name, max] of [
      ['name', 150],
      ['sku', 64]
    ] as const) {
      if (!form[name].trim()) errors.push({ name, message: 'Please fill in this field.' })
      else if (form[name].length > max)
        errors.push({ name, message: `Please use ${max} characters or fewer.` })
    }

    if (!form.category_id)
      errors.push({ name: 'category_id', message: 'Please select a category.' })
    else if (!categoryItems.value.some((category) => category.value === form.category_id)) {
      errors.push({
        name: 'category_id',
        message: 'Please select a valid category.'
      })
    }

    for (const name of ['price', 'stock_quantity'] as const) {
      const value = form[name]
      const number = Number(value)
      const max = name === 'price' ? 9999999999.99 : 2147483647

      if (String(value).trim() === '') errors.push({ name, message: 'Please fill in this field.' })
      else if (!Number.isFinite(number) || number < 0 || number > max) {
        errors.push({
          name,
          message: `Please enter a number between 0 and ${max}.`
        })
      } else if (name === 'stock_quantity' && !Number.isInteger(number)) {
        errors.push({ name, message: 'Please enter a whole number.' })
      } else if (name === 'price' && !/^\d+(\.\d{1,2})?$/.test(String(number))) {
        errors.push({
          name,
          message: 'Please enter a price with at most 2 decimal places.'
        })
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
      const message = errors.length
        ? 'Please check the highlighted fields.'
        : 'We could not save this product. Please try again.'
      if (errors.length) {
        notification.warning('Please check the form', message)
      } else {
        notification.error('Product was not saved', message)
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
    class="grid min-h-[235px] place-items-center p-[26px]"
  >
    <StudyMartLoader label="Loading categories" />
  </div>
  <div
    v-else-if="error"
    class="mb-5 rounded-[7px] border px-[18px] py-3.5 text-sm border-[#f0d5d5] bg-[#fff4f4] text-[#a03939]"
  >
    Could not load categories.
    <button
      class="
        inline-flex items-center gap-2 rounded-[3px] text-primary text-sm font-medium leading-normal
        cursor-pointer [&_.iconify]:w-4.5 [&_.iconify]:h-4.5 [&_.iconify]:flex-none
        hover:text-primary-hover hover:underline hover:underline-offset-4
      "
      @click="refresh()"
    >
      Try again
    </button>
  </div>
  <p
    v-else-if="!categories?.data.length"
    class="mb-5 rounded-[7px] border px-[18px] py-3.5 text-sm border-[#d3ead9] bg-[#edf8f0] text-[#25683b]"
  >
    Create a category in your catalog before adding a product.
  </p>
  <UForm
    v-else
    ref="productForm"
    :state="form"
    :validate="validate"
    :validate-on="['blur', 'change', 'input']"
    :disabled="saving"
    class="flex flex-col gap-5"
    novalidate
    @submit="submit"
    @error="focusFirstError"
  >
    <div
      class="
        grid grid-cols-[minmax(0,_0.8fr)_minmax(0,_1.4fr)] items-stretch gap-5.5
        max-[1101px]:grid-cols-1
      "
    >
      <section
        class="
          min-w-0 overflow-hidden border border-border rounded-xl bg-white p-6.5 max-md:py-5
          max-md:px-4.5
        "
        aria-labelledby="product-images-heading"
      >
        <h2
          class="
            m-0 text-heading text-xl font-semibold leading-[1.4] tracking-[-0.4px] max-md:text-lg mb-5.5
            max-md:mb-5
          "
          id="product-images-heading"
        >
          Product images
        </h2>
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
      </section>
      <section
        class="
          min-w-0 overflow-hidden border border-border rounded-xl bg-white p-6.5 max-md:py-5
          max-md:px-4.5 flex flex-col gap-5
        "
        aria-labelledby="product-information-heading"
      >
        <h2
          class="
            m-0 text-heading text-xl font-semibold leading-[1.4] tracking-[-0.4px] max-md:text-lg
            max-md:mb-5 mb-0
          "
          id="product-information-heading"
        >
          Product information
        </h2>
        <UFormField
          name="name"
          label="Product name"
          required
          :ui="catalogFormUi.field"
        >
          <UInput
            :ui="catalogFormUi.input"
            id="product-name"
            placeholder="Enter product name"
            v-model="form.name"
            required
            :maxlength="150"
            class="w-full"
          />
        </UFormField>
        <div class="grid grid-cols-2 gap-5 max-md:grid-cols-1 max-md:gap-[18px]">
          <UFormField
            name="sku"
            label="SKU"
            required
            :ui="catalogFormUi.field"
          >
            <UInput
              :ui="catalogFormUi.input"
              id="product-sku"
              placeholder="Enter SKU"
              v-model="form.sku"
              required
              :maxlength="64"
              class="w-full"
            />
          </UFormField>
          <UFormField
            name="category_id"
            label="Category"
            required
            :ui="catalogFormUi.field"
          >
            <USelect
              :ui="catalogFormUi.select"
              id="product-category"
              v-model="form.category_id"
              :items="categoryItems"
              placeholder="Select category"
              required
              class="w-full"
              size="lg"
            />
          </UFormField>
          <UFormField
            name="price"
            label="Price (RM)"
            required
            :ui="catalogFormUi.field"
          >
            <UInput
              :ui="catalogFormUi.input"
              id="text-heading text-[36px] font-semibold leading-[1.2] tracking-[-0.8px]"
              placeholder="0.00"
              v-model="form.price"
              type="number"
              required
              :min="0"
              :max="9999999999.99"
              :step="0.01"
              class="w-full"
            />
          </UFormField>
          <UFormField
            name="stock_quantity"
            label="Stock quantity"
            required
            :ui="catalogFormUi.field"
          >
            <UInput
              :ui="catalogFormUi.input"
              id="product-stock"
              v-model="form.stock_quantity"
              type="number"
              required
              :min="0"
              :max="2147483647"
              :step="1"
              class="w-full"
            />
          </UFormField>
        </div>
        <UFormField
          name="description"
          :ui="catalogFormUi.field"
        >
          <template #label>
            Description
            <span>(optional)</span>
          </template>
          <UTextarea
            :ui="catalogFormUi.textarea"
            id="product-description"
            placeholder="Enter product description..."
            v-model="form.description"
            :rows="3"
            class="w-full"
          />
        </UFormField>
        <div class="mt-1 border-t border-[#e5eae7] pt-6">
          <h2
            class="m-0 text-heading text-xl font-semibold leading-[1.4] tracking-[-0.4px] max-md:text-lg"
          >
            Additional details
            <span class="ml-2 text-[13px] font-normal text-[#89928c]">Optional</span>
          </h2>
          <div
            class="
              grid max-md:gap-[18px] mt-4.5 grid-cols-[minmax(0,1.2fr)_minmax(0,1.2fr)_minmax(0,1fr)]
              gap-4 max-md:grid-cols-1
            "
          >
            <UFormField
              name="brand"
              label="Brand / publisher"
              :ui="catalogFormUi.field"
            >
              <UInput
                :ui="catalogFormUi.input"
                id="product-brand"
                placeholder="Enter brand or publisher"
                v-model="form.brand"
                :maxlength="150"
                class="w-full"
              />
            </UFormField>
            <UFormField
              name="barcode"
              label="Barcode / ISBN"
              :ui="catalogFormUi.field"
            >
              <UInput
                :ui="catalogFormUi.input"
                id="product-barcode"
                placeholder="Enter barcode or ISBN"
                v-model="form.barcode"
                :maxlength="64"
                class="w-full"
              />
            </UFormField>
            <UFormField
              name="unit"
              label="Selling unit"
              :ui="catalogFormUi.field"
            >
              <USelect
                :ui="catalogFormUi.select"
                id="product-unit"
                v-model="form.unit"
                :items="unitItems"
                class="w-full"
                size="lg"
              />
            </UFormField>
          </div>
        </div>
      </section>
    </div>
    <div class="flex justify-end gap-3 pt-1">
      <AppButton
        type="button"
        variant="secondary"
        :disabled="saving"
        @click="emit('cancel')"
      >
        Cancel
      </AppButton>
      <AppButton
        type="submit"
        :disabled="saving"
      >
        {{ saving ? 'Saving product...' : isEditing ? 'Save changes' : 'Save product' }}
      </AppButton>
    </div>
  </UForm>
</template>
