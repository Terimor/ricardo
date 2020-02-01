export default {

  methods: {

    paypal_create_order() {
      this.form.payment_provider = 'paypal';

      let fingerprint_result = null;
      let ipqualityscore_result = null;

      if (!this.form.deal) {
        return;
      }
      
      this.payment_error = null;
      this.is_submitted = true;

      localStorage.setItem('selectedProductData', JSON.stringify(this.form));

      return Promise.resolve()
        .then(() => {
          return this.fingerprint_calculate();
        })
        .then(result => {
          fingerprint_result = result;
        })
        .then(() => {
          return this.ipqualityscore_form_calculate();
        })
        .then(result => {
          ipqualityscore_result = result;

          if (result && result.recent_abuse) {
            setTimeout(() => {
              this.payment_error = this.t('checkout.abuse_error');
            }, 1000);

            return new Promise(resolve => {});
          }
        })
        .then(() => {
          let data = {
            sku_code: this.form.variant,
            sku_quantity: this.form.deal,
            is_warranty_checked: this.form.warranty,
            order: '',
            page_checkout: location.href,
            cur: !js_query_params.cur || js_query_params.cur === '{aff_currency}'
              ? js_data.product.prices.currency
              : js_query_params.cur,
            offer: js_query_params.offer || null,
            affiliate: js_query_params.affiliate || null,
            ipqs: ipqualityscore_result,
            f: fingerprint_result,
          };

          return this.fetch_post('/paypal-create-order', data);
        })
        .then(this.fetch_json)
        .then(body => {
          if (body.error) {
            if (body.error.code === 10008) {
              this.payment_error = this.t(body.error.message.phrase, body.error.message.args);
            }
          } else if (body.odin_order_id) {
            localStorage.setItem('odin_order_id', body.odin_order_id);
            localStorage.setItem('order_currency', body.order_currency);
            localStorage.setItem('order_number', body.order_number);
            localStorage.setItem('order_id', body.id);
          }

          return body.id || null;
        })
        .catch(() => {
          this.payment_error = this.t('checkout.payment_error');
          this.is_submitted = false;
        });
    },

    paypal_verify_order(order_id) {
      return Promise.resolve()
        .then(() => {
          let data = {
            orderID: order_id,
          };

          return this.fetch_post('/paypal-verify-order', data);
        })
        .then(this.fetch_json)
        .then(res => {
          const odin_order_id = localStorage.getItem('odin_order_id');
          const order_currency = localStorage.getItem('order_currency');

          this.goto_upsells(odin_order_id, order_currency);
        })
        .catch(err => {
          this.payment_error = this.t('checkout.payment_error');
          this.is_submitted = false;
        });
    },

  },

};
