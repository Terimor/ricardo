export default {
  computed: {
    isMobile () {
      return ['s', 'xs'].indexOf(this.$mq) !== -1;
    }
  }
}
