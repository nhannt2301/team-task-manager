import type { RouteRecordRaw } from 'vue-router'

export const taskRoutes: RouteRecordRaw = {
  path: '/tasks',
  component: () => import('@/layouts/AppLayout.vue'),
  meta: { requiresAuth: true },
  children: [
    {
      path: '',
      name: 'tasks.list',
      component: () => import('@/views/Task/TasksView.vue'),
      meta: { title: 'List task' },
    },
    {
      path: ':id',
      name: 'tasks.detail',
      component: () => import('@/views/Task/TaskDetailView.vue'),
      meta: { title: 'Task detail' },
    },
  ],
}