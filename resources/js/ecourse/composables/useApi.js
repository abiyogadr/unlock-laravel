import axios from 'axios'

// Get CSRF token from meta tag
const getCsrfToken = () => {
  return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
}

const apiClient = axios.create({
  baseURL: '/ecourse/api',
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    'X-CSRF-TOKEN': getCsrfToken()
  }
})

// Update CSRF token on response
apiClient.interceptors.response.use(
  response => response,
  error => {
    if (error.response?.status === 419) {
      // Token expired, refresh it
      const newToken = getCsrfToken()
      apiClient.defaults.headers.common['X-CSRF-TOKEN'] = newToken
    }
    return Promise.reject(error)
  }
)

export function useApi() {
  const fetchData = async (url, options = {}) => {
    try {
      const response = await apiClient.get(url, options)
      return response.data.data || response.data
    } catch (error) {
      console.error('API Error:', error)
      throw error
    }
  }

  const postData = async (url, data = {}, options = {}) => {
    try {
      const response = await apiClient.post(url, data, options)
      return response.data.data || response.data
    } catch (error) {
      console.error('API Error:', error)
      throw error
    }
  }

  const putData = async (url, data = {}, options = {}) => {
    try {
      const response = await apiClient.put(url, data, options)
      return response.data.data || response.data
    } catch (error) {
      console.error('API Error:', error)
      throw error
    }
  }

  const deleteData = async (url, options = {}) => {
    try {
      const response = await apiClient.delete(url, options)
      return response.data.data || response.data
    } catch (error) {
      console.error('API Error:', error)
      throw error
    }
  }

  return {
    fetchData,
    postData,
    putData,
    deleteData,
    apiClient
  }
}
