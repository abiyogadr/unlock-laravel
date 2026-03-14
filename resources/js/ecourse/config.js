/**
 * E-Course Configuration
 * Settings untuk E-Course Vue Application
 */

// API Configuration
export const API_CONFIG = {
  BASE_URL: '/unlock',
  API_PREFIX: '/ecourse/api',
  TIMEOUT: 30000, // 30 seconds
  RETRY_ATTEMPTS: 3,
  RETRY_DELAY: 1000, // 1 second
}

// Pagination
export const PAGINATION = {
  DEFAULT_PER_PAGE: 12,
  COURSE_PER_PAGE: 12,
  CERTIFICATE_PER_PAGE: 9,
}

// Search Configuration
export const SEARCH = {
  DEBOUNCE_MS: 700,
  MIN_CHARACTERS: 1,
}

// Video Player Configuration
export const VIDEO_PLAYER = {
  AUTOPLAY: false,
  CONTROLS: true,
  PLAYBACK_RATES: [0.5, 0.75, 1, 1.25, 1.5, 1.75, 2],
  DEFAULT_PLAYBACK_RATE: 1,
}

// Course Levels
export const COURSE_LEVELS = [
  { value: 'beginner', label: 'Beginner', color: 'green' },
  { value: 'intermediate', label: 'Intermediate', color: 'yellow' },
  { value: 'advanced', label: 'Advanced', color: 'red' },
]

// Module Progress Thresholds
export const PROGRESS_THRESHOLDS = {
  NOT_STARTED: 0,
  IN_PROGRESS: 50,
  COMPLETED: 100,
}

// Cache Configuration
export const CACHE = {
  ENABLED: true,
  DURATION_MS: 5 * 60 * 1000, // 5 minutes
  CATEGORIES_KEY: 'ecourse_categories',
  COURSES_KEY: 'ecourse_courses',
}

// Notification Configuration
export const NOTIFICATIONS = {
  DURATION_SUCCESS: 3000,
  DURATION_ERROR: 5000,
  DURATION_WARNING: 4000,
  DURATION_INFO: 3000,
  MAX_NOTIFICATIONS: 5,
}

// Feature Flags
export const FEATURES = {
  DISCUSSION_ENABLED: false,
  CERTIFICATES_ENABLED: true,
  LIVE_CLASSES_ENABLED: false,
  COURSE_RECOMMENDATIONS_ENABLED: false,
}

// Routes
export const ROUTES = {
  DASHBOARD: '/ecourse/dashboard',
  CATALOG: '/ecourse/catalog',
  MY_JOURNEY: '/ecourse/my-journey',
  CERTIFICATES: '/ecourse/certificates',
  LOGIN: '/login',
}

// File Upload
export const FILE_UPLOAD = {
  MAX_SIZE_MB: 100,
  ALLOWED_VIDEO_TYPES: ['video/mp4', 'video/webm', 'video/ogg'],
  ALLOWED_DOCUMENT_TYPES: ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
}

// Analytics (if needed)
export const ANALYTICS = {
  ENABLED: false,
  TRACK_VIEWS: true,
  TRACK_CLICKS: true,
}

export default {
  API_CONFIG,
  PAGINATION,
  SEARCH,
  VIDEO_PLAYER,
  COURSE_LEVELS,
  PROGRESS_THRESHOLDS,
  CACHE,
  NOTIFICATIONS,
  FEATURES,
  ROUTES,
  FILE_UPLOAD,
  ANALYTICS,
}
