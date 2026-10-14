import type { StockFilter } from '~/types/catalog'

export function useInventoryFilters() {
  const route = useRoute()
  const search = ref(typeof route.query.q === 'string' ? route.query.q.slice(0, 150) : '')
  const query = ref(search.value)
  const stock = ref<StockFilter>(route.query.stock === 'low' || route.query.stock === 'out' ? route.query.stock : 'all')
  const page = ref(1)
  let timer: ReturnType<typeof setTimeout> | undefined
  watch(search, value => {
    clearTimeout(timer)
    timer = setTimeout(() => { page.value = 1; query.value = value.trim() }, 250)
  })
  watch(stock, () => { page.value = 1 })
  onScopeDispose(() => clearTimeout(timer))
  return { search, query, stock, page }
}
