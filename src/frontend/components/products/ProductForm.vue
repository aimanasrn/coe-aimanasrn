<script setup lang="ts">
import type { FormError, FormErrorEvent } from '@nuxt/ui'
import type {
  Product,
  ProductImageEntry,
  ProductResponse
} from '~/types/catalog'
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
  category_id: props.product?.category
    ? String(props.product.category.id)
    : undefined,
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
const message = ref('')

watch(images, () => {
  imageError.value = ''
  void productForm.value?.validate({ name: 'images', silent: true })
})

function validate(): FormError[] {
  const errors: FormError[] = []
  if (imageError.value)
    errors.push({ name: 'images', message: imageError.value })
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
    if (!form[name].trim())
      errors.push({ name, message: 'Please fill in this field.' })
    else if (form[name].length > max)
      errors.push({ name, message: `Please use ${max} characters or fewer.` })
  }

  if (!form.category_id)
    errors.push({ name: 'category_id', message: 'Please select a category.' })
  else if (
    !categoryItems.value.some((category) => category.value === form.category_id)
  ) {
    errors.push({
      name: 'category_id',
      message: 'Please select a valid category.'
    })
  }

  for (const name of ['price', 'stock_quantity'] as const) {
    const value = form[name]
    const number = Number(value)
    const max = name === 'price' ? 9999999999.99 : 2147483647

    if (String(value).trim() === '')
      errors.push({ name, message: 'Please fill in this field.' })
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
  notification.warning(
    'Please check the form',
    'Complete the required fields and correct the highlighted errors.'
  )
  const id = event.errors[0]?.id
  if (id) document.getElementById(id)?.focus()
}

async function submit() {
  if (saving.value) return

  saving.value = true
  emit('busy', true)
  message.value = ''

  try {
    const body = new FormData()
    for (const [name, value] of Object.entries(form))
      body.append(name, String(value).trim())
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
    const errors = Object.entries(response.data?.errors ?? {}).flatMap(
      ([name, messages]) =>
        messages[0]
          ? [
              {
                name: /^(images|retained_images)(\.|$)/.test(name)
                  ? 'images'
                  : name,
                message: messages[0]
              }
            ]
          : []
    )

    productForm.value?.setErrors(errors)
    message.value = errors.length
      ? 'Please check the highlighted fields.'
      : 'We could not save this product. Please try again.'
    if (errors.length) {
      notification.warning('Please check the form', message.value)
    } else {
      notification.error('Product was not saved', message.value)
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
    class="sm-admin-table-loading"
  >
    <StudyMartLoader label="Loading categories" />
  </div>
  <div
    v-else-if="error"
    class="sm-admin-notice sm-admin-notice-error"
  >
    Could not load categories.
    <button
      class="sm-admin-link"
      @click="refresh()"
    >
      Try again
    </button>
  </div>
  <p
    v-else-if="!categories?.data.length"
    class="sm-admin-notice"
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
    class="sm-admin-form sm-product-form"
    novalidate
    @submit="submit"
    @error="focusFirstError"
  >
    <p
      v-if="message"
      class="sm-admin-notice sm-admin-notice-error"
      role="alert"
    >
      {{ message }}
    </p>
    <div class="sm-product-editor-grid">
      <section
        class="sm-product-editor-panel sm-product-images-panel"
        aria-labelledby="product-images-heading"
      >
        <h2 id="product-images-heading">Product images</h2>
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
        class="sm-product-editor-panel sm-product-information"
        aria-labelledby="product-information-heading"
      >
        <h2 id="product-information-heading">Product information</h2>
        <UFormField
          name="name"
          label="Product name"
          required
          class="sm-admin-field"
        >
          <UInput
            id="product-name"
            v-model="form.name"
            required
            :maxlength="150"
            class="w-full"
          />
        </UFormField>
        <div class="sm-admin-form-grid">
          <UFormField
            name="sku"
            label="SKU"
            required
            class="sm-admin-field"
          >
            <UInput
              id="product-sku"
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
            class="sm-admin-field"
          >
            <USelect
              id="product-category"
              v-model="form.category_id"
              :items="categoryItems"
              placeholder="Select category"
              required
              class="w-full"
              size="xl"
            />
          </UFormField>
          <UFormField
            name="price"
            label="Price (RM)"
            required
            class="sm-admin-field"
          >
            <UInput
              id="product-price"
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
            class="sm-admin-field"
          >
            <UInput
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
          class="sm-admin-field"
        >
          <template #label>
            Description
            <span>(optional)</span>
          </template>
          <UTextarea
            id="product-description"
            v-model="form.description"
            :rows="3"
            class="w-full"
          />
        </UFormField>
        <div class="sm-product-extra-details">
          <h2>
            Additional details
            <span>Optional</span>
          </h2>
          <div class="sm-admin-form-grid">
            <UFormField
              name="brand"
              label="Brand / publisher"
              class="sm-admin-field"
            >
              <UInput
                id="product-brand"
                v-model="form.brand"
                :maxlength="150"
                class="w-full"
              />
            </UFormField>
            <UFormField
              name="barcode"
              label="Barcode / ISBN"
              class="sm-admin-field"
            >
              <UInput
                id="product-barcode"
                v-model="form.barcode"
                :maxlength="64"
                class="w-full"
              />
            </UFormField>
            <UFormField
              name="unit"
              label="Selling unit"
              class="sm-admin-field"
            >
              <USelect
                id="product-unit"
                v-model="form.unit"
                :items="unitItems"
                class="w-full"
                size="xl"
              />
            </UFormField>
          </div>
        </div>
      </section>
    </div>
    <div class="sm-admin-form-actions sm-product-form-footer">
      <button
        type="button"
        class="sm-admin-button-secondary"
        :disabled="saving"
        @click="emit('cancel')"
      >
        Cancel
      </button>
      <button
        type="submit"
        class="sm-admin-button"
        :disabled="saving"
      >
        {{
          saving
            ? 'Saving product...'
            : isEditing
              ? 'Save changes'
              : 'Save product'
        }}
      </button>
    </div>
  </UForm>
</template>
