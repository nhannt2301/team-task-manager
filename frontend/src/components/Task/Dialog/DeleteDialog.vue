<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
  modelValue: boolean
  loading?: boolean
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: boolean): void
  (e: 'confirm'): void
}>()

const isOpen = computed({
  get: () => props.modelValue,
  set: (val: boolean) => emit('update:modelValue', val),
})
</script>

<template>
  <v-dialog
    v-model="isOpen"
    max-width="420"
  >
    <v-card rounded="lg">
      <v-card-title class="pa-5">
        Delete this task?
      </v-card-title>
      <v-card-text>
        This action cannot be undone. The task will be permanently removed.
      </v-card-text>
      <v-card-actions class="px-5 pb-5">
        <v-spacer />
        <v-btn
          variant="text"
          @click="isOpen = false"
        >
          Cancel
        </v-btn>
        <v-btn
          color="error"
          :loading="loading"
          @click="$emit('confirm')"
        >
          Delete task
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>