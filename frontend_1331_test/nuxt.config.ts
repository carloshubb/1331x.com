// https://nuxt.com/docs/api/configuration/nuxt-config

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
      apiBase: 'https://neon.shipfaststar.com/api',
      apiImageKey: process.env.NUXT_PUBLIC_API_IMAGE_KEY,
      apiImageUrl: process.env.NUXT_PUBLIC_API_IMAGE_URL,
    }
  },
  critters: {
    preload: 'swap', // preload fonts/CSS
    inline: true, // inline critical CSS into HTML
    preconnect: ['https://neon.shipfaststar.com'],
  },
  experimental: {
    // Inline SSR styles so above-the-fold CSS is delivered with HTML,
    // reducing the impact of the main stylesheet on LCP.
    inlineSSRStyles: true
  },
  build: {
    cssCodeSplit: true
  },
  app: {
    head: {
      htmlAttrs: {
        lang: 'en'
      },
      link: [
        { rel: 'icon', type: 'image/x-icon', href: '/favicon.ico' },
        { rel: 'preload', href: '/fonts/Flaticon.woff', as: 'font', type: 'font/woff2', crossorigin: 'anonymous' },
        { rel: 'preload', href: '/fonts/oswald-regular.woff', as: 'font', type: 'font/woff2', crossorigin: 'anonymous' },
        { rel: 'preconnect', href: 'https://neon.shipfaststar.com' },
        { rel: 'preconnect', href: 'https://lx1.dyncdn.cc', crossorigin: 'anonymous' },
        // { rel: 'preconnect', href: 'https://www.googletagmanager.com', crossorigin: 'anonymous' },
      ],
      meta: [
        { charset: "utf-8" },
        { name: "viewport", content: "width=device-width, initial-scale=1" },
        { property: 'og:updated_time', content: new Date().toISOString() },
        { name: "google-site-verification", content: "6fNsscqru2jVe7tJjw-82hJONB_my0SF9w7fM4g60_Q" },
        { name: 'keywords', content: 'Download the best torrents in 2026 - movies, TV shows, music, games, and more. Fast, secure, and updated daily with the latest torrent downloads.' },
        { 'http-equiv': 'X-UA-Compatible', content: 'IE=edge' },
        { name: 'robots', content: 'index, follow, max-image-preview:large' },
      ],
      script: [
        // analysis tag
        {
          innerHTML: `
            var _Hasync = _Hasync || [];
            _Hasync.push(['Histats.start', '1,4969467,4,0,0,0,00010000']);
            _Hasync.push(['Histats.fasi', '1']);
            _Hasync.push(['Histats.track_hits', '']);
            (function() {
              var hs = document.createElement('script');
              hs.type = 'text/javascript';
              hs.async = true;
              hs.src = '/js/js15_as.js';
              (document.head || document.body).appendChild(hs);
            })();
          `,
          type: 'text/javascript'
        },
      ],
      noscript: []
    }
  },
  vite: {
    build: {
      minify: 'terser',
      cssCodeSplit: false, // single main CSS again
      terserOptions: {
        compress: {
          drop_console: true,
          drop_debugger: true,
        },
        format: {
          comments: false,
        },
      },
    },
  },
  nitro: {
    minify: true,
    routeRules: {
      '/_nuxt/**': {
        // Handle _nuxt paths as static assets, don't pass to router
        headers: { 
          'Cache-Control': 'public, max-age=31536000, immutable',
          'Access-Control-Allow-Origin': '*', // If needed
        },
        prerender: false
      },
      '/robots.txt': {
        headers: {
          'Content-Type': 'text/plain; charset=utf-8',
          'Cache-Control': 'public, max-age=3600'
        },
        prerender: true
      },
      '/torrent/**': {
        headers: {
          'Last-Modified': new Date().toUTCString()
        },
        // Enable ISR (Incremental Static Regeneration) for torrent pages
        // This caches the SSR response and serves it to all browsers
        isr: 3600, // Cache for 1 hour (backend has forever cache, but Nuxt refreshes periodically)
      }
    }
  },
  site: {
    url: 'https://neon.shipfaststar.com',
  },
  // robots.txt is served from public/robots.txt (static file)
  // The robots config below is for nuxt-seo-utils, but static file takes precedence
  robots: {
    UserAgent: '*',
    Allow: '/',
    Sitemap: 'https://neon.shipfaststar.com/sitemap_index.xml'
  },
  sitemap: {
    cacheTtl: 60 * 60 * 24, // 24 hours caching
    sitemaps: {
      static: {
        urls: [
          { loc: '/', priority: 1.0 },
          { loc: '/rules', priority: 0.5 },
          { loc: '/about', priority: 0.5 },
          { loc: '/contact', priority: 0.5 },
        ]
      },
      page: {
        // includeAppSources: true,
        defaults: {
          changefreq: 'monthly',
          priority: 0.6
        },
        urls: [
          { loc: 'library/1' },
          { loc: 'categories/trending' },
          { loc: 'categories/trending-week' },
          { loc: 'categories/top' },
          { loc: 'top/movies' },
          { loc: 'top/tv' },
          { loc: 'top/games' },
          { loc: 'top/apps' },
          { loc: 'top/music' },
          { loc: 'top/doc' },
          { loc: 'top/other' },
          { loc: 'top/xxx' },
          { loc: 'cat/Anime/1' },
          { loc: 'cat/Apps/1' },
          { loc: 'cat/Documentaries/1' },
          { loc: 'cat/Games/1' },
          { loc: 'cat/Movies/1' },
          { loc: 'cat/Music/1' },
          { loc: 'cat/Other/1' },
          { loc: 'cat/TV/1' },
          { loc: 'cat/XXX/1' },
        ]
      },
      posts: {
        sources: ['https://1331x.com/api/posts/slugs'], // returns 10,000 posts
        chunks: 4500, // Enable chunking with default size (1000)
        defaults: {
          changefreq: 'daily',
          priority: 0.6
        }
      },
    }
  },

  seo: {
    canonicalLowercase: false,
    redirectToCanonicalSiteUrl: true,
  },

})