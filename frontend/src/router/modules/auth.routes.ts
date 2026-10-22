import type { RouteRecordRaw } from 'vue-router'

export const authRoutes: RouteRecordRaw[] = [
  {
    path: '/login',
    component: () => import('@/layouts/BlankLayout.vue'),
    children: [
      {
        path: '',
        name: 'login',
        component: () => import('@/views/Auth/LoginView.vue'),
        meta: { guestOnly: true, title: 'Login' },
      },
    ],
  },
]