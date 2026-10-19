import { assertSameOrigin, authRequest, setAuthCookie } from '../../../utils/auth'
import type { AuthTokenResponse } from '~/types/auth'

export default defineEventHandler(async (event) => {
  assertSameOrigin(event)
  const body = await readBody(event)
  const result = await authRequest<AuthTokenResponse>(event, 'register', body)
  setAuthCookie(event, result.access_token, result.expires_in)
  setResponseStatus(event, 201)
  return { data: result.data }
})
