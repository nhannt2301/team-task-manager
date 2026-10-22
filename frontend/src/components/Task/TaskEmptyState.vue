<script setup lang="ts">
defineEmits<{
  (e: 'create'): void
}>()
</script>

<template>
  <v-card-text class="text-center py-12">
    <v-avatar color="primary" variant="tonal" size="64" class="mb-4">
      <v-icon icon="mdi-checkbox-marked-circle-outline" size="32" />
    </v-avatar>
    <h2 class="text-h6 mb-2">No tasks yet</h2>
    <p class="text-body-2 text-medium-emphasis mb-5">
      Create a task to get your team's work moving.
    </p>
    <v-btn color="primary" prepend-icon="mdi-plus" @click="$emit('create')">
      Create a task
    </v-btn>
  </v-card-text>
</template>