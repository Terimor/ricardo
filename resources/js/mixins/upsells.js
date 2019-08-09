export default {
  methods: {
    addToCart (quantity) {
      const {
        name,
        benefitList,
        imageUrl,
        price,
      } = this

      this.$emit('addAccessory', {
        quantity,
        name,
        benefitList,
        imageUrl,
        price,
      })
    }
  }
}
