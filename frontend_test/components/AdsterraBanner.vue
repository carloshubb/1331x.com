<template>
  <div v-if="isAdAvailable" :class="{ 'fixed bottom-0 left-0 right-0 z-50 bg-gradient-to-r from-orange-500 to-gray-500': !isDesktop }">
    <div
    ref="bannerContainer"
    class="ad-mobile-banner flex items-center justify-center"
    :class="{ 'neon-flicker border-t border-white': !isDesktop }"
    style="width: 100%;"
    ></div>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick, computed, watch } from 'vue'
const { $AD_NATIVE_CONTAINER_HTML } = useNuxtApp()

// Safely get plugin values with fallbacks for SSR
const nuxtApp = useNuxtApp()
const $getBannerConfig = nuxtApp.$getBannerConfig || (() => null)
const $isMobileDevice = nuxtApp.$isMobileDevice || ref(false)

const bannerContainer = ref(null)
const isMounted = ref(false)

// Use device detection from plugin, but allow override via props if needed
const props = defineProps({
  isDesktop: {
    type: Boolean,
    default: undefined
  }
})

// Determine if desktop: use prop if provided, otherwise use plugin's device detection
// Always default to mobile on server to ensure consistent rendering
const isDesktop = computed(() => {
  // On server, always return false (mobile) to ensure consistent rendering
  if (process.server) {
    return false
  }
  
  // If prop is provided, use it (but only after mount to avoid hydration issues)
  if (props.isDesktop !== undefined && isMounted.value) {
    return props.isDesktop
  }
  
  // Before mount, default to false (mobile)
  if (!isMounted.value) {
    return false
  }
  
  // After mount, use plugin's device detection
  const isMobile = $isMobileDevice?.value ?? false
  return !isMobile
})

// Check if primary script is available (isPrimaryAvailable from plugin)
// isPrimaryAvailable is set to AD_NATIVE_CONTAINER_HTML string when script is available, null otherwise
const isAdAvailable = computed(() => {
  // Only show banner when isPrimaryAvailable has a value (not null)
  // This means the primary script availability check passed
  const value = $AD_NATIVE_CONTAINER_HTML?.value
  return value !== null && value !== undefined && value !== ''
})

function loadBanner() {
  // Only run on client
  if (process.server) return
  if (!bannerContainer.value) return

  // Check if primary script is available (isPrimaryAvailable from plugin)
  // Only load banner if isPrimaryAvailable has been set (not null)
  const isPrimaryAvailable = $AD_NATIVE_CONTAINER_HTML?.value
  if (!isPrimaryAvailable || isPrimaryAvailable === null || isPrimaryAvailable === '') {
    return
  }

  // Clear container
  bannerContainer.value.innerHTML = ''
  
  // Get banner configuration from plugin based on device type
  const bannerConfig = $getBannerConfig?.()
  if (!bannerConfig) {
    console.error('Banner config not available')
    return
  }
  
  const scriptSrc = bannerConfig.scriptUrl
  const scriptId = bannerConfig.scriptId
  
  // Check if script already exists
  const existingScript = document.getElementById(scriptId) || document.querySelector(`script[src="${scriptSrc}"]`)
  
  if (!existingScript) {
    // Set global options BEFORE loading script
    if (typeof window !== 'undefined') {
      window.atOptions = {
        key: bannerConfig.key,
        format: bannerConfig.format,
        width: bannerConfig.width,
        height: bannerConfig.height,
        params: bannerConfig.params
      }
    }

    const script = document.createElement('script')
    script.id = scriptId
    script.src = scriptSrc
    script.async = true
    script.type = 'text/javascript'
    
    script.onload = () => {
      // console.log(`${isDesktop.value ? 'Desktop' : 'Mobile'} ad script loaded successfully`)
    }
    
    script.onerror = () => {
      // console.error(`${isDesktop.value ? 'Desktop' : 'Mobile'} ad script failed to load`)
    }
    
    bannerContainer.value.appendChild(script)
    
  } else {
    console.log('Ad script already exists')
  }
}

onMounted(async () => {
  isMounted.value = true
  await nextTick()

  // Function to attempt loading the banner
  const tryLoadBanner = async () => {
    const isPrimaryAvailable = $AD_NATIVE_CONTAINER_HTML?.value
    
    if (isPrimaryAvailable && isPrimaryAvailable !== null && isPrimaryAvailable !== '') {
      // Wait for next tick to ensure bannerContainer is available after v-if renders
      await nextTick()
      
      if (bannerContainer.value) {
        loadBanner()
        return true
      } else {
        return false
      }
    }
    return false
  }

  // Watch for isPrimaryAvailable changes from plugin
  watch(() => $AD_NATIVE_CONTAINER_HTML?.value, async (isPrimaryAvailable) => {
    await tryLoadBanner()
  }, { immediate: true })
  
  // Also watch for bannerContainer to become available (when v-if renders)
  watch(() => bannerContainer.value, async (container) => {
    if (container) {
      await tryLoadBanner()
    }
  })
  
  // Try immediately if already available
  await tryLoadBanner()
})
</script>

<style scoped>
.ad-mobile-banner {  
  width: 100%;  

  background: linear-gradient(
    135deg,
    rgba(0, 255, 255, 0.15),
    rgba(255, 0, 255, 0.15)
  );
  
  box-shadow:
    0 0 6px rgba(249, 115, 22, 1)
    0 0 14px rgba(249, 115, 22, 0.6),
    0 0 24px rgba(249, 115, 22, 0.2)
;

  padding: 0px;
  overflow: hidden;
  position: relative;
}


/* Flicker animation */
.neon-flicker {
  animation: flicker 2.5s infinite alternate;
}

/* Pulse glow */
@keyframes flicker {
  0% {
    box-shadow:
      0 0 6px rgba(249, 115, 22, 1),
      0 0 14px rgba(249, 115, 22, 0.6),
      0 0 24px rgba(249, 115, 22, 0.2);
    opacity: 1;
  }
  45% {
    opacity: 0.95;
  }
  55% {
    opacity: 0.85;
  }
  100% {
    box-shadow:
      0 0 10px rgba(249, 115, 22, 1),
      0 0 22px rgba(249, 115, 22, 0.9),
      0 0 36px rgba(249, 115, 22, 0.7);
    opacity: 1;
  }
}

/* Make sure iframe stays centered */
.ad-mobile-banner iframe {
  display: block !important;
  
}
</style>
