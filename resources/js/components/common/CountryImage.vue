<template>
    <img class="country-image" :src="`/images/country-flags/${data.countryCode}.png`">
</template>

<script>
export default {
  name: 'CountryImage',
  props: ['src', 'data']
}
</script>

<style>
</style>
