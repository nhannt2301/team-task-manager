import { createRouter, createWebHistory } from 'vue-router'
import { authRoutes } from './modules/auth.routes'
import { taskRoutes } from './modules/task.routes'
import { setupNavigationGuards } from './guards'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      redirect: { name: 'tasks.list' },
    },
    ...authRoutes,
    taskRoutes,
    {
      path: '/:pathMatch(.*)*',
      redirect: { name: 'tasks.list' },
    },
  ],
})

router.beforeEach(setupNavigationGuards)

router.afterEach((to) => {
  if (to.meta.title) {
    document.title = `${to.meta.title} - Team Task Manager`
  }
})

export default router