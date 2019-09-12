export default {
  methods: {
    addToCart (quantity) {
      const {
        name,
        benefitList,
        imageUrl,
        price,
        id,
      } = this

      this.$emit('addAccessory', {
        quantity,
        name,
        benefitList,
        imageUrl,
        price,
        id,
      })
    }
  }
}
