import { assertSameOrigin, authRequest, clearAuthCookie } from '../../../utils/auth'

export default defineEventHandler(async (event) => {
  assertSameOrigin(event)
  try {
    await authRequest(event, 'logout', {})
  } catch (error: unknown) {
    if ((error as { statusCode?: number }).statusCode !== 401) throw error
  }
  clearAuthCookie(event)
  return { success: true }
})
