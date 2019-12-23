export default {
  methods: {
    setDataToLocalStorage(data) {
      const skus = Array.isArray(js_data.product.skus)
          ? js_data.product.skus
          : [];

      const selectedProductData = {
        ...data,
        quantity: data.deal,
        upsells: js_data.product.upsells,
        prices: js_data.product.prices[data.deal],
        product_name: js_data.product.product_name,
        image: data.variant
          ? skus.find(it => it.code === data.variant).quantity_image[1]
          : null,
      };

      localStorage.setItem('selectedProductData', JSON.stringify(selectedProductData));
    },
    getOrderAmount(deal, isWarrantyChecked) {
      if (!deal || !js_data.product.prices[deal]) {
        return 0;
      }

      const price = js_data.product.prices[deal].value || 0;
      const exchange_rate = js_data.product.prices.exchange_rate || 1;

      const warranty = isWarrantyChecked
        ? js_data.product.prices[deal].warranty_price || 0
        : 0;

      let result = ((price + warranty) / exchange_rate) || 0;
      result = Math.round(result * 100) / 100;

      return result;
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


      if (selectedProductData.product_name === js_data.product.product_name) {
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
