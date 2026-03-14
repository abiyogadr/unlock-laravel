import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { createPinia } from 'pinia'
import EcourseLayout from './layouts/EcourseLayout.vue'

createInertiaApp({
  resolve: name => {
    const pages = import.meta.glob('./pages/**/*.vue', { eager: true })
    const page = pages[`./pages/${name}.vue`]?.default
    if (!page) throw new Error(`Page not found: ${name}`)

    // Hanya tetapkan default layout bila properti `layout` TIDAK didefinisikan.
    // Jika `layout` diset ke `null` oleh halaman, itu berarti: jangan pakai layout sama sekali.
    if (typeof page.layout === 'undefined') {
      page.layout = EcourseLayout
    }

    return page
  },
  setup({ el, App, props, plugin }) {
    const app = createApp({ render: () => h(App, props) })
    
    app.use(plugin)
    app.use(createPinia())
    
    app.mount(el)
  },
  progress: {
    color: '#4f46e5',
    showSpinner: true,
  },
})
