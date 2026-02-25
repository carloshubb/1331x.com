<template>
  <div
    ref="bannerContainer"
    class="ad-banner"
  ></div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue'

const props = defineProps({
  adKey: { type: String, required: true },
  width: { type: Number, default: 320 },
  height: { type: Number, default: 50 }
})

const bannerContainer = ref(null)

onMounted(async () => {
  await nextTick()

  if (!bannerContainer.value) return

  // Clear container
  bannerContainer.value.innerHTML = ''

  // 1️⃣ Required global config for invoke.js
  window.atOptions = {
    key: props.adKey,
    format: 'iframe',
    width: props.width,
    height: props.height,
    params: {}
  }

  // 2️⃣ Load invoke.js (only once per ad key)
  const scriptSrc = `https://venisonglum.com/${props.adKey}/invoke.js`

  if (!document.querySelector(`script[src="${scriptSrc}"]`)) {
    const script = document.createElement('script')
    script.src = scriptSrc
    script.async = true
    bannerContainer.value.appendChild(script)
  }
})
</script>

<style scoped>
.ad-banner {
  margin: 0 auto;
  overflow: hidden;
  text-align: center;
}
</style>
