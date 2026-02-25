<template>
  <!-- Begin BidVertiser code -->
  <div ref="ad"></div>
  <!-- End BidVertiser code -->
</template>

<script setup>
import { onMounted, ref } from 'vue'

const ad = ref(null)

onMounted(() => {
  if (!ad.value) return

  // Prevent duplicate load (important for Nuxt routing)
  if (ad.value.dataset.loaded) return
  ad.value.dataset.loaded = 'true'

  const s = document.createElement('script')
  s.type = 'text/javascript'
  s.src = '//bdv.bidvertiser.com/BidVertiser.dbm?pid=941225&bid=2103441'
  s.setAttribute('data-cfasync', 'false')

  ad.value.appendChild(s)
})
</script>
