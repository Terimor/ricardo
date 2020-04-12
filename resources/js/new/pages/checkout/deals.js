export default {

  computed: {

    product_main_image() {
      const index = +(js_query_params.image || null) - 1;
      return js_data.product.image[index] || js_data.product.image[0];
    },

    deal_images_per_quantity() {
      return js_data.deal_available.reduce((acc, quantity) => {
        const variant = this.form.variant || (js_data.product.skus[0] && js_data.product.skus[0].code);
        const sku_variant = js_data.product.skus.find && js_data.product.skus.find(sku => sku.code === variant);

        acc[quantity] = sku_variant && (sku_variant.quantity_image[quantity] || sku_variant.quantity_image[1]) || this.product_main_image;

        return acc;
      }, {});
    },

  },

};
