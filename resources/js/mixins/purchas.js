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
    },
    getOrderAmount(deal, isWarrantyChecked) {
      if (!deal || !checkoutData.product.prices[deal]) {
        return 0;
      }

      const price = checkoutData.product.prices[deal].value || 0;
      const exchange_rate = checkoutData.product.prices.exchange_rate || 1;

      const warranty = isWarrantyChecked
        ? checkoutData.product.prices[deal].warranty_price || 0
        : 0;

      return (price + warranty) / exchange_rate;
    },
  },
  computed: {
    isPurchasAlreadyExists() {
      let selectedProductData = {};
      const odin_order_created_at = localStorage.getItem('odin_order_created_at');

      try {
        selectedProductData = JSON.parse(localStorage.getItem('selectedProductData')) || {};
      }
      catch (err) {

      }

      if (!odin_order_created_at || !selectedProductData.product_name) {
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
