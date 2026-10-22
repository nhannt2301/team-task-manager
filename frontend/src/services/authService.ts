import type { AuthResponse, LoginCredentials, User } from '@/types/auth'
import { apiClient } from './api'

export async function login(credentials: LoginCredentials): Promise<AuthResponse> {
  const response = await apiClient.post<{ data: AuthResponse }>('/login', credentials)
  return response.data.data
}

export async function getMe(): Promise<User> {
  const response = await apiClient.get<{ data: User }>('/me')
  return response.data.data
}

export async function logout(): Promise<void> {
  await apiClient.post('/logout')
}
