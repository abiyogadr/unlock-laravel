import { createApp } from 'vue'
import axios from 'axios'
import BootcampCoretax from './BootcampCoretax.vue'

// Setup axios globally untuk standalone mount
window.axios = axios
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

createApp(BootcampCoretax).mount('#app-coretax')
