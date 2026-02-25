<template>
  <NuxtLayout>
    <NuxtPage />

    <!-- Ads must be client-only -->
    <ClientOnly>
      <!-- <AdScript /> -->
    </ClientOnly>
  </NuxtLayout>
</template>

<script setup>
import AdScript from '~/components/AdScript.vue'
</script>
