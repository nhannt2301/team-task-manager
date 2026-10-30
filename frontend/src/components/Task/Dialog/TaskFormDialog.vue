<script setup lang="ts">
import { computed, reactive, ref, watch } from 'vue'
import { getApiErrorMessage } from '@/services/api'
import { createTask, updateTask } from '@/services/taskService'
import { listUserAssignees } from '@/services/userService'
import { useAuthStore } from '@/stores/auth'
import { TASK_STATUSES } from '@/constants/task'
import { useNotifications } from '@/composables/useNotifications'
import type { Task, TaskInput } from '@/types/task'

interface UserOption {
  id: number
  name: string
  role?: string
}

interface FormState extends TaskInput {
  assigned_to: number | null
}

const props = withDefaults(
  defineProps<{
    modelValue: boolean
    task?: Task | null
  }>(),
  {
    task: null,
  }
)

const emit = defineEmits<{
  (e: 'update:modelValue', value: boolean): void
  (e: 'saved'): void
}>()

const authStore = useAuthStore()
const { notifySuccess, notifyError } = useNotifications()

const saving = ref(false)
const loadingUsers = ref(false)
const users = ref<UserOption[]>([])
const formError = ref('')
const titleInputRef = ref<HTMLInputElement | null>(null)

const isEdit = computed(() => Boolean(props.task?.id))
const dialogTitle = computed(() => (isEdit.value ? 'Edit task' : 'Create task'))
const submitButtonText = computed(() => (isEdit.value ? 'Save changes' : 'Create task'))

const emptyTask = (): FormState => ({
  title: '',
  description: null,
  status: 'todo',
  due_date: null,
  assigned_to: null,
})

const form = reactive<FormState>(emptyTask())

const isOpen = computed({
  get: () => props.modelValue,
  set: (val: boolean) => emit('update:modelValue', val),
})

async function loadUsers() {
  if (users.value.length > 0) return
  loadingUsers.value = true
  try {
    const result = await listUserAssignees()
    users.value = Array.isArray(result) ? result : (result.data ?? [])
  } catch (error) {
    formError.value = getApiErrorMessage(error)
  } finally {
    loadingUsers.value = false
  }
}

watch(
  () => props.modelValue,
  (val) => {
    if (!val) return

    formError.value = ''
    if (authStore.isAdmin) {
      loadUsers()
    }

    if (props.task) {
      // Map data edit
      Object.assign(form, {
        title: props.task.title || '',
        description: props.task.description || null,
        status: props.task.status || 'todo',
        due_date: props.task.due_date ? props.task.due_date.slice(0, 10) : null,
        assigned_to: props.task.assigned_to ?? null,
      })
    } else {
      // Reset data
      Object.assign(form, emptyTask())
    }
  }
)

function focusTitleInput() {
  titleInputRef.value?.focus()
}

function closeDialog() {
  emit('update:modelValue', false)
}

async function saveTask() {
  formError.value = ''
  saving.value = true

  const payload: FormState = {
    ...form,
    title: form.title.trim(),
    description: form.description?.trim() || null,
    assigned_to: authStore.isAdmin ? form.assigned_to : null,
  }

  try {
    if (isEdit.value && props.task?.id) {
      await updateTask(props.task.id, payload)
      notifySuccess('Task updated successfully.')
    } else {
      await createTask(payload)
      notifySuccess('Task created successfully.')
    }

    closeDialog()
    emit('saved')
  } catch (error) {
    notifyError(getApiErrorMessage(error))
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <v-dialog
    v-model="isOpen"
    max-width="560"
    @after-enter="focusTitleInput"
  >
    <v-card rounded="lg">
      <v-card-title class="pa-5">
        {{ dialogTitle }}
      </v-card-title>
      <v-divider />

      <v-card-text class="pa-5">
        <v-alert
          v-if="formError"
          type="error"
          variant="tonal"
          class="mb-4"
        >
          {{ formError }}
        </v-alert>

        <v-form @submit.prevent="saveTask">
          <v-text-field
            ref="titleInputRef"
            v-model="form.title"
            label="Task name"
            variant="outlined"
            required
          />

          <v-textarea
            v-model="form.description"
            label="Description"
            variant="outlined"
            rows="3"
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

          <v-select
            v-model="form.status"
            :items="TASK_STATUSES"
            label="Status"
            variant="outlined"
          />

          <v-text-field
            v-model="form.due_date"
            label="Due date"
            type="date"
            variant="outlined"
            clearable
          />
        </v-form>
      </v-card-text>

      <v-card-actions class="px-5 pb-5">
        <v-spacer />
        <v-btn
          variant="text"
          @click="closeDialog"
        >
          Cancel
        </v-btn>
        <v-btn
          color="primary"
          :loading="saving"
          :disabled="!form.title.trim()"
          @click="saveTask"
        >
          {{ submitButtonText }}
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>