import axios from 'axios'

const TOKEN_KEY = 'task_manager_token'
const STATUS_MESSAGES: Record<number, string> = {
  400: 'Invalid request. Please check and try again.',
  401: 'Session expired. Please sign in again.',
  403: 'You do not have permission to perform this action.',
  404: 'The requested resource was not found.',
  408: 'Request timed out. Please try again.',
  409: 'A conflict occurred. Please reload and try again.',
  422: 'Invalid input. Please check the entered data.',
  429: 'Too many requests. Please wait a moment and try again.',
  500: 'Internal server error. Please try again later.',
  502: 'Bad gateway. The server encountered a temporary issue.',
  503: 'Service unavailable. Please try again shortly.',
  504: 'Gateway timeout. The server took too long to respond.',
}

export const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api',
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
})

apiClient.interceptors.request.use((config) => {
  const token = localStorage.getItem(TOKEN_KEY)
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

export function getApiErrorMessage(error: unknown): string {
  if (axios.isAxiosError(error)) {
    if (!error.response) {
      if (error.code === 'ECONNABORTED') {
        return 'The request timed out. Please try again.'
      }
      return 'Unable to connect to the server. Please check your network connection.'
    }

    const status = error.response.status
    return STATUS_MESSAGES[status] ?? `An unexpected error occurred (${status}). Please try again later.`
  }

  return 'An unexpected error occurred. Please try again.'
}