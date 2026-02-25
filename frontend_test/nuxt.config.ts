// nuxt.config.ts
import { ofetch } from 'ofetch'
export default defineNuxtConfig({
  devtools: { enabled: true },

  postcss: {
    plugins: {
      tailwindcss: {},
      autoprefixer: {},
    },
  },

  compatibilityDate: "latest",
  ssr: true,
  css: ['~/assets/css/main.css', '~/assets/css/icons.css'],

  modules: ['@nuxtjs/tailwindcss', '@nuxtjs/critters', '@nuxtjs/sitemap', 'nuxt-schema-org', 'nuxt-seo-utils'],

  runtimeConfig: {
    public: {
      apiBase: 'http://test.1331x.com/api',
      apiImageKey: process.env.NUXT_PUBLIC_API_IMAGE_KEY,
      apiImageUrl: process.env.NUXT_PUBLIC_API_IMAGE_URL,
    },
  },

  critters: {
    preload: 'swap',
    inline: true,
    preconnect: ['https://test.1331x.com'],
  },

  experimental: {
    inlineSSRStyles: true
  },

  build: {
    cssCodeSplit: true
  },

  app: {
    head: {
      htmlAttrs: { lang: 'en' },
      link: [
        { rel: 'icon', type: 'image/x-icon', href: '/favicon.ico' },
        { rel: 'preload', href: '/fonts/Flaticon.woff', as: 'font', type: 'font/woff2', crossorigin: 'anonymous' },
        { rel: 'preload', href: '/fonts/oswald-regular.woff', as: 'font', type: 'font/woff2', crossorigin: 'anonymous' },
        { rel: 'preconnect', href: 'https://test.1331x.com' },
        { rel: 'preconnect', href: 'https://lx1.dyncdn.cc', crossorigin: 'anonymous' },
      ],
      meta: [
        { charset: "utf-8" },
        { name: "viewport", content: "width=device-width, initial-scale=1" },
        { property: 'og:updated_time', content: new Date().toISOString() },
        { name: "google-site-verification", content: "LXBwz24XENBcS-NVwuNOioaR85EGi_NMFLlnaUIuKH4" },
        { name: 'keywords', content: 'Download the best torrents in 2025 - movies, TV shows, music, games, and more. Fast, secure, and updated daily with the latest torrent downloads.' },
        { 'http-equiv': 'X-UA-Compatible', content: 'IE=edge' },
        { name: 'robots', content: 'index, follow, max-image-preview:large' },
      ],
      script: [
        
      ],

    }
  },

  vite: {
    build: {
      minify: 'terser',
      cssCodeSplit: false,
      terserOptions: {
        compress: { drop_console: true, drop_debugger: true },
        format: { comments: false },
      },
    },
  },

  nitro: {
    minify: true,
    routeRules: {
      '/_nuxt/**': {
        // Handle _nuxt paths as static assets, don't pass to router
        headers: { 'Cache-Control': 'public, max-age=31536000, immutable' },
        prerender: false
      },
      '/torrent/**': {
        headers: { 'Last-Modified': new Date().toUTCString() }
      }
    }
  },

  site: {
    url: process.env.NUXT_PUBLIC_SITE_URL || 'https://test.1331x.com',
  },
  seo: {
    canonicalLowercase: false,
    redirectToCanonicalSiteUrl: true,
    robots: false,
  },
})
