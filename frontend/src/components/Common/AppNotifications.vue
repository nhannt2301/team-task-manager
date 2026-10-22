<script setup lang="ts">
import { computed } from 'vue'
import { useNotifications } from '@/composables/useNotifications'

const { notifications, dismissNotification } = useNotifications()

const currentNotification = computed(() => notifications.value[0])

function updateVisibility(visible: boolean) {
  if (!visible && currentNotification.value) {
    dismissNotification(currentNotification.value.id)
  }
}
</script>

<template>
  <v-snackbar
    :key="currentNotification?.id"
    :model-value="Boolean(currentNotification)"
    :color="currentNotification?.type"
    :timeout="5000"
    location="top end"
    @update:model-value="updateVisibility"
  >
    <div class="d-flex align-center ga-2">
      <v-icon
        :icon="currentNotification?.type === 'success' ? 'mdi-check-circle' : 'mdi-alert-circle'"
      />
      <span>{{ currentNotification?.message }}</span>
    </div>

    <template #actions>
      <v-btn
        icon="mdi-close"
        variant="text"
        aria-label="Dismiss notification"
        @click="currentNotification && dismissNotification(currentNotification.id)"
      />
    </template>
  </v-snackbar>
</template>
