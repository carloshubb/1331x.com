<template>
  <div class="mbid-banners">
    <div   
      :data-banner-id="2014612"
      class="mbid-banner"
    />
  </div>
</template>
<script setup lang="ts">
onMounted(() => {
  // Prevent loading script multiple times
  if (!document.querySelector('script[data-admpid="413375"]')) {
    const script = document.createElement('script')
    script.src = 'https://js.mbidadm.com/static/scripts.js'
    script.async = true
    script.setAttribute('data-admpid', '413375')
    document.body.appendChild(script)
  }
})
</script>
