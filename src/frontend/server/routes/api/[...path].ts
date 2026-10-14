export default defineEventHandler((event) => {
  const config = useRuntimeConfig(event)
  const path = event.path.replace(/^\/api(?=\/|$)/, '')

  return proxyRequest(event, `${config.apiBase.replace(/\/$/, '')}${path}`)
})
