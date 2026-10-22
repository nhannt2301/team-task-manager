<script setup lang="ts">
import { reactive, ref, watch } from 'vue'
import { getApiErrorMessage } from '@/services/api'
import { listUserAssignees } from '@/services/userService'
import { useAuthStore } from '@/stores/auth'
import { TASK_STATUSES } from '@/constants/task'
import { useNotifications } from '@/composables/useNotifications'
import type { Task, TaskInput } from '@/types/task'

const props = defineProps<{
  task: Task
  saving: boolean
}>()

const emit = defineEmits<{
  (e: 'save', form: TaskInput): void
}>()

const authStore = useAuthStore()
const { notifyError } = useNotifications()
const users = ref<{ id: number; name: string }[]>([])
const loadingUsers = ref(false)

async function loadUsers() {
  if (users.value.length > 0 || !authStore.isAdmin) return
  loadingUsers.value = true

  try {
    const result = await listUserAssignees()
    users.value = Array.isArray(result) ? result : (result.data ?? [])
  } catch (error) {
    notifyError(getApiErrorMessage(error))
  } finally {
    loadingUsers.value = false
  }
}

const form = reactive<TaskInput>({
  title: props.task.title,
  description: props.task.description,
  status: props.task.status,
  due_date: props.task.due_date,
  assigned_to: props.task.assigned_to,
})

if (authStore.isAdmin) {
  loadUsers()
}

watch(
  () => props.task,
  (val) => {
    if (val) {
      form.title = val.title
      form.description = val.description
      form.status = val.status
      form.due_date = val.due_date
      form.assigned_to = val.assigned_to
      if (authStore.isAdmin) {
        loadUsers()
      }
    }
  },
  { deep: true }
)

function onSubmit() {
  const { assigned_to, ...restForm } = form

  const payload: TaskInput = {
    ...restForm,
    title: form.title.trim(),
    description: form.description?.trim() || null,
    ...(authStore.isAdmin && { assigned_to: assigned_to ?? null }),
  }

  emit('save', payload)
}
</script>

<template>
  <v-card rounded="lg" elevation="1">
    <v-card-title class="pa-5">Edit task</v-card-title>
    <v-divider />
    <v-card-text class="pa-5">
      <v-form @submit.prevent="onSubmit">
        <v-text-field
          v-model="form.title"
          label="Task name"
          variant="outlined"
          required
        />
        <v-textarea
          v-model="form.description"
          label="Description"
          variant="outlined"
          rows="5"
          auto-grow
        />
        <v-select
          v-if="authStore.isAdmin"
          v-model="form.assigned_to"
          :items="users"
          item-title="name"
          item-value="id"
          label="Assignee"
          variant="outlined"
          :loading="loadingUsers"
          clearable
        />
        <v-row>
          <v-col cols="12" sm="6">
            <v-select
              v-model="form.status"
              :items="TASK_STATUSES"
              label="Status"
              variant="outlined"
            />
          </v-col>
          <v-col cols="12" sm="6">
            <v-text-field
              v-model="form.due_date"
              label="Due date"
              type="date"
              variant="outlined"
              clearable
            />
          </v-col>
        </v-row>
        <div class="d-flex justify-end">
          <v-btn
            color="primary"
            type="submit"
            :loading="saving"
            :disabled="!form.title.trim()"
          >
            Save changes
          </v-btn>
        </div>
      </v-form>
    </v-card-text>
  </v-card>
</template>