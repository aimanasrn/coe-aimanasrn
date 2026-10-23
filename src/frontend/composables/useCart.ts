import type { Cart } from '~/types/shop'
export function useCart() {
  const { user } = useAuth()
  const accountId = user.value?.id
  const cart = useState<Cart | null>('shopping-cart-' + accountId, () => null)
  const busy = useState('cart-busy-' + accountId, () => false)
  const loading = useState('cart-loading-' + accountId, () => false)
  const error = useState('cart-error-' + accountId, () => '')
  const requestFetch = useRequestFetch()
  const notification = useActionToast()
  async function refresh() {
    loading.value = true
    error.value = ''
    try {
      const response = await requestFetch<{ data: Cart }>('/api/cart')
      cart.value = response.data
      error.value = ''
    } catch {
      error.value = 'We could not load your cart. Please try again.'
    } finally {
      loading.value = false
    }
    return { loaded: true }
  }
  async function mutate(
    path: string,
    method: 'POST' | 'PATCH' | 'DELETE',
    title: string,
    body?: Record<string, number>
  ) {
    if (busy.value) return false
    busy.value = true
    try {
      const response = await $fetch<{ data: Cart }>(path, { method, body, retry: 0 })
      cart.value = response.data
      error.value = ''
      notification.success(
        title,
        response.data.item_count +
          (response.data.item_count === 1 ? ' item' : ' items') +
          ' in your cart.'
      )
      return true
    } catch (failure: unknown) {
      const response = failure as {
        statusCode?: number
        data?: { message?: string; errors?: Record<string, string[]> }
      }
      if (response.statusCode === 401) {
        await navigateTo('/login', { replace: true })
        return false
      }
      notification.error(
        'Cart was not updated',
        response.data?.errors?.quantity?.[0] || response.data?.message || 'Please try again.'
      )
      await refresh()
      return false
    } finally {
      busy.value = false
    }
  }
  return {
    cart,
    busy,
    loading,
    error,
    refresh,
    add: (productId: number, quantity: number) =>
      mutate('/api/cart/items', 'POST', 'Added to cart', { product_id: productId, quantity }),
    update: (productId: number, quantity: number) =>
      mutate('/api/cart/items/' + productId, 'PATCH', 'Cart updated', { quantity }),
    remove: (productId: number) => mutate('/api/cart/items/' + productId, 'DELETE', 'Item removed'),
    clear: () => mutate('/api/cart', 'DELETE', 'Cart cleared')
  }
}
