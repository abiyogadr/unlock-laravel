import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { useApi } from '../composables/useApi'

export const useEcourseStore = defineStore('ecourse', () => {
  const { fetchData, postData } = useApi()

  const user = ref(null)
  const currentCourse = ref(null)
  const currentModule = ref(null)
  const userCourses = ref([])
  const isLoading = ref(false)

  const isAuthenticated = computed(() => !!user.value)

  const loadUser = async () => {
    try {
      const userData = await fetchData('/api/user')
      user.value = userData
    } catch (error) {
      console.error('Failed to load user:', error)
    }
  }

  const loadUserCourses = async () => {
    isLoading.value = true
    try {
      const courses = await fetchData('/ecourse/api/user-courses')
      userCourses.value = courses
    } catch (error) {
      console.error('Failed to load user courses:', error)
    } finally {
      isLoading.value = false
    }
  }

  const completeModule = async (moduleId) => {
    try {
      await postData(`/ecourse/module/${moduleId}/complete`)
      return true
    } catch (error) {
      console.error('Failed to complete module:', error)
      return false
    }
  }

  const updateProgress = async (moduleId, progress) => {
    try {
      await postData(`/ecourse/module/${moduleId}/progress`, { progress })
      return true
    } catch (error) {
      console.error('Failed to update progress:', error)
      return false
    }
  }

  return {
    user,
    currentCourse,
    currentModule,
    userCourses,
    isLoading,
    isAuthenticated,
    loadUser,
    loadUserCourses,
    completeModule,
    updateProgress
  }
})
