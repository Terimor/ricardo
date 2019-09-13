export default {
  methods: {
    addToCart (quantity) {
      const {
        priceFormatted,
        benefitList,
        imageUrl,
        price,
        name,
        id,
      } = this;

      const cartData = {
        priceFormatted,
        benefitList,
        imageUrl,
        quantity,
        name,
        price,
        id,
      }

      const oldSubOrders = JSON.parse(localStorage.getItem('subOrder'));

      if (oldSubOrders && id) {
        oldSubOrders.push(cartData);
        localStorage.setItem('subOrder', JSON.stringify(oldSubOrders));
      }

      this.$emit('addAccessory', cartData)
    },
  },
}
