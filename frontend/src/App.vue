<script setup lang="ts">
import AppNotifications from '@/components/Common/AppNotifications.vue'
</script>

<template>
  <v-app>
    <router-view />
    <AppNotifications />
  </v-app>
</template>
