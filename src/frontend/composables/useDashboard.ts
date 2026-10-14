import type { DashboardData, StockFilter } from '~/types/catalog'
export function useDashboard(query: Ref<string>, stock: Ref<StockFilter>) {
  const request = useFetch<{ data: DashboardData }>('/dashboard', {
    baseURL: useRuntimeConfig().public.apiBase,
    server: false,
    query: { q: query, stock }
  })
  useRequestNotification(request.error, 'the dashboard')
  return request
}
