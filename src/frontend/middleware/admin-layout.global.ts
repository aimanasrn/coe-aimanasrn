export default defineNuxtRouteMiddleware((to) => {
  if (to.path === '/admin' || to.path.startsWith('/admin/')) {
    to.meta.layout = 'admin'
    to.meta.colorMode = 'light'
  }
})
