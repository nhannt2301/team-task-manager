<script setup lang="ts">
export interface BasePagination {
  prev_cursor: string | null
  next_cursor: string | null
}

defineProps<{
  pagination: BasePagination | null
  loading?: boolean
}>()

defineEmits<{
  (e: 'change', cursor: string | null): void
}>()
</script>

<template>
  <template v-if="pagination && (pagination.prev_cursor || pagination.next_cursor)">
    <v-divider />
    <v-card-actions class="justify-center py-3">
      <v-btn
        variant="outlined"
        size="small"
        class="mr-2"
        :disabled="!pagination.prev_cursor || loading"
        @click="$emit('change', pagination.prev_cursor)"
      >
        Prev
      </v-btn>

      <v-btn
        variant="outlined"
        size="small"
        :disabled="!pagination.next_cursor || loading"
        @click="$emit('change', pagination.next_cursor)"
      >
        Next
      </v-btn>
    </v-card-actions>
  </template>
</template>