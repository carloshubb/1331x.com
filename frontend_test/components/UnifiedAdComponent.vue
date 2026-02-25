<template>
  <div
    :ref="el => containerRef = el"
    :class="`ad-container ad-${adType}`"
    :style="containerStyle"
  ></div>
</template>

<script setup>
import { ref, onMounted, nextTick, computed } from 'vue'

const props = defineProps({
  adType: {
    type: String,
    required: true,
    validator: (value) => ['mobile', 'pc'].includes(value)
  },
  width: { type: Number, default: null },
  height: { type: Number, default: null }
})

const containerRef = ref(null)

// Ad configurations(Adsterra keys and sizes)
const adConfigs = {
  mobile: {
    key: "d146ad82eefd5313e355875ccc47bdd5",
    width: 320,
    height: 50
  },
  pc: {
    key: "89eb9d4ecf0d2fe16576dae1b907df02",
    width: 728,
    height: 90
  }
}

const currentConfig = computed(() => ({
  ...adConfigs[props.adType],
  ...(props.width && { width: props.width }),
  ...(props.height && { height: props.height })
}))

const containerStyle = computed(() => ({
  minHeight: `${currentConfig.value.height}px`,
  width: '100%',
  maxWidth: `${currentConfig.value.width}px`,
  margin: '0 auto',
  display: 'block'
}))

// Global queue to ensure ads load sequentially
if (typeof window !== 'undefined' && !window.__adQueue) {
  window.__adQueue = []
  window.__adQueueProcessing = false
}

const processAdQueue = async () => {
  if (typeof window === 'undefined') return
  
  if (window.__adQueueProcessing) return
  
  window.__adQueueProcessing = true
  
  while (window.__adQueue.length > 0) {
    const adLoader = window.__adQueue.shift()
    await adLoader()
    // Small delay between ads
    await new Promise(resolve => setTimeout(resolve, 100))
  }
  
  window.__adQueueProcessing = false
}

const loadAd = () => {
  return new Promise((resolve) => {
    if (!containerRef.value) {
      resolve()
      return
    }

    const config = currentConfig.value
    const scriptSrc = `https://venisonglum.com/${config.key}/invoke.js`
    const scriptId = `ad-script-${config.key}`
    
    // Clear container
    containerRef.value.innerHTML = ''
    
    // Check if script already loaded
    const existingScript = document.getElementById(scriptId)
    
    if (existingScript) {
      console.log(`${props.adType} ad: Script already loaded, skipping`)
      resolve()
      return
    }

    // Set atOptions for this ad
    window.atOptions = {
      key: config.key,
      format: 'iframe',
      width: config.width,
      height: config.height,
      params: {}
    }

    // Create and append script
    const script = document.createElement('script')
    script.id = scriptId
    script.src = scriptSrc
    script.async = true
    script.type = 'text/javascript'
    
    script.onload = () => {
      console.log(`${props.adType} ad loaded successfully`)
      resolve()
    }
    
    script.onerror = () => {
      console.error(`${props.adType} ad failed to load`)
      resolve()
    }
    
    containerRef.value.appendChild(script)
  })
}

onMounted(async () => {
  await nextTick()
  
  // Add to queue and process
  if (typeof window !== 'undefined') {
    window.__adQueue.push(loadAd)
    processAdQueue()
  }
})
</script>

<style scoped>
</style>