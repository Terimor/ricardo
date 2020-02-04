import bluesnap from './payment/bluesnap';
import fingerprint from './payment/fingerprint';
import credit_cards from './payment/credit_cards';
import ipqualityscore from './payment/ipqualityscore';
import payment_credit_card from './payment/payment_credit_card';
import payment_paypal from './payment/payment_paypal';
import paypal_button from './payment/paypal_button';


export default {

  mixins: [
    bluesnap,
    fingerprint,
    credit_cards,
    ipqualityscore,
    payment_credit_card,
    payment_paypal,
    paypal_button,
  ],


  computed: {

    is_paypal_hidden() {
      return !!js_data.product.is_paypal_hidden || this.form.installments !== 1;
    },

  },


  methods: {

    goto_upsells(order, currency) {
      let url = js_data.product.upsells.length > 0
        ? !this.is_vrtl_checkout
          ? '/thankyou-promos'
          : '/vrtl/upsells'
        : !this.is_vrtl_checkout
          ? '/thankyou'
          : '/vrtl/thankyou';

      url += '?order=' + encodeURIComponent(order);
      url += '&cur=' + encodeURIComponent(currency);

      if (js_query_params.cop_id) {
        url += '&cop_id=' + encodeURIComponent(js_query_params.cop_id);
      }

      localStorage.setItem('odin_order_created_at', new Date());

      this.goto(url, ['3ds', '3ds_restore']);
    },

  },

};
