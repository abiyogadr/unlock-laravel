/**
 * Format price to Indonesian currency
 */
export function formatPrice(price) {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(price)
}

/**
 * Format date to Indonesian locale
 */
export function formatDate(date, options = {}) {
  const defaultOptions = { 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric',
    ...options
  }
  return new Date(date).toLocaleDateString('id-ID', defaultOptions)
}

/**
 * Format time duration (e.g., "2 jam 30 menit")
 */
export function formatDuration(minutes) {
  const hours = Math.floor(minutes / 60)
  const mins = minutes % 60
  
  if (hours === 0) return `${mins} menit`
  if (mins === 0) return `${hours} jam`
  return `${hours} jam ${mins} menit`
}

/**
 * Truncate text
 */
export function truncate(text, length = 100) {
  if (text.length <= length) return text
  return text.substring(0, length) + '...'
}

/**
 * Get level badge color
 */
export function getLevelColor(level) {
  const levelMap = {
    'beginner': 'bg-green-100 text-green-700',
    'intermediate': 'bg-yellow-100 text-yellow-700',
    'advanced': 'bg-red-100 text-red-700',
  }
  return levelMap[level?.toLowerCase()] || 'bg-gray-100 text-gray-700'
}

/**
 * Get level icon
 */
export function getLevelIcon(level) {
  const iconMap = {
    'beginner': 'fa-leaf',
    'intermediate': 'fa-arrow-up',
    'advanced': 'fa-star',
  }
  return iconMap[level?.toLowerCase()] || 'fa-book'
}
