export default {

  methods: {

    credit_card_create_order() {
      let fingerprint_result = null;
      let ipqualityscore_result = null;

      if (this.is_submitted) {
        return;
      }

      this.$v.form.$touch();

      if (this.$v.form.$pending || this.$v.form.$error) {
        return this.scroll_to_error();
      }
      
      this.payment_provider = 'credit-card';
      this.payment_error = null;
      this.is_submitted = true;

      if (this.email_validation && this.email_validation.block) {
        return setTimeout(() => {
          this.payment_error = this.t('checkout.abuse_error');
          this.is_submitted = false;
        }, 1000);
      }

      localStorage.setItem('3ds_params', JSON.stringify(js_query_params));
      localStorage.setItem('selectedProductData', JSON.stringify({
        ...this.form,
        card_holder: undefined,
        card_number: undefined,
        card_type: undefined,
        card_date: undefined,
        card_cvv: undefined,
      }));

      const phone = this.form.phone.replace(/[^0-9]/g, '');
      const card_number = this.form.card_number.replace(/[^0-9]/g, '');

      return Promise.resolve()
        .then(() => {
          return this.fingerprint_calculate();
        })
        .then(result => {
          fingerprint_result = result;
        })
        .then(() => {
          let fields = {
            order_quantity: this.form.deal,
            order_amount: this.get_local_order_amount(),
            billing_first_name: this.form.first_name,
            billing_last_name: this.form.last_name,
            billing_country: this.form.country,
            billing_address_1: this.form.street,
            billing_city: this.form.city,
            billing_region: this.extra_fields.state
              ? this.form.state
              : '',
            billing_postcode: this.form.zipcode,
            billing_email: this.form.email,
            billing_phone: this.form.phone_code + phone,
            credit_card_bin: card_number.substr(0, 6),
            credit_card_expiration_month: this.form.card_date.split('/')[0],
            credit_card_expiration_year: this.form.card_date.split('/')[1],
            cvv_code: this.form.card_cvv,
          };

          if (window.sha256) {
            fields.credit_card_hash = sha256(card_number);
          }

          return this.ipqualityscore_calculate(fields);
        })
        .then(result => {
          ipqualityscore_result = result;
        })
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
                number: phone,
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
              number: card_number,
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

          let url = '/pay-by-card';

          url += '?cur=' + (!js_query_params.cur || js_query_params.cur === '{aff_currency}'
            ? js_data.product.prices.currency
            : js_query_params.cur);

          if (localStorage.getItem('order_failed')) {
            url += '&order=' + localStorage.getItem('odin_order_id');
          }

          return this.fetch_post(url, data);
        })
        .then(resp => {
          if (!resp.ok) {
            throw new Error(resp.statusText);
          }

          return resp.json();
        })
        .then(body => {
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
              this.goto_thankyou_page(body.order_id, body.order_currency);
            }
          }
        })
        .catch(() => {
          this.payment_error = this.t('checkout.payment_error');
          this.is_submitted = false;
        });
    },

  },

};
