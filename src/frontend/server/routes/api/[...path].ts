import { assertSameOrigin, authCookieName } from '../../utils/auth'

export default defineEventHandler((event) => {
  const config = useRuntimeConfig(event)
  const path = event.path.replace(/^\/api(?=\/|$)/, '')

  if (!['GET', 'HEAD', 'OPTIONS'].includes(event.method)) assertSameOrigin(event)
  const token = getCookie(event, authCookieName)

  return proxyRequest(event, `${config.apiBase.replace(/\/$/, '')}${path}`, {
    headers: token ? { Authorization: `Bearer ${token}` } : undefined
  })
})
