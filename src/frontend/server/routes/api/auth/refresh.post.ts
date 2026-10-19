import { assertSameOrigin, authRequest, setAuthCookie } from '../../../utils/auth'

export default defineEventHandler(async (event) => {
  assertSameOrigin(event)
  const result = await authRequest<{ access_token: string; expires_in: number }>(
    event,
    'refresh',
    {}
  )
  setAuthCookie(event, result.access_token, result.expires_in)
  return { success: true }
})
