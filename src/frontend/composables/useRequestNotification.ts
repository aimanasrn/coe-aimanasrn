export function useRequestNotification(
  error: Ref<{ statusCode?: number } | null | undefined>,
  resource: string
) {
  const notification = useActionToast()
  const authUser = useState('auth-user')

  watch(error, (value) => {
    if (!value) return

    if (value.statusCode === 401) {
      authUser.value = null
      void navigateTo('/login', { replace: true })
    } else if (value.statusCode === 404 && resource === 'this product') {
      notification.warning(
        'Product not found',
        'This product may have been removed from the catalog.'
      )
    } else {
      notification.error(
        `Could not load ${resource}`,
        'Please check your connection and try again.'
      )
    }
  })
}
