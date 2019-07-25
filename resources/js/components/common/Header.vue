<template>
  <header :style="{ ...withTimer && isMobile && { 'margin-top': '45px' } }">
    <div class="container">
      <Timer v-if="withTimer"></Timer>
      <slot></slot>
    </div>
  </header>
</template>

<script>
import Timer from './Timer'
import isMobile from '../../mixins/isMobile'

export default {
  name: 'Header',
  mixins: [isMobile],
  props: ['withTimer'],
  components: {
    Timer
  },
  methods: {

  }
}
</script>

<style scoped lang="scss">
@import "../../../sass/variables";

header {
  display: flex;

  & > .container {
    display: flex;
    height: 100%;
    justify-content: space-between;
    align-items: center;
  }

  @media screen and ($s-down) {
    & > .container {
      justify-content: center;
    }
  }
}
</style>
