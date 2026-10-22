import { readonly, ref } from 'vue'

export type NotificationType = 'success' | 'error'

export interface AppNotification {
  id: number
  type: NotificationType
  message: string
}

const notifications = ref<AppNotification[]>([])
let nextNotificationId = 0

function notify(type: NotificationType, message: string) {
  notifications.value.push({
    id: ++nextNotificationId,
    type,
    message,
  })
}

export function useNotifications() {
  return {
    notifications: readonly(notifications),
    notifySuccess: (message: string) => notify('success', message),
    notifyError: (message: string) => notify('error', message),
    dismissNotification: (id: number) => {
      notifications.value = notifications.value.filter((notification) => notification.id !== id)
    },
  }
}
