<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { getApiErrorMessage } from '../services/api'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const drawer = ref(false)
const logoutError = ref('')
const showLogoutError = ref(false)

async function handleLogout() {
  try {
    await authStore.signOut()
    await router.push({ name: 'login' })
  } catch (error) {
    logoutError.value = `Error: ${getApiErrorMessage(error)}`
    showLogoutError.value = true
    await router.push({ name: 'login' })
  }
}
</script>

<template>
  <v-layout>
    <!-- Navigation Drawer -->
    <v-navigation-drawer v-model="drawer">
      <v-list-item
        title="Task Manager"
        subtitle="Task manager"
        class="py-4"
      >
        <template #prepend>
          <v-icon color="primary" size="large">mdi-checkbox-marked-circle-outline</v-icon>
        </template>
      </v-list-item>

      <v-divider />

      <v-list density="compact" nav class="mt-2">
        <v-list-item
          prepend-icon="mdi-format-list-checks"
          title="List task"
          value="tasks"
          :to="{ name: 'tasks.list' }"
        />
      </v-list>
    </v-navigation-drawer>

    <!-- App Bar -->
    <v-app-bar elevation="1">
      <v-app-bar-nav-icon @click="drawer = !drawer" />

      <v-app-bar-title class="font-weight-bold">
        Team Task Manager
      </v-app-bar-title>

      <v-spacer />

      <!-- User Profile & Action -->
      <div v-if="authStore.user" class="d-flex align-center ga-3 mr-4">
        <div class="text-right d-none d-sm-block">
          <div class="text-subtitle-2 font-weight-bold leading-tight">
            {{ authStore.user.name }}
          </div>
          <div class="text-caption text-medium-emphasis">
            {{ authStore.user.email }}
          </div>
        </div>

        <v-chip
          :color="authStore.isAdmin ? 'error' : 'primary'"
          size="small"
          class="text-uppercase font-weight-bold"
        >
          {{ authStore.user.role }}
        </v-chip>

        <v-btn
          icon="mdi-logout"
          variant="text"
          color="medium-emphasis"
          title="Logout"
          :loading="authStore.isLoading"
          @click="handleLogout"
        />
      </div>
    </v-app-bar>

    <!-- Main View -->
    <v-main>
      <v-container fluid class="pa-6">
        <router-view />
      </v-container>
    </v-main>

    <!-- Error Snackbar -->
    <v-snackbar v-model="showLogoutError" color="error" timeout="6000">
      {{ logoutError }}
    </v-snackbar>
  </v-layout>
</template>

<style scoped>
.leading-tight {
  line-height: 1.2;
}
</style>