<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { getApiErrorMessage } from '@/services/api'
import { listTasks } from '@/services/taskService'
import TaskTable from '@/components/Task/TaskTable.vue'
import TaskFilters, { type TaskFiltersValue } from '@/components/Task/TaskFilters.vue'
import TaskEmptyState from '@/components/Task/TaskEmptyState.vue'
import TaskCreateDialog from '@/components/Task/Dialog/CreateDialog.vue'
import type { Pagination, Task } from '@/types/task'

const tasks = ref<Task[]>([])
const pagination = ref<Pagination | null>(null)
const loading = ref(false)
const dialog = ref(false)
const errorMessage = ref('')
const filters = ref<TaskFiltersValue>({ status: null, search: '' })
let latestRequestId = 0

async function loadTasks(cursor: string | null = null) {
  const requestId = ++latestRequestId
  loading.value = true
  errorMessage.value = ''

  try {
    const result = await listTasks({ ...filters.value, cursor })
    if (requestId !== latestRequestId) return
    tasks.value = result.tasks
    pagination.value = result.pagination
  } catch (error) {
    if (requestId !== latestRequestId) return
    errorMessage.value = getApiErrorMessage(error)
  } finally {
    if (requestId === latestRequestId) loading.value = false
  }
}

function updateFilters(value: TaskFiltersValue) {
  filters.value = value
  tasks.value = []
  pagination.value = null
  loadTasks()
}

function openCreateDialog() {
  dialog.value = true
}

onMounted(() => loadTasks())
</script>

<template>
  <div>
    <div class="d-flex align-center justify-space-between flex-wrap ga-4 mb-7">
      <div>
        <p class="text-overline text-primary font-weight-bold">WORKSPACE</p>
        <h1 class="text-h4 font-weight-bold">Tasks</h1>
        <p class="text-body-2 text-medium-emphasis mt-1">Track and organize your team's work.</p>
      </div>
      <v-btn color="primary" prepend-icon="mdi-plus" size="large" @click="openCreateDialog">
        New task
      </v-btn>
    </div>

    <v-alert v-if="errorMessage" type="error" variant="tonal" class="mb-5">
      {{ errorMessage }}
      <template #append>
        <v-btn variant="text" @click="loadTasks()">Retry</v-btn>
      </template>
    </v-alert>

    <v-card rounded="lg" elevation="1">
      <v-card-title class="d-flex align-center justify-space-between py-4">
        <span class="text-h6">All tasks</span>
        <span v-if="pagination" class="text-body-2 text-medium-emphasis">
          {{ pagination.per_page }} per page
        </span>
      </v-card-title>
      <v-divider />

      <TaskFilters @change="updateFilters" />
      <v-divider />

      <v-progress-linear v-if="loading" indeterminate color="primary" />
      <TaskTable
        v-if="tasks.length"
        :tasks="tasks"
        :pagination="pagination"
        :loading="loading"
        @cursor-change="loadTasks"
      />
      <TaskEmptyState v-else-if="!loading && !errorMessage" @create="openCreateDialog" />
    </v-card>

    <TaskCreateDialog v-model="dialog" @created="loadTasks()" />
  </div>
</template>