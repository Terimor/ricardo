export default {
  computed: {
    isMobile () {
      return ['s', 'xs'].includes(this.$mq)
    }
  }
}
