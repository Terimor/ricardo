export default {

  methods: {

    credit_card_create_order() {
      this.form.payment_provider = 'credit-card';

      let fingerprint_result = null;
      let ipqualityscore_result = null;

      if (this.is_submitted) {
        return;
      }

      this.$v.form.$touch();

      if (this.$v.form.$invalid || this.$v.form.$pending) {
        return setTimeout(() => this.scroll_to_error(), 100);
      }
      
      this.payment_error = null;
      this.is_submitted = true;

      if (this.email_validation && this.email_validation.block) {
        return setTimeout(() => {
          this.payment_error = this.t('checkout.abuse_error');
          this.is_submitted = false;
        }, 1000);
      }

      localStorage.setItem('3ds_params', JSON.stringify(js_query_params));

      localStorage.setItem('3ds_form', JSON.stringify({
        ...this.form,
        card_holder: undefined,
        card_number: undefined,
        card_type: undefined,
        card_date: undefined,
        card_cvv: undefined,
      }));

      return Promise.resolve()
        .then(() => {
          return this.fingerprint_calculate();
        })
        .then(result => {
          fingerprint_result = result;
        })
        .then(() => {
          return this.ipqualityscore_calculate();
        })
        .then(result => {
          ipqualityscore_result = result;
        })
        .then(() => {
          return this.credit_card_pay_by_card(fingerprint_result, ipqualityscore_result);
        })
        .then(body => {
          if (!body.bs_pf_token) {
            return body;
          }

          return this.credit_card_pay_by_bluesnap(body.order_id, body.bs_pf_token, body.order_currency, body.order_amount);
        })
        .then(body => {
          this.credit_card_parse_result(body);
        })
        .catch(err => {
          this.payment_error = this.t('checkout.payment_error');
          this.is_submitted = false;
        });
    },

    credit_card_create_order_3ds_bluesnap() {
      this.form.payment_provider = 'credit-card';

      this.payment_error = null;
      this.is_submitted = true;

      return Promise.resolve()
        .then(body => {
          const order_id = js_query_params.order;
          const bs_pf_token = js_query_params.bs_pf_token;
          const order_currency = js_query_params.cur;
          const order_amount = +js_query_params.amount;

          return this.credit_card_pay_by_bluesnap(order_id, bs_pf_token, order_currency, order_amount);
        })
        .then(body => {
          this.credit_card_parse_result(body);
        })
        .catch(err => {
          this.payment_error = this.t('checkout.payment_error');
          this.is_submitted = false;
        });
    },

    credit_card_get_3ds_errors() {
      Promise.resolve()
        .then(() => {
          const order_id = localStorage.getItem('odin_order_id');
          return this.fetch_get('/pay-by-card-errors?order=' + order_id);
        })
        .then(this.fetch_json)
        .then(body => {
          this.payment_error = this.t('checkout.payment_error');

          if (body.errors && body.errors.length > 0) {
            this.payment_error = this.t(body.errors[0]);
          }
        })
        .catch(err => {
          this.payment_error = this.t('checkout.payment_error');
        });
    },

    credit_card_3ds_form_resrote() {
      try {
        this.form = JSON.parse(localStorage.getItem('3ds_form'));
      }
      catch (err) {

      }
    },

    credit_card_pay_by_card(fingerprint_result, ipqualityscore_result) {
      return Promise.resolve()
        .then(() => {
          let data = {
            page_checkout: location.href,
            product: {
              qty: this.form.deal,
              sku: this.form.variant,
              is_warranty_checked: this.form.warranty,
            },
            contact: {
              phone: {
                country_code: this.form.phone_code,
                number: this.form.phone.replace(/[^0-9]/g, ''),
              },
              first_name: this.form.first_name,
              last_name: this.form.last_name,
              email: this.form.email,
            },
            address: {
              zip: this.form.zipcode,
              street: this.form.street,
              city: this.form.city,
              country: this.form.country,
            },
            card: {
              number: this.form.card_number.replace(/[^0-9]/g, ''),
              cvv: this.form.card_cvv,
              month: this.form.card_date.split('/')[0],
              year: '20' + this.form.card_date.split('/')[1],
            },
            ipqs: ipqualityscore_result,
            f: fingerprint_result,
          };

          if (this.is_affid_empty) {
            data.card.holder = this.form.card_holder;
          }

          if (this.extra_fields.installments) {
            data.card.installments = this.form.installments;
          }

          if (this.extra_fields.state) {
            data.address.state = this.form.state;
          }

          if (this.extra_fields.building) {
            data.address.building = this.form.building;
          }

          if (this.extra_fields.complement) {
            data.address.complement = this.form.complement;
          }

          if (this.extra_fields.district) {
            data.address.district = this.form.district;
          }

          if (this.extra_fields.card_type) {
            data.card.type = this.form.card_type;
          }

          if (this.extra_fields.document_type) {
            data.contact.document_type = this.form.document_type;
          }

          if (this.extra_fields.document_number) {
            data.contact.document_number = this.form.document_number;
          }

          if (window.kount_params) {
            data.kount_session_id = kount_params.MercSessId;
          }

          let url = '/pay-by-card';
          url += '?cur=' + this.price_currency;

          if (localStorage.getItem('order_failed')) {
            url += '&order=' + localStorage.getItem('order_failed');
          }

          return this.fetch_post(url, data);
        })
        .then(this.fetch_json);
    },

    credit_card_pay_by_bluesnap(order_id, bs_pf_token, order_currency, order_amount) {
      return this.bluesnap_create_order(bs_pf_token, order_currency, order_amount)
        .then(bs_3ds_ref => {
          return this.fetch_post('/pay-by-card-bs-3ds', {
            '3ds_ref': bs_3ds_ref,
            order_id: order_id,
          });
        })
        .then(this.fetch_json);
    },

    credit_card_parse_result(body) {
      if (body.order_id) {
        localStorage.setItem('odin_order_id', body.order_id);
        localStorage.setItem('order_currency', body.order_currency);
        localStorage.setItem('order_number', body.order_number);
        localStorage.setItem('order_id', body.id);

        if (body.status === 'ok') {
          localStorage.removeItem('order_failed');
        } else {
          localStorage.setItem('order_failed', body.order_id);
        }
      }

      if (body.status !== 'ok') {
        this.payment_error = this.t('checkout.payment_error');

        if (body.errors) {
          if (Array.isArray(body.errors)) {
            if (body.errors.length > 0) {
              this.payment_error = this.t(body.errors[0]);
            }
          } else {
            if (Object.keys(body.errors).length > 0) {
              this.payment_error = body.message || Object.values(body.errors)[0][0];
            }
          }
        }

        if (body.error) {
          if (body.error.phrase) {
            this.payment_error = this.t(body.error.phrase);
          } else if (body.error.message) {
            this.payment_error = body.error.message;
          }
        }

        this.is_submitted = false;
      }

      if (body.status === 'ok') {
        if (body.redirect_url) {
          location.href = body.redirect_url;
        } else {
          this.goto_upsells(body.order_id, body.order_currency);
        }
      }
    },

  },

};
