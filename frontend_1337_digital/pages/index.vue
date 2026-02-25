<template>

  <AppLayout>
    <TorrentHead v-if="home_data.images.length > 0" :images="home_data.images" />
    <TorrentHome v-for="(torrents, index) in home_data.torrents" :key="index" :torrents="torrents" />
  </AppLayout>
</template>

<script setup>

import AppLayout from '~/layouts/AppLayout.vue';
import TorrentHead from '~/components/TorrentHead.vue';
import TorrentHome from '~/components/TorrentHome.vue';

const config = useRuntimeConfig()

const { data: home_data } = await useFetch(`${config.public.apiBase}/torrents/home`)

//  Inject SEO tags dynamically
if (home_data.value) {

  useHead({
    title: home_data.value.title,
    meta: [
      { name: 'description', content: home_data.value.description },

      { property: 'og:title', content: home_data.value.title },
      { property: 'og:image', content: 'https://i.imgur.com/vlsT9e7.png' },
      { property: 'og:description', content: home_data.value.description },

    ],
  })
}

</script>