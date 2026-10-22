import { computed, ref } from 'vue'
import { defineStore } from 'pinia'
import { login as apiLogin, logout as apiLogout, getMe as apiGetMe } from '@/services/authService'
import { apiClient } from '@/services/api'
import type { LoginCredentials, User } from '@/types/auth'

const TOKEN_KEY = 'task_manager_token'
const USER_KEY = 'task_manager_user'

export const useAuthStore = defineStore('auth', () => {
  const token = ref<string | null>(localStorage.getItem(TOKEN_KEY))
  const user = ref<User | null>(null)
  const isLoading = ref(false)

  const storedUser = localStorage.getItem(USER_KEY)
  if (storedUser) {
    try {
      user.value = JSON.parse(storedUser) as User
    } catch {
      localStorage.removeItem(USER_KEY)
    }
  }

  const isAuthenticated = computed(() => Boolean(token.value))
  const isAdmin = computed(() => user.value?.role === 'admin')
  const currentRole = computed(() => user.value?.role ?? 'guest')

  async function initializeAuth(): Promise<void> {
    if (!token.value) {
      clearAuth()
      return
    }

    apiClient.defaults.headers.common.Authorization = `Bearer ${token.value}`

    try {
      user.value = await apiGetMe()
      localStorage.setItem(USER_KEY, JSON.stringify(user.value))
    } catch {
      clearAuth()
    }
  }

  async function signIn(credentials: LoginCredentials): Promise<void> {
    isLoading.value = true
    try {
      const result = await apiLogin(credentials)
      token.value = result.access_token
      user.value = result.user

      localStorage.setItem(TOKEN_KEY, result.access_token)
      localStorage.setItem(USER_KEY, JSON.stringify(result.user))
      apiClient.defaults.headers.common.Authorization = `Bearer ${result.access_token}`
    } finally {
      isLoading.value = false
    }
  }

  async function signOut(): Promise<void> {
    isLoading.value = true
    try {
      if (token.value) {
        await apiLogout()
      }
    } catch {
      // End the local session even if the server cannot be reached.
    } finally {
      clearAuth()
      isLoading.value = false
    }
  }

  function clearAuth(): void {
    token.value = null
    user.value = null
    delete apiClient.defaults.headers.common.Authorization
    localStorage.removeItem(TOKEN_KEY)
    localStorage.removeItem(USER_KEY)
  }

  return {
    token,
    user,
    isLoading,
    isAuthenticated,
    isAdmin,
    currentRole,
    initializeAuth,
    signIn,
    signOut,
    clearAuth,
  }
})
