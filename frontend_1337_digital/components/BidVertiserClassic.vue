<template>
  <ClientOnly>
    <div ref="container"></div>
  </ClientOnly>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const container = ref(null)

onMounted(() => {
  if (!container.value) return

  const script = document.createElement('script')
  script.type = 'text/javascript'
  script.async = true
  script.setAttribute('data-cfasync', 'false')
  script.src = '//bdv.bidvertiser.com/BidVertiser.dbm?pid=941225&bid=2103441'

  container.value.appendChild(script)
})
</script>
