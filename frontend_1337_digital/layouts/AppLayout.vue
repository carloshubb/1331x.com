<!-- layouts/default.vue -->
<template>
  <div class="min-h-screen bg-gradient-to-r from-gray-300 to-gray-700 text-white">
    <!-- Header -->
    <Header />
    <!-- Main Layout -->
    <div class="max-w-7xl mx-auto px-2">
      <Sidebar class="md:hidden py-2" :is_open="false" />
      <div class="xl:grid xl:grid-cols-5 lg:grid lg:grid-cols-4 gap-4">
        <!-- Content -->
        <main class="xl:col-span-4 lg:col-span-3 py-2">
          <slot />
        </main>
        <!-- Sidebar -->
        <Sidebar class="hidden md:block py-2" />
      </div>
    </div>

    <!-- Footer -->
    <Footer />
  </div>
</template>

<script setup>
// Components are auto-imported in Nuxt 3
import { onMounted } from 'vue'
import Header from '~/components/layout/Header.vue';
import Sidebar from '~/components/layout/Sidebar.vue';
import Footer from '~/components/layout/Footer.vue';
const scriptStatus = useCookie('venisong_script_status', {
  maxAge: 60 * 60 * 24, // 1 day
  sameSite: 'lax'
})
const route = useRoute()
useHead({
  titleTemplate: (titleChunk) => {
    return titleChunk ? `${titleChunk} Free Download` : 'Free Download'
  },
  meta: [

    { property: 'og:type', content: 'website' },

    { property: 'og:url', content: `https://1337x.digital${route.path}` },
    { property: 'og:site_name', content: 'Ultimate Torrent Hub' }
  ],
  link: [
    {
      rel: 'canonical',
      href: `https://1337x.digital${route.path}`
    }
  ], 
  
})

useSchemaOrg([
  defineWebSite({
    name: 'Torrent free download'
  })
])



const isMenuOpen = ref(false);
</script>