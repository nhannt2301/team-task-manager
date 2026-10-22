import type { ApiResponse, Pagination, Task, TaskInput } from '@/types/task'
import { apiClient } from './api'

export interface TaskListFilters {
  cursor?: string | null
  status?: Task['status'] | null
  search?: string
}

export async function listTasks(
  filters: TaskListFilters = {},
): Promise<{ tasks: Task[]; pagination: Pagination }> {
  const params = {
    ...(filters.cursor ? { cursor: filters.cursor } : {}),
    ...(filters.status ? { status: filters.status } : {}),
    ...(filters.search ? { search: filters.search } : {}),
  }
  const response = await apiClient.get<ApiResponse<Task[]>>('/tasks', {
    params,
  })

  if (!response.data.meta?.pagination) {
    throw new Error('The task list response is missing pagination metadata.')
  }

  return {
    tasks: response.data.data ?? [],
    pagination: response.data.meta.pagination,
  }
}

export async function getTask(id: number): Promise<Task> {
  const response = await apiClient.get<ApiResponse<Task>>(`/tasks/${id}`)
  return response.data.data
}

export async function createTask(task: TaskInput): Promise<Task> {
  const response = await apiClient.post<ApiResponse<Task>>('/tasks', task)
  return response.data.data
}

export async function updateTask(id: number, task: TaskInput): Promise<Task> {
  const response = await apiClient.patch<ApiResponse<Task>>(`/tasks/${id}`, task)
  return response.data.data
}

export async function deleteTask(id: number): Promise<void> {
  await apiClient.delete(`/tasks/${id}`)
}
