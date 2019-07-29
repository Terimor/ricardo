<template>
    <img :src="`https://www.countryflags.io/${data.countryCode}/flat/64.png`">
</template>

<script>
export default {
  name: 'CountryImage',
  props: ['src', 'data']
}
</script>

<style>
</style>
