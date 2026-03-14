<template>
  <div class="flex min-h-screen bg-gray-50">
    <!-- Sidebar -->
    <aside 
      v-if="!page.props.embed"
      :class="[
        'fixed inset-y-0 left-0 z-100 w-64 bg-gradient-to-br from-primary to-purple-800',
        'transform transition-transform duration-300 shadow-2xl',
        'xl:translate-x-0',
        isSidebarOpen ? 'translate-x-0' : '-translate-x-full'
      ]"
    >
      <div class="flex flex-col h-full">
        <!-- Logo -->
        <div class="p-6 border-b border-white/10 flex items-center justify-between">
          <template v-if="page.props.auth && page.props.auth.user">
            <Link href="/ecourse/dashboard" class="flex items-center text-white font-bold text-xl hover:opacity-80 transition">
              <i class="fas fa-graduation-cap mr-3"></i>
              <span>Unlock E-Course</span>
            </Link>
          </template>
          <template v-else>
            <a href="/login?redirect=/ecourse/dashboard" class="flex items-center text-white font-bold text-xl hover:opacity-80 transition" @click.prevent="goExternal('/login?redirect=/ecourse/dashboard')">
              <i class="fas fa-graduation-cap mr-3"></i>
              <span>Unlock E-Course</span>
            </a>
          </template>
          <!-- <button @click="isSidebarOpen = false" class="xl:hidden text-white/50 hover:text-white">
            <i class="fas fa-times"></i>
          </button> -->
        </div>

        <!-- Navigation -->
        <nav class="flex-1 p-4 overflow-y-auto">
          <ul class="space-y-1.5">
            <li>
              <template v-if="page.props.auth && page.props.auth.user">
                <Link 
                  href="/ecourse/dashboard" 
                  :class="[
                    'flex items-center p-3 rounded-xl transition',
                    'text-white/80 hover:text-white hover:bg-white/10',
                    isActive('/ecourse/dashboard') ? 'bg-white/20 text-white' : ''
                  ]"
                  @click="isSidebarOpen = false"
                >
                  <i class="fas fa-home w-5 mr-3"></i>
                  <span class="text-sm font-medium">Dashboard</span>
                </Link>
              </template>
              <template v-else>
                <a
                  href="/login?redirect=/ecourse/dashboard"
                  class="flex items-center p-3 rounded-xl transition text-white/80 hover:text-white hover:bg-white/10"
                  @click.prevent="isSidebarOpen = false; goExternal('/login?redirect=/ecourse/dashboard')"
                >
                  <i class="fas fa-home w-5 mr-3"></i>
                  <span class="text-sm font-medium">Dashboard</span>
                </a>
              </template>
            </li>
            <li>
              <Link 
                href="/ecourse/catalog" 
                :class="[
                  'flex items-center p-3 rounded-xl transition',
                  'text-white/80 hover:text-white hover:bg-white/10',
                  isActive('/ecourse/catalog') ? 'bg-white/20 text-white' : ''
                ]"
                @click="isSidebarOpen = false"
              >
                <i class="fas fa-book w-5 mr-3"></i>
                <span class="text-sm font-medium">Catalog</span>
              </Link>
            </li>
            <li>
              <template v-if="page.props.auth && page.props.auth.user">
                <Link 
                  href="/ecourse/my-journey" 
                  :class="[
                    'flex items-center p-3 rounded-xl transition',
                    'text-white/80 hover:text-white hover:bg-white/10',
                    isActive('/ecourse/my-journey') ? 'bg-white/20 text-white' : ''
                  ]"
                  @click="isSidebarOpen = false"
                >
                  <i class="fas fa-graduation-cap w-5 mr-3"></i>
                  <span class="text-sm font-medium">My Journey</span>
                </Link>
              </template>
              <template v-else>
                <a
                  href="/login?redirect=/ecourse/my-journey"
                  class="flex items-center p-3 rounded-xl transition text-white/80 hover:text-white hover:bg-white/10"
                  @click.prevent="isSidebarOpen = false; goExternal('/login?redirect=/ecourse/my-journey')"
                >
                  <i class="fas fa-graduation-cap w-5 mr-3"></i>
                  <span class="text-sm font-medium">My Journey</span>
                </a>
              </template>
            </li>
            <li>
              <template v-if="page.props.auth && page.props.auth.user">
                <Link 
                  href="/ecourse/certificates" 
                  :class="[
                    'flex items-center p-3 rounded-xl transition',
                    'text-white/80 hover:text-white hover:bg-white/10',
                    isActive('/ecourse/certificates') ? 'bg-white/20 text-white' : ''
                  ]"
                  @click="isSidebarOpen = false"
                >
                  <i class="fas fa-award w-5 mr-3"></i>
                  <span class="text-sm font-medium">Sertifikat</span>
                </Link>
              </template>
              <template v-else>
                <a
                  href="/login?redirect=/ecourse/certificates"
                  class="flex items-center p-3 rounded-xl transition text-white/80 hover:text-white hover:bg-white/10"
                  @click.prevent="isSidebarOpen = false; goExternal('/login?redirect=/ecourse/certificates')"
                >
                  <i class="fas fa-award w-5 mr-3"></i>
                  <span class="text-sm font-medium">Sertifikat</span>
                </a>
              </template>
            </li>
          </ul>
        </nav>

        <!-- User Info -->
        <div class="p-4 border-t border-white/10 bg-black/10 space-y-3">
          <div v-if="page.props.auth && page.props.auth.user" class="flex items-center">
            <img :src="page.props.auth.user.avatar || '/favicon.png'" :alt="page.props.auth.user.name || 'User'" class="w-10 h-10 rounded-full border-2 border-white/30 object-cover">
            <div class="ml-3 text-white overflow-hidden flex-1">
              <p class="font-semibold text-sm truncate">{{ $page.props.auth.user.name }}</p>
              <p class="text-xs text-white/60">{{ $page.props.auth.user.email }}</p>
            </div>
          </div>
          <div v-else class="flex items-center">
            <a href="/login" class="inline-flex items-center px-3 py-2 bg-white/10 text-white rounded-md font-semibold" @click.prevent="goExternal('/login?redirect=' + page.url)">Masuk</a>
          </div>

          <button v-if="page.props.auth && page.props.auth.user" @click="handleLogout" class="w-full flex items-center justify-center p-2 text-xs font-medium text-white/80 hover:text-white hover:bg-white/10 rounded-lg transition cursor-pointer">
            <i class="fas fa-arrow-right-from-bracket mr-2"></i>Keluar
          </button>
        </div>
      </div>
    </aside>

    <!-- Overlay untuk mobile sidebar -->
    <div 
      v-if="isSidebarOpen" 
      @click="isSidebarOpen = false"
      class="fixed inset-0 bg-black/50 z-100 xl:hidden"
    ></div>

    <!-- Main Content -->
    <main :class="{ 'xl:ml-64': !page.props.embed, 'flex-1 flex flex-col min-w-0 relative [overflow-x:clip]': true }">
      <!-- Header -->
      <header v-if="!page.props.embed" class="sticky top-0 z-100 bg-white/80 backdrop-blur-md border-b border-gray-100 px-4 sm:px-6 py-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center">
            <button 
              @click="isSidebarOpen = !isSidebarOpen"
              class="xl:hidden mr-4 p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition cursor-pointer"
            >
              <i class="fas fa-bars text-lg"></i>
            </button>
            <div>
              <h2 class="text-lg font-bold text-gray-800 tracking-tight">{{ pageTitle }}</h2>
            </div>
          </div>
          <div class="flex items-center gap-4">
          </div>
        </div>
      </header>

      <!-- Page Content -->
      <div class="flex-1 max-w-8xl w-full">
        <slot />
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'

const page = usePage()
const isSidebarOpen = ref(false)

const pageTitle = computed(() => {
  const titles = {
    '/ecourse/dashboard': 'Dashboard Overview',
    '/ecourse/catalog': 'Catalog',
    '/ecourse/my-journey': 'My Journey',
    '/ecourse/player': 'Module Player',
    '/ecourse/certificates': 'Certificates',
  }
  return titles[page.url] || 'E-Course'
})

function isActive(path) {
  return page.url.startsWith(path)
}

async function handleLogout() {
  // Logout implementation using form submission
  const form = document.createElement('form')
  form.method = 'POST'
  form.action = '/logout'
  form.innerHTML = '<input type="hidden" name="_token" value="' + document.querySelector('meta[name="csrf-token"]').content + '">'
  document.body.appendChild(form)
  form.submit()
}

function goExternal(url) {
  // Full-page navigation for routes that must live outside the SPA
  window.location.href = url
}

// Close sidebar on route change
watch(() => page.url, () => {
  isSidebarOpen.value = false
})

</script>

<style scoped>
/* Smooth transitions */
aside {
  @apply transition-transform duration-300;
}
</style>
