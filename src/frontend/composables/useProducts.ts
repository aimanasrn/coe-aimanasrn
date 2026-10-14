import type { ProductPage, StockFilter } from '~/types/catalog'
export function useProducts(
  query: Ref<string>,
  stock: Ref<StockFilter>,
  page: Ref<number>
) {
  const request = useFetch<ProductPage>('/products', {
    baseURL: useRuntimeConfig().public.apiBase,
    server: false,
    query: { q: query, stock, page }
  })
  useRequestNotification(request.error, 'products')
  return request
}
