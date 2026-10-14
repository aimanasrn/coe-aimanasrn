import type { Category } from '~/types/catalog'
export function useCategories() {
  const request = useFetch<{ data: Category[] }>('/categories', {
    baseURL: useRuntimeConfig().public.apiBase,
    server: false
  })
  useRequestNotification(request.error, 'categories')
  return request
}
