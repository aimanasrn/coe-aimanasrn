// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  devtools: { enabled: false },

  modules: ['@nuxt/ui'],
  css: ['~/assets/css/main.css'],

  app: {
    head: {
      link: [{ rel: 'icon', type: 'image/svg+xml', href: '/studymart-mark.svg' }]
    }
  },

  runtimeConfig: {
    apiBase: 'http://127.0.0.1:8000/api',
    public: {
      apiBase: '/api'
    }
  }
})
