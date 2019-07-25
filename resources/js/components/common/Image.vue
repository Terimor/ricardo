<template>
  <img :src="src || data.src" alt="">
</template>

<script>
export default {
  name: 'Image',
  props: ['src', 'data']
}
</script>

<style>

</style>
