<script setup lang="ts">
import { onBeforeUnmount, ref, watch } from 'vue'
import { TASK_STATUSES } from '@/constants/task'
import type { TaskStatus } from '@/types/task'

export interface TaskFiltersValue {
  status: TaskStatus | null
  search: string
}

const emit = defineEmits<{
  (e: 'change', filters: TaskFiltersValue): void
}>()

const status = ref<TaskStatus | null>(null)
const search = ref('')
let debounceTimer: ReturnType<typeof setTimeout> | undefined

watch(status, () => {
  clearTimeout(debounceTimer)
  emit('change', { status: status.value, search: search.value.trim() })
})

watch(search, () => {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    emit('change', { status: status.value, search: search.value.trim() })
  }, 300)
})

onBeforeUnmount(() => clearTimeout(debounceTimer))
</script>

<template>
  <div class="d-flex flex-wrap ga-4 pa-4">
    <v-select
      v-model="status"
      :items="[{ title: 'All statuses', value: null }, ...TASK_STATUSES]"
      item-title="title"
      item-value="value"
      label="Filter by status"
      variant="outlined"
      density="comfortable"
      hide-details
      clearable
      class="task-filter-status"
    />
    <v-text-field
      v-model="search"
      label="Search by title"
      placeholder="Enter task title"
      prepend-inner-icon="mdi-magnify"
      variant="outlined"
      density="comfortable"
      hide-details
      clearable
      class="task-filter-search"
    />
  </div>
</template>

<style scoped>
.task-filter-status {
  max-width: 240px;
  min-width: 180px;
}

.task-filter-search {
  flex: 1;
  min-width: 220px;
}
</style>
