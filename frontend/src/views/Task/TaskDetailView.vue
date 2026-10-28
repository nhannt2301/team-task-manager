<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { getApiErrorMessage } from '@/services/api'
import { deleteTask, getTask, updateTask } from '@/services/taskService'
import { getStatusColor, getStatusLabel } from '@/constants/task'
import TaskEditForm from '@/components/Task/TaskEditForm.vue'
import TaskInfoCard from '@/components/Task/TaskInfoCard.vue'
import TaskDeleteDialog from '@/components/Task/Dialog/DeleteDialog.vue'
import { useNotifications } from '@/composables/useNotifications'
import type { Task, TaskInput } from '@/types/task'

const route = useRoute()
const router = useRouter()
const { notifySuccess, notifyError } = useNotifications()
const task = ref<Task | null>(null)
const loading = ref(true)
const saving = ref(false)
const deleting = ref(false)
const errorMessage = ref('')
const deleteDialog = ref(false)

const taskId = computed(() => Number(route.params.id))

async function loadTask() {
  loading.value = true
  errorMessage.value = ''

  if (!Number.isInteger(taskId.value) || taskId.value < 1) {
    errorMessage.value = 'Invalid task ID.'
    loading.value = false
    return
  }

  try {
    task.value = await getTask(taskId.value)
  } catch (error) {
    errorMessage.value = getApiErrorMessage(error)
  } finally {
    loading.value = false
  }
}

async function saveTask(form: TaskInput) {
  if (!task.value) return
  saving.value = true

  try {
    task.value = await updateTask(task.value.id, form)
    notifySuccess('Task updated successfully.')
  } catch (error) {
    notifyError(getApiErrorMessage(error))
  } finally {
    saving.value = false
  }
}

async function confirmDelete() {
  if (!task.value) return
  deleting.value = true

  try {
    await deleteTask(task.value.id)
    notifySuccess('Task deleted successfully.')
    await router.push({ name: 'tasks.list' })
  } catch (error) {
    notifyError(getApiErrorMessage(error))
    deleteDialog.value = false
  } finally {
    deleting.value = false
  }
}

onMounted(loadTask)
watch(() => route.params.id, loadTask)
</script>

<template>
  <div>
    <div class="mb-6">
      <v-btn
        :to="{ name: 'tasks.list' }"
        prepend-icon="mdi-arrow-left"
        variant="text"
        class="mb-3"
      >
        All tasks
      </v-btn>
      <v-progress-linear
        v-if="loading"
        indeterminate
        color="primary"
      />
      <v-alert
        v-else-if="errorMessage"
        type="error"
        variant="tonal"
      >
        {{ errorMessage }}
        <template #append>
          <v-btn
            variant="text"
            @click="loadTask"
          >
            Retry
          </v-btn>
        </template>
      </v-alert>
    </div>

    <template v-if="task && !loading">
      <div class="d-flex align-start justify-space-between flex-wrap ga-4 mb-6">
        <div>
          <p class="text-overline text-primary font-weight-bold">
            TASK DETAILS
          </p>
          <h1 class="text-h4 font-weight-bold">
            {{ task.title }}
          </h1>
          <v-chip
            :color="getStatusColor(task.status)"
            size="small"
            variant="tonal"
            class="mt-3"
          >
            {{ getStatusLabel(task.status) }}
          </v-chip>
        </div>
        <v-btn
          color="error"
          prepend-icon="mdi-delete-outline"
          variant="tonal"
          @click="deleteDialog = true"
        >
          Delete
        </v-btn>
      </div>

      <v-row>
        <v-col
          cols="12"
          md="8"
        >
          <TaskEditForm
            :task="task"
            :saving="saving"
            @save="saveTask"
          />
        </v-col>

        <v-col
          cols="12"
          md="4"
        >
          <TaskInfoCard :task="task" />
        </v-col>
      </v-row>
    </template>

    <TaskDeleteDialog
      v-model="deleteDialog"
      :loading="deleting"
      @confirm="confirmDelete"
    />
  </div>
</template>