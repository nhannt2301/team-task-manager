<script setup lang="ts">
import { RouterLink } from 'vue-router'
import AppPagination from '@/components/Common/AppPagination.vue'
import { formatDate } from '@/utils/formatters'
import { getStatusColor, getStatusLabel } from '@/constants/task'
import type { Pagination, Task } from '@/types/task'

defineProps<{
  tasks: Task[]
  pagination?: Pagination | null
  loading?: boolean
}>()

defineEmits<{
  (e: 'cursor-change', cursor: string | null): void
}>()
</script>

<template>
  <div>
    <v-table hover>
      <thead>
        <tr>
          <th class="text-left">Task</th>
          <th class="text-left">Status</th>
          <th class="text-left">Assignee</th>
          <th class="text-left">Due date</th>
          <th aria-label="Actions" />
        </tr>
      </thead>
      <tbody>
        <tr v-for="task in tasks" :key="task.id">
          <td>
            <RouterLink :to="{ name: 'tasks.detail', params: { id: task.id } }">
              <span class="font-weight-medium text-primary">{{ task.title }}</span>
              <span
                v-if="task.description"
                class="d-block text-caption text-medium-emphasis text-truncate"
                style="max-width: 320px"
              >
                {{ task.description }}
              </span>
            </RouterLink>
          </td>
          <td>
            <v-chip :color="getStatusColor(task.status)" size="small" variant="tonal">
              {{ getStatusLabel(task.status) }}
            </v-chip>
          </td>
          <td>{{ task.assignee?.name ?? '—' }}</td>
          <td>{{ formatDate(task.due_date) }}</td>
          <td class="text-right">
            <v-btn
              :to="{ name: 'tasks.detail', params: { id: task.id } }"
              icon="mdi-chevron-right"
              variant="text"
              size="small"
              :aria-label="`View ${task.title}`"
            />
          </td>
        </tr>
      </tbody>
    </v-table>

    <AppPagination
      :pagination="pagination ?? null"
      :loading="loading"
      @change="$emit('cursor-change', $event)"
    />
  </div>
</template>