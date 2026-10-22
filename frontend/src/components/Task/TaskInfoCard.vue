<script setup lang="ts">
import { formatDate } from '../../utils/formatters'
import type { Task } from '../../types/task'

defineProps<{
  task: Task
}>()
</script>

<template>
  <v-card rounded="lg" elevation="1">
    <v-card-title class="pa-5">Task information</v-card-title>
    <v-divider />
    <v-list lines="two">
      <v-list-item
        prepend-icon="mdi-account-outline"
        title="Assignee"
        :subtitle="task.assignee?.name ?? '—'"
      />
      <v-list-item
        prepend-icon="mdi-calendar-clock"
        title="Due date"
        :subtitle="formatDate(task.due_date)"
      />
      <v-list-item
        prepend-icon="mdi-calendar-plus"
        title="Created"
        :subtitle="new Date(task.created_at).toLocaleDateString()"
      />
    </v-list>
  </v-card>
</template>