export default {
  methods: {
    addToCart (quantity) {
      const {
        priceFormatted,
        benefitList,
        finalPrice,
        finalPricePure,
        imageUrl,
        price,
        name,
        id,
      } = this;

      const cartData = {
        priceFormatted,
        benefitList,
        finalPrice,
        finalPricePure,
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

      if (!oldSubOrders) {
        localStorage.setItem('subOrder', JSON.stringify([cartData]));
      }

      this.$emit('addAccessory', cartData)
    },
  },
}
