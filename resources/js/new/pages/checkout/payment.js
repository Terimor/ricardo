import credit_cards from './payment/credit_cards';
import payment_credit_card from './payment/payment_credit_card';
import payment_paypal from './payment/payment_paypal';
import paypal_button from './payment/paypal_button';


export default {

  mixins: [
    credit_cards,
    payment_credit_card,
    payment_paypal,
    paypal_button,
  ],


  computed: {

    is_paypal_hidden() {
      return !!js_data.product.is_paypal_hidden;
    },

  },


  methods: {

    goto_thankyou_page(order, currency) {
      let url = js_data.product.upsells.length > 0
        ? '/thankyou-promos'
        : '/thankyou';

      url += '?order=' + encodeURIComponent(order);
      url += '&cur=' + encodeURIComponent(currency);

      if (js_query_params.cop_id) {
        url += '&cop_id=' + encodeURIComponent(js_query_params.cop_id);
      }

      localStorage.setItem('odin_order_created_at', new Date());

      this.goto(url, ['3ds', '3ds_restore']);
    },

    get_local_order_amount() {
      if (!this.form.deal || !js_data.product.prices[this.form.deal]) {
        return 0;
      }

      const price = js_data.product.prices[this.form.deal].value || 0;
      const exchange_rate = js_data.product.prices.exchange_rate || 1;

      const warranty = this.form.warranty
        ? js_data.product.prices[this.form.deal].warranty_price || 0
        : 0;

      let result = ((price + warranty) / exchange_rate) || 0;
      result = Math.round(result * 100) / 100;

      return result;
    },

  },

};
