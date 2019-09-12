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
      } = this

      this.$emit('addAccessory', {
        priceFormatted,
        benefitList,
        imageUrl,
        quantity,
        name,
        price,
        id,
      })
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
          accept: 'application/json',
            'content-type': 'application/json'
        },
      })
      .then(({ data }) => {
        this.total = data.value_text;
        return data.value_text;
      });
    },
  },
}
