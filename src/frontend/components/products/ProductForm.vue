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
        inline-flex items-center gap-[9px] text-[15px] font-[550] text-primary cursor-pointer hover:text-primary-hover
        hover:underline hover:underline-offset-4 [&_.iconify]:size-[18px] [&_.iconify]:shrink-0
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
    class="flex flex-col gap-5 "
    novalidate
    @submit="submit"
    @error="focusFirstError"
  >
    <p
      v-if="message"
      class="mb-5 rounded-[7px] border px-[18px] py-3.5 text-sm border-[#f0d5d5] bg-[#fff4f4] text-[#a03939]"
      role="alert"
    >
      {{ message }}
    </p>
    <div class="grid grid-cols-[minmax(0,0.8fr)_minmax(0,1.2fr)] items-stretch gap-6 max-[1101px]:grid-cols-1">
      <section
        class="
          min-w-0 rounded-[10px] border border-[#e0e6e2] bg-white p-[26px] max-md:p-[18px] [&_h2]:mt-0 [&_h2]:mb-5
          [&_h2]:text-xl [&_h2]:leading-[1.4] [&_h2]:font-[650] [&_h2]:text-heading max-[1101px]:order-0
        "
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
        class="
          min-w-0 rounded-[10px] border border-[#e0e6e2] bg-white p-[26px] max-md:p-[18px] [&_h2]:mt-0 [&_h2]:mb-5
          [&_h2]:text-xl [&_h2]:leading-[1.4] [&_h2]:font-[650] [&_h2]:text-heading flex flex-col gap-[22px] [&>h2]:mb-0
        "
        aria-labelledby="product-information-heading"
      >
        <h2 id="product-information-heading">Product information</h2>
        <UFormField
          name="name"
          label="Product name"
          required
          class="
            flex flex-col gap-[7px] [&_label]:text-sm [&_label]:font-[550] [&_label]:text-[#374f40]
            [&_label_span]:font-normal [&_label_span]:text-[#7b867f] [&_input]:min-h-11 [&_input]:w-full
            [&_input]:rounded-md [&_input]:border [&_input]:border-[#dce4df] [&_input]:bg-white [&_input]:px-3
            [&_input]:py-2.5 [&_input]:font-[inherit] [&_input]:text-[15px] [&_input]:text-[#37463f]
            [&_input]:focus:outline-2 [&_input]:focus:outline-primary [&_input]:focus:outline-offset-1
            [&_textarea]:min-h-11 [&_textarea]:w-full [&_textarea]:rounded-md [&_textarea]:border
            [&_textarea]:border-[#dce4df] [&_textarea]:bg-white [&_textarea]:px-3 [&_textarea]:py-2.5
            [&_textarea]:font-[inherit] [&_textarea]:text-[15px] [&_textarea]:text-[#37463f] [&_textarea]:focus:outline-2
            [&_textarea]:focus:outline-primary [&_textarea]:focus:outline-offset-1 [&_select]:min-h-11 [&_select]:w-full
            [&_select]:rounded-md [&_select]:border [&_select]:border-[#dce4df] [&_select]:bg-white [&_select]:px-3
            [&_select]:py-2.5 [&_select]:font-[inherit] [&_select]:text-[15px] [&_select]:text-[#37463f]
            [&_select]:focus:outline-2 [&_select]:focus:outline-primary [&_select]:focus:outline-offset-1
            [&_[aria-invalid=true]]:border-[#b83d3d] [&>p]:text-xs [&>p]:text-[#b83d3d]
          "
        >
          <UInput
            id="product-name"
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
            class="
              flex flex-col gap-[7px] [&_label]:text-sm [&_label]:font-[550] [&_label]:text-[#374f40]
              [&_label_span]:font-normal [&_label_span]:text-[#7b867f] [&_input]:min-h-11 [&_input]:w-full
              [&_input]:rounded-md [&_input]:border [&_input]:border-[#dce4df] [&_input]:bg-white [&_input]:px-3
              [&_input]:py-2.5 [&_input]:font-[inherit] [&_input]:text-[15px] [&_input]:text-[#37463f]
              [&_input]:focus:outline-2 [&_input]:focus:outline-primary [&_input]:focus:outline-offset-1
              [&_textarea]:min-h-11 [&_textarea]:w-full [&_textarea]:rounded-md [&_textarea]:border
              [&_textarea]:border-[#dce4df] [&_textarea]:bg-white [&_textarea]:px-3 [&_textarea]:py-2.5
              [&_textarea]:font-[inherit] [&_textarea]:text-[15px] [&_textarea]:text-[#37463f] [&_textarea]:focus:outline-2
              [&_textarea]:focus:outline-primary [&_textarea]:focus:outline-offset-1 [&_select]:min-h-11 [&_select]:w-full
              [&_select]:rounded-md [&_select]:border [&_select]:border-[#dce4df] [&_select]:bg-white [&_select]:px-3
              [&_select]:py-2.5 [&_select]:font-[inherit] [&_select]:text-[15px] [&_select]:text-[#37463f]
              [&_select]:focus:outline-2 [&_select]:focus:outline-primary [&_select]:focus:outline-offset-1
              [&_[aria-invalid=true]]:border-[#b83d3d] [&>p]:text-xs [&>p]:text-[#b83d3d]
            "
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
            class="
              flex flex-col gap-[7px] [&_label]:text-sm [&_label]:font-[550] [&_label]:text-[#374f40]
              [&_label_span]:font-normal [&_label_span]:text-[#7b867f] [&_input]:min-h-11 [&_input]:w-full
              [&_input]:rounded-md [&_input]:border [&_input]:border-[#dce4df] [&_input]:bg-white [&_input]:px-3
              [&_input]:py-2.5 [&_input]:font-[inherit] [&_input]:text-[15px] [&_input]:text-[#37463f]
              [&_input]:focus:outline-2 [&_input]:focus:outline-primary [&_input]:focus:outline-offset-1
              [&_textarea]:min-h-11 [&_textarea]:w-full [&_textarea]:rounded-md [&_textarea]:border
              [&_textarea]:border-[#dce4df] [&_textarea]:bg-white [&_textarea]:px-3 [&_textarea]:py-2.5
              [&_textarea]:font-[inherit] [&_textarea]:text-[15px] [&_textarea]:text-[#37463f] [&_textarea]:focus:outline-2
              [&_textarea]:focus:outline-primary [&_textarea]:focus:outline-offset-1 [&_select]:min-h-11 [&_select]:w-full
              [&_select]:rounded-md [&_select]:border [&_select]:border-[#dce4df] [&_select]:bg-white [&_select]:px-3
              [&_select]:py-2.5 [&_select]:font-[inherit] [&_select]:text-[15px] [&_select]:text-[#37463f]
              [&_select]:focus:outline-2 [&_select]:focus:outline-primary [&_select]:focus:outline-offset-1
              [&_[aria-invalid=true]]:border-[#b83d3d] [&>p]:text-xs [&>p]:text-[#b83d3d]
            "
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
            class="
              flex flex-col gap-[7px] [&_label]:text-sm [&_label]:font-[550] [&_label]:text-[#374f40]
              [&_label_span]:font-normal [&_label_span]:text-[#7b867f] [&_input]:min-h-11 [&_input]:w-full
              [&_input]:rounded-md [&_input]:border [&_input]:border-[#dce4df] [&_input]:bg-white [&_input]:px-3
              [&_input]:py-2.5 [&_input]:font-[inherit] [&_input]:text-[15px] [&_input]:text-[#37463f]
              [&_input]:focus:outline-2 [&_input]:focus:outline-primary [&_input]:focus:outline-offset-1
              [&_textarea]:min-h-11 [&_textarea]:w-full [&_textarea]:rounded-md [&_textarea]:border
              [&_textarea]:border-[#dce4df] [&_textarea]:bg-white [&_textarea]:px-3 [&_textarea]:py-2.5
              [&_textarea]:font-[inherit] [&_textarea]:text-[15px] [&_textarea]:text-[#37463f] [&_textarea]:focus:outline-2
              [&_textarea]:focus:outline-primary [&_textarea]:focus:outline-offset-1 [&_select]:min-h-11 [&_select]:w-full
              [&_select]:rounded-md [&_select]:border [&_select]:border-[#dce4df] [&_select]:bg-white [&_select]:px-3
              [&_select]:py-2.5 [&_select]:font-[inherit] [&_select]:text-[15px] [&_select]:text-[#37463f]
              [&_select]:focus:outline-2 [&_select]:focus:outline-primary [&_select]:focus:outline-offset-1
              [&_[aria-invalid=true]]:border-[#b83d3d] [&>p]:text-xs [&>p]:text-[#b83d3d]
            "
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
            class="
              flex flex-col gap-[7px] [&_label]:text-sm [&_label]:font-[550] [&_label]:text-[#374f40]
              [&_label_span]:font-normal [&_label_span]:text-[#7b867f] [&_input]:min-h-11 [&_input]:w-full
              [&_input]:rounded-md [&_input]:border [&_input]:border-[#dce4df] [&_input]:bg-white [&_input]:px-3
              [&_input]:py-2.5 [&_input]:font-[inherit] [&_input]:text-[15px] [&_input]:text-[#37463f]
              [&_input]:focus:outline-2 [&_input]:focus:outline-primary [&_input]:focus:outline-offset-1
              [&_textarea]:min-h-11 [&_textarea]:w-full [&_textarea]:rounded-md [&_textarea]:border
              [&_textarea]:border-[#dce4df] [&_textarea]:bg-white [&_textarea]:px-3 [&_textarea]:py-2.5
              [&_textarea]:font-[inherit] [&_textarea]:text-[15px] [&_textarea]:text-[#37463f] [&_textarea]:focus:outline-2
              [&_textarea]:focus:outline-primary [&_textarea]:focus:outline-offset-1 [&_select]:min-h-11 [&_select]:w-full
              [&_select]:rounded-md [&_select]:border [&_select]:border-[#dce4df] [&_select]:bg-white [&_select]:px-3
              [&_select]:py-2.5 [&_select]:font-[inherit] [&_select]:text-[15px] [&_select]:text-[#37463f]
              [&_select]:focus:outline-2 [&_select]:focus:outline-primary [&_select]:focus:outline-offset-1
              [&_[aria-invalid=true]]:border-[#b83d3d] [&>p]:text-xs [&>p]:text-[#b83d3d]
            "
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
          class="
            flex flex-col gap-[7px] [&_label]:text-sm [&_label]:font-[550] [&_label]:text-[#374f40]
            [&_label_span]:font-normal [&_label_span]:text-[#7b867f] [&_input]:min-h-11 [&_input]:w-full
            [&_input]:rounded-md [&_input]:border [&_input]:border-[#dce4df] [&_input]:bg-white [&_input]:px-3
            [&_input]:py-2.5 [&_input]:font-[inherit] [&_input]:text-[15px] [&_input]:text-[#37463f]
            [&_input]:focus:outline-2 [&_input]:focus:outline-primary [&_input]:focus:outline-offset-1
            [&_textarea]:min-h-11 [&_textarea]:w-full [&_textarea]:rounded-md [&_textarea]:border
            [&_textarea]:border-[#dce4df] [&_textarea]:bg-white [&_textarea]:px-3 [&_textarea]:py-2.5
            [&_textarea]:font-[inherit] [&_textarea]:text-[15px] [&_textarea]:text-[#37463f] [&_textarea]:focus:outline-2
            [&_textarea]:focus:outline-primary [&_textarea]:focus:outline-offset-1 [&_select]:min-h-11 [&_select]:w-full
            [&_select]:rounded-md [&_select]:border [&_select]:border-[#dce4df] [&_select]:bg-white [&_select]:px-3
            [&_select]:py-2.5 [&_select]:font-[inherit] [&_select]:text-[15px] [&_select]:text-[#37463f]
            [&_select]:focus:outline-2 [&_select]:focus:outline-primary [&_select]:focus:outline-offset-1
            [&_[aria-invalid=true]]:border-[#b83d3d] [&>p]:text-xs [&>p]:text-[#b83d3d]
          "
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
        <div class="mt-1 border-t border-[#e5eae7] pt-6 [&>div]:grid-cols-[minmax(0,1.2fr)_minmax(0,1.2fr)_minmax(0,1fr)] [&>div]:gap-4 [&>div]:max-md:grid-cols-1 [&_h2_span]:ml-2 [&_h2_span]:text-[13px] [&_h2_span]:font-normal [&_h2_span]:text-[#89928c]">
          <h2>
            Additional details
            <span>Optional</span>
          </h2>
          <div class="grid grid-cols-2 gap-5 max-md:grid-cols-1 max-md:gap-[18px]">
            <UFormField
              name="brand"
              label="Brand / publisher"
              class="
                flex flex-col gap-[7px] [&_label]:text-sm [&_label]:font-[550] [&_label]:text-[#374f40]
                [&_label_span]:font-normal [&_label_span]:text-[#7b867f] [&_input]:min-h-11 [&_input]:w-full
                [&_input]:rounded-md [&_input]:border [&_input]:border-[#dce4df] [&_input]:bg-white [&_input]:px-3
                [&_input]:py-2.5 [&_input]:font-[inherit] [&_input]:text-[15px] [&_input]:text-[#37463f]
                [&_input]:focus:outline-2 [&_input]:focus:outline-primary [&_input]:focus:outline-offset-1
                [&_textarea]:min-h-11 [&_textarea]:w-full [&_textarea]:rounded-md [&_textarea]:border
                [&_textarea]:border-[#dce4df] [&_textarea]:bg-white [&_textarea]:px-3 [&_textarea]:py-2.5
                [&_textarea]:font-[inherit] [&_textarea]:text-[15px] [&_textarea]:text-[#37463f] [&_textarea]:focus:outline-2
                [&_textarea]:focus:outline-primary [&_textarea]:focus:outline-offset-1 [&_select]:min-h-11 [&_select]:w-full
                [&_select]:rounded-md [&_select]:border [&_select]:border-[#dce4df] [&_select]:bg-white [&_select]:px-3
                [&_select]:py-2.5 [&_select]:font-[inherit] [&_select]:text-[15px] [&_select]:text-[#37463f]
                [&_select]:focus:outline-2 [&_select]:focus:outline-primary [&_select]:focus:outline-offset-1
                [&_[aria-invalid=true]]:border-[#b83d3d] [&>p]:text-xs [&>p]:text-[#b83d3d]
              "
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
              class="
                flex flex-col gap-[7px] [&_label]:text-sm [&_label]:font-[550] [&_label]:text-[#374f40]
                [&_label_span]:font-normal [&_label_span]:text-[#7b867f] [&_input]:min-h-11 [&_input]:w-full
                [&_input]:rounded-md [&_input]:border [&_input]:border-[#dce4df] [&_input]:bg-white [&_input]:px-3
                [&_input]:py-2.5 [&_input]:font-[inherit] [&_input]:text-[15px] [&_input]:text-[#37463f]
                [&_input]:focus:outline-2 [&_input]:focus:outline-primary [&_input]:focus:outline-offset-1
                [&_textarea]:min-h-11 [&_textarea]:w-full [&_textarea]:rounded-md [&_textarea]:border
                [&_textarea]:border-[#dce4df] [&_textarea]:bg-white [&_textarea]:px-3 [&_textarea]:py-2.5
                [&_textarea]:font-[inherit] [&_textarea]:text-[15px] [&_textarea]:text-[#37463f] [&_textarea]:focus:outline-2
                [&_textarea]:focus:outline-primary [&_textarea]:focus:outline-offset-1 [&_select]:min-h-11 [&_select]:w-full
                [&_select]:rounded-md [&_select]:border [&_select]:border-[#dce4df] [&_select]:bg-white [&_select]:px-3
                [&_select]:py-2.5 [&_select]:font-[inherit] [&_select]:text-[15px] [&_select]:text-[#37463f]
                [&_select]:focus:outline-2 [&_select]:focus:outline-primary [&_select]:focus:outline-offset-1
                [&_[aria-invalid=true]]:border-[#b83d3d] [&>p]:text-xs [&>p]:text-[#b83d3d]
              "
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
              class="
                flex flex-col gap-[7px] [&_label]:text-sm [&_label]:font-[550] [&_label]:text-[#374f40]
                [&_label_span]:font-normal [&_label_span]:text-[#7b867f] [&_input]:min-h-11 [&_input]:w-full
                [&_input]:rounded-md [&_input]:border [&_input]:border-[#dce4df] [&_input]:bg-white [&_input]:px-3
                [&_input]:py-2.5 [&_input]:font-[inherit] [&_input]:text-[15px] [&_input]:text-[#37463f]
                [&_input]:focus:outline-2 [&_input]:focus:outline-primary [&_input]:focus:outline-offset-1
                [&_textarea]:min-h-11 [&_textarea]:w-full [&_textarea]:rounded-md [&_textarea]:border
                [&_textarea]:border-[#dce4df] [&_textarea]:bg-white [&_textarea]:px-3 [&_textarea]:py-2.5
                [&_textarea]:font-[inherit] [&_textarea]:text-[15px] [&_textarea]:text-[#37463f] [&_textarea]:focus:outline-2
                [&_textarea]:focus:outline-primary [&_textarea]:focus:outline-offset-1 [&_select]:min-h-11 [&_select]:w-full
                [&_select]:rounded-md [&_select]:border [&_select]:border-[#dce4df] [&_select]:bg-white [&_select]:px-3
                [&_select]:py-2.5 [&_select]:font-[inherit] [&_select]:text-[15px] [&_select]:text-[#37463f]
                [&_select]:focus:outline-2 [&_select]:focus:outline-primary [&_select]:focus:outline-offset-1
                [&_[aria-invalid=true]]:border-[#b83d3d] [&>p]:text-xs [&>p]:text-[#b83d3d]
              "
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
    <div class="flex justify-end gap-3 border-t border-[#dce4df] pt-5 [&>button]:px-[18px]! [&>button]:py-[11px]! [&>button]:text-[15px]!">
      <button
        type="button"
        class="
          inline-flex items-center justify-center gap-[9px] rounded-[7px] border px-5 py-[13px] font-[inherit]
          text-[18px] leading-6 font-semibold whitespace-nowrap cursor-pointer transition-colors duration-150 max-md:px-4
          max-md:py-2.5 max-md:text-sm border-[#dce4df] bg-white text-[#37463f] hover:bg-surface-muted
        "
        :disabled="saving"
        @click="emit('cancel')"
      >
        Cancel
      </button>
      <button
        type="submit"
        class="
          inline-flex items-center justify-center gap-[9px] rounded-[7px] border px-5 py-[13px] font-[inherit]
          text-[18px] leading-6 font-semibold whitespace-nowrap cursor-pointer transition-colors duration-150 max-md:px-4
          max-md:py-2.5 max-md:text-sm border-primary bg-primary text-white hover:bg-primary-hover [&_.iconify]:size-5
        "
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
