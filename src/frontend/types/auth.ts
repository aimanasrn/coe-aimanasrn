export type UserRole = 'admin' | 'customer'

export interface AuthUser {
  id: number
  name: string
  email: string
  role: UserRole
  avatar_url: string | null
  created_at: string
}

export interface AuthTokenResponse {
  access_token: string
  expires_in: number
  data: AuthUser
}
