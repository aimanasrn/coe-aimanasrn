import { assertSameOrigin, authRequest, setAuthCookie } from '../../../utils/auth'

export default defineEventHandler(async (event) => {
  assertSameOrigin(event)
  const body = await readBody(event)
  const credentials = { email: body?.email, password: body?.password }
  const result = await authRequest<{ access_token: string; expires_in: number }>(
    event,
    'login',
    credentials
  )

  setAuthCookie(event, result.access_token, result.expires_in)
  return { success: true }
})
