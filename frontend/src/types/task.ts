export type TaskStatus = 'todo' | 'in_progress' | 'done'

export interface TaskAssignee {
  id: number
  name: string
  email: string
}

export interface Task {
  id: number
  title: string
  description: string | null
  status: TaskStatus
  assigned_to: number | null
  due_date: string | null
  assignee: TaskAssignee
  created_at: string
  updated_at: string
}

export interface TaskInput {
  title: string
  description: string | null
  status: TaskStatus
  due_date: string | null
  assigned_to?: number | null
}

export interface Pagination {
  per_page: number
  prev_cursor: string | null
  next_cursor: string | null
  has_more: boolean
}

export interface ApiResponse<T> {
  status?: string
  message?: string
  data: T
  meta?: {
    pagination?: Pagination
  }
}
