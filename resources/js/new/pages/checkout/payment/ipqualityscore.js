let cache = null;


window.IPQ = {

  Callback() {
    Startup.AfterResult(result => {
      IPQ.success(result);
    });

    Startup.AfterFailure(result => {
      IPQ.failure(result);
    });

    IPQ.send_request();
  },

};


export default {

  methods: {

    ipqualityscore_calculate() {
      let attempt = 0;

      let fields = {
        order_quantity: this.form.deal,
        order_amount: this.price_total_value_usd,
      };

      if (this.form.first_name) {
        fields.billing_first_name = this.form.first_name;
      }

      if (this.form.last_name) {
        fields.billing_last_name = this.form.last_name;
      }

      if (this.form.country) {
        fields.billing_country = this.form.country;
      }

      if (this.form.street) {
        fields.billing_address_1 = this.form.street;
      }

      if (this.form.city) {
        fields.billing_city = this.form.city;
      }

      if (this.extra_fields.state) {
        fields.billing_region = this.form.state;
      }

      if (this.form.zipcode) {
        fields.billing_postcode = this.form.zipcode;
      }

      if (this.form.email) {
        fields.billing_email = this.form.email;
      }

      if (this.form.phone) {
        fields.billing_phone = this.form.phone_code + this.form.phone.replace(/[^0-9]/g, '');
      }

      if (this.form.card_number) {
        fields.credit_card_bin = this.form.card_number.replace(/[^0-9]/g, '').substr(0, 6);

        if (window.sha256) {
          fields.credit_card_hash = sha256(this.form.card_number.replace(/[^0-9]/g, ''));
        }
      }

      if (this.form.card_date) {
        fields.credit_card_expiration_month = this.form.card_date.split('/')[0];
        fields.credit_card_expiration_year = this.form.card_date.split('/')[1];
      }

      if (this.form.card_cvv) {
        fields.cvv_code = this.form.card_cvv;
      }

      return new Promise(resolve => {
        if (cache) {
          return resolve(cache);
        }

        IPQ.send_request = () => {
          attempt++;

          for (let key of Object.keys(js_query_params)) {
            Startup.Store(key, js_query_params[key]);
          }

          for (let key of Object.keys(fields)) {
            Startup.FieldStore(key, fields[key]);
          }

          Startup.Init();
        };

        IPQ.success = result => {
          localStorage.setItem('3ds_ipqs', JSON.stringify(result));
          cache = result;
          resolve(result);
        };

        IPQ.failure = result => {
          resolve(null);
        };

        if (js_query_params['3ds'] === 'failure') {
          let result = null;

          try {
            result = JSON.parse(localStorage.getItem('3ds_ipqs'));
          }
          catch (err) {

          }

          if (result) {
            cache = result;
            return resolve(result);
          }
        }

        if (!window.Startup) {
          js_deps.add_script('ipqualityscore', 'https://www.clkscore.com/api/*/' + js_data.ipqualityscore_api_hash + '/learn.js');
        } else {
          IPQ.send_request();
        }
      });
    },

  },

};
