export default {

  methods: {

    home_products_goto_checkout(sku_code) {
      this.goto('/checkout?product=' + sku_code);
    },

  },

};
