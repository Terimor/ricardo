import apm_buttons from './payment/apm_buttons';
import bluesnap from './payment/bluesnap';
import credit_cards from './payment/credit_cards';
import fingerprint from './payment/fingerprint';
import ipqualityscore from './payment/ipqualityscore';
import payment_apm from './payment/payment_apm';
import payment_credit_card from './payment/payment_credit_card';
import payment_paypal from './payment/payment_paypal';
import paypal_button from './payment/paypal_button';


export default {

  mixins: [
    apm_buttons,
    bluesnap,
    credit_cards,
    fingerprint,
    ipqualityscore,
    payment_apm,
    payment_credit_card,
    payment_paypal,
    paypal_button,
  ],


  data() {
    return {
      payment_methods: js_data.payment_methods
        ? JSON.parse(JSON.stringify(js_data.payment_methods))
        : {},
      payment_error: null,
    };
  },


  watch: {

    'form.country'() {
      this.payment_methods_reload();
    },

  },


  computed: {

    is_paypal_hidden() {
      return !!js_data.product.is_paypal_hidden || this.form.installments !== 1;
    },

    paypal_payment_method() {
      return this.payment_methods.instant_transfer || null;
    },

    is_apm_visible() {
      return Object.keys(this.payment_methods).reduce((acc, name) => {
        return acc || this.payment_methods[name].is_apm;
      }, false);
    },

    extra_fields() {
      const payment_method = Object.keys(this.payment_methods)
        .filter(name => name !== 'instant_transfer')
        .shift();

      return this.payment_methods[payment_method]
        ? this.payment_methods[payment_method].extra_fields || {}
        : {};
    },

  },


  methods: {

    payment_methods_reload() {
      return this.fetch_get('/payment-methods-by-country?country=' + this.form.country)
        .then(this.fetch_json)
        .then(body => {
          this.payment_methods = body;
        })
        .catch(err => {

        });
    },

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
