import type { NavigationGuardNext, RouteLocationNormalized } from 'vue-router'
import { useAuthStore } from './../stores/auth'

export function setupNavigationGuards(
  to: RouteLocationNormalized,
  _from: RouteLocationNormalized,
  next: NavigationGuardNext
) {
  const authStore = useAuthStore()
  const isAuthenticated = authStore.isAuthenticated

  if (to.meta.requiresAuth && !isAuthenticated) {
    return next({
      name: 'login',
      query: { redirect: to.fullPath !== '/tasks' ? to.fullPath : undefined },
    })
  }

  if (to.meta.guestOnly && isAuthenticated) {
    return next({ name: 'tasks.list' })
  }

  if (to.meta.roles && authStore.user) {
    const hasRole = to.meta.roles.includes(authStore.user.role)
    if (!hasRole) {
      return next({ name: 'tasks.list' })
    }
  }

  return next()
}