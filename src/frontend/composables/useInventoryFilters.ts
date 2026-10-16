import type { ProductSort, StockFilter } from '~/types/catalog'

export function useInventoryFilters() {
  const route = useRoute()
  const search = ref(typeof route.query.q === 'string' ? route.query.q.slice(0, 150) : '')
  const query = ref(search.value)
  const stock = ref<StockFilter>(route.query.stock === 'low' || route.query.stock === 'out' ? route.query.stock : 'all')
  const page = ref(1)
  const itemsPerPage = ref(5)
  const sort = ref<ProductSort>({ column: 'id', direction: 'asc' })
  let timer: ReturnType<typeof setTimeout> | undefined
  watch(search, value => {
    clearTimeout(timer)
    timer = setTimeout(() => { page.value = 1; query.value = value.trim() }, 250)
  })
  watch(stock, () => { page.value = 1 })
  watch(itemsPerPage, () => { page.value = 1 }, { flush: 'sync' })
  watch(sort, () => { page.value = 1 }, { flush: 'sync' })
  onScopeDispose(() => clearTimeout(timer))
  return { search, query, stock, page, itemsPerPage, sort }
}
