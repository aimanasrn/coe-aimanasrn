import type { ProductResponse } from '~/types/catalog'

export function useProduct(id: MaybeRefOrGetter<string>) {
  const request = useFetch<ProductResponse>(() => `/products/${encodeURIComponent(toValue(id))}`, {
    baseURL: useRuntimeConfig().public.apiBase,
    server: false,
    key: `product-${toValue(id)}`
  })
  useRequestNotification(request.error, 'this product')
  return request
}
