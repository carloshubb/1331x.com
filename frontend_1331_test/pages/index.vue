<template>
  <AppLayout>

    <!-- Loading UI -->
    <div v-if="pending" class="flex justify-center py-10">
      <span class="loader"></span>
    </div>

    <!-- Error UI -->
    <div v-else-if="error" class="text-center text-red-500">
      Failed to load data
    </div>

    <!-- Actual Content -->
    <template v-else>
      <TorrentHead v-if="home_data?.images?.length" :images="home_data.images" class="hidden md:block"/>      
      <TorrentHome v-for="(torrents, index) in home_data.torrents" :key="index" :torrents="torrents" />
    </template>

  </AppLayout>
</template>

<script setup>
import AppLayout from '~/layouts/AppLayout.vue'
import TorrentHead from '~/components/TorrentHead.vue'
import TorrentHome from '~/components/TorrentHome.vue'

const config = useRuntimeConfig()

const {
  data: home_data,
  pending,
  error
} = await useFetch(`${config.public.apiBase}/torrents/home`)

// SEO after data arrives
watchEffect(() => {
  if (!home_data.value) return

  useHead({
    title: home_data.value.title,
    meta: [
      { name: 'description', content: home_data.value.description },
      { property: 'og:title', content: home_data.value.title },
      { property: 'og:image', content: 'https://i.imgur.com/vlsT9e7.png' },
      { property: 'og:description', content: home_data.value.description },
    ],    
  })
})
</script>
