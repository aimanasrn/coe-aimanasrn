import { authRequest } from '../../../utils/auth'
import type { AuthUser } from '~/types/auth'

export default defineEventHandler((event) => authRequest<{ data: AuthUser }>(event, 'profile'))
