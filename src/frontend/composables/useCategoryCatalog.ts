import type { CategoryPage, CategoryProductFilter, CategorySort } from '~/types/catalog'

export function useCategoryCatalog() {
  const route = useRoute()
  const search = ref(typeof route.query.q === 'string' ? route.query.q.slice(0, 150) : '')
  const query = ref(search.value.trim())
  const products = ref<CategoryProductFilter>('all')
  const page = ref(1)
  const itemsPerPage = ref(5)
  const sort = ref<CategorySort>({ column: 'name', direction: 'asc' })
  let searchTimer: ReturnType<typeof setTimeout> | undefined

  watch(search, (value) => {
    clearTimeout(searchTimer)
    searchTimer = setTimeout(() => {
      page.value = 1
      query.value = value.trim()
    }, 250)
  })

  watch(
    [products, itemsPerPage, sort],
    () => {
      page.value = 1
    },
    { flush: 'sync' }
  )

  onScopeDispose(() => clearTimeout(searchTimer))

  const request = useFetch<CategoryPage>('/categories', {
    key: 'admin-category-catalog',
    baseURL: useRuntimeConfig().public.apiBase,
    server: false,
    query: {
      q: query,
      products,
      page,
      per_page: itemsPerPage,
      sort_by: computed(() => sort.value.column),
      sort_direction: computed(() => sort.value.direction)
    }
  })

  watch(request.data, (response) => {
    if (response && page.value > response.last_page) page.value = response.last_page
  })

  useRequestNotification(request.error, 'categories')

  return { ...request, search, products, page, itemsPerPage, sort }
}
