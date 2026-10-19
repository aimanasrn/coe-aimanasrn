import { assertSameOrigin, authRequest, setAuthCookie } from '../../../utils/auth'
import type { AuthTokenResponse } from '~/types/auth'

export default defineEventHandler(async (event) => {
  assertSameOrigin(event)
  const body = await readBody(event)
  const result = await authRequest<AuthTokenResponse>(event, 'profile', body, 'PATCH')
  setAuthCookie(event, result.access_token, result.expires_in)
  return { data: result.data }
})
