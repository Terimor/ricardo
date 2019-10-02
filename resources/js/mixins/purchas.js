export default {
  methods: {
    setDataToLocalStorage(data) {
      const selectedProductData = {
        ...data,
        quantity: data.deal,
        upsells: checkoutData.product.upsells,
        prices: checkoutData.product.prices[data.deal],
        product_name: checkoutData.product.product_name,
        image: checkoutData.product.skus.find(it => it.code === data.variant).quantity_image[1],
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


      if (selectedProductData.product_name === checkoutData.product.product_name) {
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
