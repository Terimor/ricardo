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
        price30d,
        price30dFormatted,
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
        price30d,
        price30dFormatted,
        id,
      }

      let subOrder = [];

      try {
        subOrder = JSON.parse(localStorage.getItem('subOrder')) || [];
      }
      catch (err) {

      }

      if (id) {
        subOrder.push(cartData);
      }

      localStorage.setItem('subOrder', JSON.stringify(subOrder));

      this.$emit('addAccessory', cartData)
    },
  },
}
