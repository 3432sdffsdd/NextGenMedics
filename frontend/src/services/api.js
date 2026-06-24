import axios from 'axios'

const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost/LMS/backend/public'

const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
  },
})

// Attach JWT token to every request
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('ngm_token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// Handle 401 globally
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('ngm_token')
      localStorage.removeItem('ngm_user')
    }
    return Promise.reject(error)
  }
)

export const authService = {
  login: (email, password) => api.post('/auth/login', { email, password }),
  me: () => api.get('/auth/me'),
  logout: () => {
    localStorage.removeItem('ngm_token')
    localStorage.removeItem('ngm_user')
  },
}

export const coursesService = {
  list: () => api.get('/courses'),
  get: (slug) => api.get(`/courses/${slug}`),
}

export const contactService = {
  send: (payload) => api.post('/contact', payload),
}

export const resourcesService = {
  list: (type) => api.get(`/resources${type ? `?type=${type}` : ''}`),
}

export const mentorsService = {
  list: () => api.get('/mentors'),
}

export const testimonialsService = {
  list: () => api.get('/testimonials'),
}

export const announcementsService = {
  list: () => api.get('/announcements'),
}

export default api
