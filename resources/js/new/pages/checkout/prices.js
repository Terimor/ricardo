export default {

  computed: {

    price_exchange_rate() {
      return js_data.product.prices.exchange_rate || 1;
    },

    price_currency() {
      return !js_query_params.cur || js_query_params.cur === '{aff_currency}'
        ? js_data.product.prices.currency
        : js_query_params.cur;
    },

    price_multiplier() {
      return this.form.installments !== 1
        ? this.form.installments + '× '
        : '';
    },

    price_value() {
      return this.form.deal
        ? js_data.product.prices[this.form.deal].value
        : 0;
    },

    price_warranty_value() {
      return this.form.deal
        ? js_data.product.prices[this.form.deal].warranty_price
        : 0;
    },

    price_total_value() {
      return this.form.deal
        ? js_data.product.prices[this.form.deal].total_amount
        : 0;
    },

    price_total_value_usd() {
      return Math.round((this.price_total_value / this.price_exchange_rate) * 100) / 100;
    },

    price_text() {
      let result = '';

      if (this.form.deal) {
        switch (this.form.installments) {
          case 1:
            result = js_data.product.prices[this.form.deal].value_text;
            break;
          case 3:
            result = js_data.product.prices[this.form.deal].installments3_value_text;
            break;
          case 6:
            result = js_data.product.prices[this.form.deal].installments6_value_text;
            break;
        }
      }

      return result;
    },

    price_warranty_text() {
      let result = '';

      if (this.form.deal) {
        switch (this.form.installments) {
          case 1:
            result = js_data.product.prices[this.form.deal].warranty_price_text;
            break;
          case 3:
            result = js_data.product.prices[this.form.deal].installments3_warranty_price_text;
            break;
          case 6:
            result = js_data.product.prices[this.form.deal].installments6_warranty_price_text;
            break;
        }
      }

      return result;
    },

    price_total_text() {
      let result = '';

      if (this.form.deal) {
        switch (this.form.installments) {
          case 1:
            result = this.form.warranty
              ? js_data.product.prices[this.form.deal].total_amount_text
              : js_data.product.prices[this.form.deal].value_text;
            break;
          case 3:
            result = this.form.warranty
              ? js_data.product.prices[this.form.deal].installments3_total_amount_text
              : js_data.product.prices[this.form.deal].installments3_value_text;
            break;
          case 6:
            result = this.form.warranty
              ? js_data.product.prices[this.form.deal].installments6_total_amount_text
              : js_data.product.prices[this.form.deal].installments6_value_text;
            break;
        }
      }

      return result;
    },

    xprice_text() {
      return this.price_multiplier + this.price_text;
    },

    xprice_warranty_text() {
      return this.price_multiplier + this.price_warranty_text;
    },

    xprice_total_text() {
      return this.price_multiplier + this.price_total_text;
    },

  },

};
