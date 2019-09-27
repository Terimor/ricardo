export default {
  methods: {
    setDataToLocalStorage() {
      const currentVariant = this.skusList.find(it => it.code === this.form.variant);
      const prices = this.checkoutData.product.prices;
      const selectedProductData = {
        upsells: this.productData.upsells,
        prices: prices[this.radioIdx],
        quantity: this.radioIdx,
        isWarrantyChecked: this.form.isWarrantyChecked,
        variant: this.form.variant,
        product_name: this.productData.product_name,
        image: currentVariant && currentVariant.quantity_image[1]
      };

      localStorage.setItem('selectedProductData', JSON.stringify(selectedProductData));
    }
  },
  computed: {
    isPurchasAlreadyExists() {
      const selectedProductData = JSON.parse(localStorage.getItem('selectedProductData'));
      const odin_order_created_at = localStorage.getItem('odin_order_created_at');

      if (!odin_order_created_at || !selectedProductData) {
        return false
      }


      if (selectedProductData.product_name === this.productData.product_name) {
        const now = new Date()
        const then = odin_order_created_at
        const diffinMilliseconds = Date.parse(now) - Date.parse(then);
        const diffInMinutes = diffinMilliseconds / 1000 / 60;
        const timeLimit = 30;

        if  (parseInt(diffInMinutes) >= timeLimit) {
          localStorage.removeItem('odin_order_created_at');
          return false
        } else {
          return true;
        }
      }
    }
  }
}
