import bluesnap from './payment/bluesnap';
import credit_cards from './payment/credit_cards';
import eps_button from './payment/eps_button';
import fingerprint from './payment/fingerprint';
import ipqualityscore from './payment/ipqualityscore';
import payment_apm from './payment/payment_apm';
import payment_credit_card from './payment/payment_credit_card';
import payment_paypal from './payment/payment_paypal';
import paypal_button from './payment/paypal_button';


export default {

  mixins: [
    bluesnap,
    credit_cards,
    eps_button,
    fingerprint,
    ipqualityscore,
    payment_apm,
    payment_credit_card,
    payment_paypal,
    paypal_button,
  ],


  computed: {

    is_paypal_hidden() {
      return !!js_data.product.is_paypal_hidden || this.form.installments !== 1;
    },

    paypal_payment_method() {
      return this.payment_methods.instant_transfer || null;
    },

    is_apm_visible() {
      return !!this.eps_method;
    },

    eps_method() {
      return this.payment_methods.eps || null;
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

      if (js_data.product.upsells.length > 0) {
        localStorage.setItem((this.is_vrtl_checkout ? 'vrtl_' : '') + 'show_upsells', true);
      }

      this.goto(url, ['3ds', '3ds_restore']);
    },

  },

};
