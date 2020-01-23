export default {

  computed: {

    total_price() {
      return js_data.product.prices[this.quantity].value_text;
    },

  },


  methods: {

    goto_checkout() {
      this.goto('/checkout?qty=' + this.quantity);
    },

  },

};
