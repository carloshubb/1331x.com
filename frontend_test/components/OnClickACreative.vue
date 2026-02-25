<template>
  <div>
    <h1>Homepage Ads</h1>
    <div :id="spotId" :style="{width:'300px', height:'100px'}" :data-nat="spotId"></div>
  </div>
</template>

<script setup lang="ts">
const config = useRuntimeConfig()
const spotId = config.public.onclickaSpotId
</script>
