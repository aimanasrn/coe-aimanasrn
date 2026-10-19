import { authDestination } from '~/utils/auth'

export default defineNuxtRouteMiddleware(async (to) => {
  const adminPage =
    to.path === '/admin' || to.path.startsWith('/admin/') || to.path.startsWith('/products/')
  const shopPage = to.path === '/shop' || to.path.startsWith('/shop/') || to.path === '/cart'
  const protectedPage = adminPage || shopPage || to.path === '/profile'
  const guestPage = to.path === '/login' || to.path === '/register'
  if (!protectedPage && !guestPage) return

  const { fetchUser } = useAuth()
  const user = await fetchUser()

  if (protectedPage && !user) {
    return navigateTo({ path: '/login', query: { redirect: to.fullPath } }, { replace: true })
  }
  if (adminPage && user?.role !== 'admin') {
    return navigateTo('/profile?access=restricted', { replace: true })
  }
  if (shopPage && user?.role === 'admin') return navigateTo('/admin', { replace: true })
  if (to.path === '/profile') {
    to.meta.layout = user?.role === 'admin' ? 'admin' : 'shop'
  }
  if (guestPage && user) {
    return navigateTo(authDestination(to.query.redirect, user.role), { replace: true })
  }
})
