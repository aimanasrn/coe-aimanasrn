import type { UserRole } from '~/types/auth'

export function authDestination(value: unknown, role: UserRole): string {
  if (role === 'customer') {
    if (
      typeof value === 'string' &&
      /^(?:\/shop(?:\/\d+)?|\/cart|\/profile)(?:\?[^\\\s#]*)?$/.test(value)
    )
      return value
    return '/shop'
  }
  if (value === '/profile') return '/profile'
  if (typeof value !== 'string') return '/admin'
  if (/^\/admin(?:\/[^\\\s?#]*)?(?:\?[^\\\s#]*)?$/.test(value)) return value
  if (/^\/products\/\d+$/.test(value)) return value
  return '/admin'
}

export function authFormError(error: unknown) {
  const failure = error as {
    statusCode?: number
    data?: { data?: { message?: string; errors?: Record<string, string[]> } }
  }
  return {
    message:
      failure.statusCode === 422
        ? 'Please check the highlighted fields.'
        : failure.statusCode === 429
          ? 'Too many attempts. Please wait a minute and try again.'
          : failure.data?.data?.message || 'Something went wrong. Please try again.',
    errors: failure.data?.data?.errors || {}
  }
}
