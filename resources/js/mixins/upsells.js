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

    getTotalPrice(data, total) {
      return axios
        .post(`${window.location.origin}/calculate-upsells-total`,
          {
            upsells: data,
            total: total
          },
          {
            credentials: 'same-origin',
            headers: {
              accept:
              'application/json',
              'content-type': 'application/json'
            },
          })
        .then(({ data }) => {
          this.total = data.value_text;
          return data.value_text;
        });
    },

    getUppSells(product_id, quantity) {
      return axios
        .get(`${window.location.origin}/upsell-product/${product_id}?quantity=${quantity}`)
        .then((res) => {
          return res
        });
    },
  },
}
