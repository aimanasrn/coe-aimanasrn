import type { H3Event } from 'h3'

export const authCookieName = 'studymart_session'

export function assertSameOrigin(event: H3Event) {
  const origin = getRequestHeader(event, 'origin')
  if (
    getRequestHeader(event, 'sec-fetch-site') === 'cross-site' ||
    (origin && new URL(origin).host !== getRequestHeader(event, 'host'))
  ) {
    throw createError({ statusCode: 403, statusMessage: 'Request origin is not allowed.' })
  }
}

export function setAuthCookie(event: H3Event, token: string, expiresIn: number) {
  setCookie(event, authCookieName, token, {
    httpOnly: true,
    secure: getRequestURL(event).protocol === 'https:',
    sameSite: 'lax',
    path: '/',
    maxAge: expiresIn
  })
}

export function clearAuthCookie(event: H3Event) {
  deleteCookie(event, authCookieName, { path: '/' })
}

export async function authRequest<T>(
  event: H3Event,
  path: string,
  body?: Record<string, unknown>,
  method?: 'GET' | 'POST' | 'PATCH'
): Promise<T> {
  const token = getCookie(event, authCookieName)
  const config = useRuntimeConfig(event)

  setResponseHeader(event, 'Cache-Control', 'no-store')

  try {
    return await $fetch<T>(`${config.apiBase.replace(/\/$/, '')}/auth/${path}`, {
      method: method || (body !== undefined ? 'POST' : 'GET'),
      body,
      headers: {
        Accept: 'application/json',
        ...(token ? { Authorization: `Bearer ${token}` } : {})
      },
      retry: 0
    })
  } catch (error: unknown) {
    const failure = error as {
      statusCode?: number
      data?: { message?: string; errors?: Record<string, string[]> }
    }
    if (failure.statusCode === 401) clearAuthCookie(event)
    throw createError({
      statusCode: failure.statusCode || 502,
      statusMessage: failure.statusCode === 401 ? 'Unauthorized' : 'Authentication request failed',
      data: {
        message: failure.data?.message || 'Unable to connect. Please try again.',
        errors: failure.data?.errors
      }
    })
  }
}
