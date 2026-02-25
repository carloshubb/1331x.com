<template>
  <div
    ref="bannerContainer"
    class="ad-pc-banner"
    style="min-height: 90px; width: 100%; max-width: 728px; margin: 0 auto; display: block;"
  ></div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue'

const props = defineProps({  
  width: { type: Number, default: 728 },
  height: { type: Number, default: 90 }
})

const bannerContainer = ref(null)

onMounted(async () => {
  await nextTick()

  if (!bannerContainer.value) return

  // Clear container
  bannerContainer.value.innerHTML = ''

  // Unique ID for this ad
  const adKey = "89eb9d4ecf0d2fe16576dae1b907df02"
  const scriptSrc = `https://venisonglum.com/${adKey}/invoke.js`
  
  // Check if script already exists
  const existingScript = document.querySelector(`script[src="${scriptSrc}"]`)
  
  if (!existingScript) {
    // Set global options BEFORE loading script
    window.atOptions = {
      key: adKey,
      format: 'iframe',
      width: props.width,
      height: props.height,
      params: {}
    }

    const script = document.createElement('script')
    script.src = scriptSrc
    script.async = true
    script.type = 'text/javascript'
    
    script.onload = () => {
      console.log('PC ad script loaded successfully')
    }
    
    script.onerror = () => {
      console.error('PC ad script failed to load')
    }
    
    bannerContainer.value.appendChild(script)
  } else {
    console.log('PC ad script already exists')
  }
})
</script>

<style scoped>
.ad-pc-banner {
  overflow: visible;
}

.ad-pc-banner iframe {
  display: block !important;
  visibility: visible !important;
  margin: 0 auto;
}
</style>