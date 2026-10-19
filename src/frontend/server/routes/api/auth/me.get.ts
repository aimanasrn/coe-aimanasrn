import { authRequest, authCookieName } from '../../../utils/auth'
import type { AuthUser } from '~/types/auth'

export default defineEventHandler((event) => {
  setResponseHeader(event, 'Cache-Control', 'no-store')
  if (!getCookie(event, authCookieName)) {
    throw createError({ statusCode: 401, statusMessage: 'Unauthorized' })
  }
  return authRequest<{ data: AuthUser }>(event, 'me')
})
