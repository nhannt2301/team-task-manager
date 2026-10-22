export type UserRole = 'admin' | 'user'

export interface User {
  id: number
  name: string
  email: string
  role: UserRole
  created_at?: string
  updated_at?: string
}

export interface LoginCredentials {
  email: string
  password: string
}

export interface AuthResponse {
  access_token: string
  token_type: string
  user: User
}