import type { TaskStatus } from '@/types/task'

export interface TaskStatusOption {
  title: string
  value: TaskStatus
  color: string
}

export const TASK_STATUSES: TaskStatusOption[] = [
  { title: 'To do', value: 'todo', color: 'grey' },
  { title: 'In progress', value: 'in_progress', color: 'blue' },
  { title: 'Done', value: 'done', color: 'green' },
]

export function getStatusColor(status: TaskStatus): string {
  return TASK_STATUSES.find((item) => item.value === status)?.color ?? 'grey'
}

export function getStatusLabel(status: TaskStatus): string {
  return TASK_STATUSES.find((item) => item.value === status)?.title ?? status
}