import type { ProductPage, ProductSort, StockFilter } from '~/types/catalog'
export function useProducts(
  query: Ref<string>,
  stock: Ref<StockFilter>,
  page: Ref<number>,
  itemsPerPage: Ref<number>,
  sort: Ref<ProductSort>
) {
  const request = useFetch<ProductPage>('/products', {
    baseURL: useRuntimeConfig().public.apiBase,
    server: false,
    query: {
      q: query,
      stock,
      page,
      per_page: itemsPerPage,
      sort_by: computed(() => sort.value.column),
      sort_direction: computed(() => sort.value.direction)
    }
  })
  useRequestNotification(request.error, 'products')
  return request
}
