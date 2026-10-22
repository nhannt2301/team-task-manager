import { apiClient } from './api'

export async function listUserAssignees() {
    const response = await apiClient.get('/users/assignees')
    return response.data.data ?? response.data
}