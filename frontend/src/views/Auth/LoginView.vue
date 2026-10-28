<script setup lang="ts">
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { getApiErrorMessage } from '@/services/api'
import { useAuthStore } from '@/stores/auth'

const email = ref('')
const password = ref('')
const errorMessage = ref('')
const loading = ref(false)
const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

async function submit() {
  errorMessage.value = ''
  loading.value = true

  try {
    await authStore.signIn({ email: email.value, password: password.value })
    const redirect = typeof route.query.redirect === 'string' ? route.query.redirect : '/tasks'
    await router.push(redirect)
  } catch (error) {
    errorMessage.value = getApiErrorMessage(error)
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <v-row
    align="center"
    justify="center"
    class="w-100"
  >
    <v-col
      cols="12"
      sm="9"
      md="6"
      lg="4"
    >
      <v-card
        class="pa-6 pa-sm-8"
        rounded="xl"
        elevation="2"
      >
        <div class="text-center mb-7">
          <v-avatar
            color="primary"
            size="56"
            class="mb-4"
          >
            <v-icon
              icon="mdi-clipboard-check-outline"
              size="30"
            />
          </v-avatar>
          <h1 class="text-h4 font-weight-bold mb-2">
            Welcome back
          </h1>
          <p class="text-body-2 text-medium-emphasis">
            Sign in to manage your team tasks.
          </p>
        </div>

        <v-alert
          v-if="errorMessage"
          type="error"
          variant="tonal"
          class="mb-5"
        >
          {{ errorMessage }}
        </v-alert>

        <v-form @submit.prevent="submit">
          <v-text-field
            v-model="email"
            label="Email"
            type="email"
            autocomplete="username"
            prepend-inner-icon="mdi-email-outline"
            variant="outlined"
            required
            class="mb-2"
          />
          <v-text-field
            v-model="password"
            label="Password"
            type="password"
            autocomplete="current-password"
            prepend-inner-icon="mdi-lock-outline"
            variant="outlined"
            required
            class="mb-3"
          />
          <v-btn
            type="submit"
            color="primary"
            block
            size="large"
            :loading="loading"
            :disabled="!email || !password"
          >
            Sign in
          </v-btn>
        </v-form>
      </v-card>
      <p class="text-center text-caption text-medium-emphasis mt-5">
        Internal team workspace
      </p>
    </v-col>
  </v-row>
</template>
