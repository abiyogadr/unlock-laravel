import { ref } from 'vue'

export const notifications = ref([])

/**
 * Tampilkan notifikasi success
 */
export function notifySuccess(message, duration = 3000) {
  const id = Date.now()
  notifications.value.push({
    id,
    type: 'success',
    message,
    icon: 'fa-check-circle'
  })
  
  if (duration) {
    setTimeout(() => removeNotification(id), duration)
  }
}

/**
 * Tampilkan notifikasi error
 */
export function notifyError(message, duration = 5000) {
  const id = Date.now()
  notifications.value.push({
    id,
    type: 'error',
    message,
    icon: 'fa-exclamation-circle'
  })
  
  if (duration) {
    setTimeout(() => removeNotification(id), duration)
  }
}

/**
 * Tampilkan notifikasi warning
 */
export function notifyWarning(message, duration = 4000) {
  const id = Date.now()
  notifications.value.push({
    id,
    type: 'warning',
    message,
    icon: 'fa-exclamation-triangle'
  })
  
  if (duration) {
    setTimeout(() => removeNotification(id), duration)
  }
}

/**
 * Tampilkan notifikasi info
 */
export function notifyInfo(message, duration = 3000) {
  const id = Date.now()
  notifications.value.push({
    id,
    type: 'info',
    message,
    icon: 'fa-info-circle'
  })
  
  if (duration) {
    setTimeout(() => removeNotification(id), duration)
  }
}

/**
 * Hapus notifikasi
 */
export function removeNotification(id) {
  const index = notifications.value.findIndex(n => n.id === id)
  if (index > -1) {
    notifications.value.splice(index, 1)
  }
}

/**
 * Clear all notifications
 */
export function clearNotifications() {
  notifications.value = []
}

/**
 * Handle API error dan tampilkan notifikasi
 */
export function handleApiError(error) {
  if (error.response?.status === 401) {
    notifyError('Anda tidak terautentikasi. Silakan login kembali.')
    window.location.href = '/login'
  } else if (error.response?.status === 403) {
    notifyError('Anda tidak memiliki akses ke resource ini.')
  } else if (error.response?.status === 404) {
    notifyError('Resource tidak ditemukan.')
  } else if (error.response?.status === 422) {
    const errors = error.response.data?.errors || {}
    const errorMessages = Object.values(errors).flat().join(', ')
    notifyError(errorMessages || 'Data tidak valid.')
  } else if (error.response?.status === 500) {
    notifyError('Terjadi kesalahan server. Silakan coba lagi.')
  } else if (error.code === 'ERR_NETWORK') {
    notifyError('Tidak dapat terhubung ke server.')
  } else {
    notifyError(error.response?.data?.message || 'Terjadi kesalahan.')
  }
}
