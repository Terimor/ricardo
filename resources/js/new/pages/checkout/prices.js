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
      return this.form.deal
        ? this.price_perdeal_text[this.form.deal]
        : '';
    },

    price_perdeal_text() {
      return Object.keys(js_data.product.prices)
        .filter(quantity => !!js_data.product.prices[quantity].value)
        .reduce((acc, quantity) => {
          acc[quantity] = this.form.installments === 6
            ? js_data.product.prices[quantity].installments6_value_text
            : this.form.installments === 3
              ? js_data.product.prices[quantity].installments3_value_text
              : js_data.product.prices[quantity].value_text;
          return acc;
        }, {});
    },

    price_perdeal_old_text() {
      return Object.keys(js_data.product.prices)
        .filter(quantity => !!js_data.product.prices[quantity].value)
        .reduce((acc, quantity) => {
          acc[quantity] = this.form.installments === 6
            ? js_data.product.prices[quantity].installments6_old_value_text
            : this.form.installments === 3
              ? js_data.product.prices[quantity].installments3_old_value_text
              : js_data.product.prices[quantity].old_value_text;
          return acc;
        }, {});
    },

    price_perdeal_unit_text() {
      return Object.keys(js_data.product.prices)
        .filter(quantity => !!js_data.product.prices[quantity].value)
        .reduce((acc, quantity) => {
          acc[quantity] = this.form.installments === 6
            ? js_data.product.prices[quantity].installments6_unit_value_text
            : this.form.installments === 3
              ? js_data.product.prices[quantity].installments3_unit_value_text
              : js_data.product.prices[quantity].unit_value_text;
          return acc;
        }, {});
    },

    price_warranty_text() {
      return this.form.deal
        ? this.price_perdeal_warranty_text[this.form.deal]
        : '';
    },

    price_perdeal_warranty_text() {
      return Object.keys(js_data.product.prices)
        .filter(quantity => !!js_data.product.prices[quantity].value)
        .reduce((acc, quantity) => {
          acc[quantity] = this.form.installments === 6
            ? js_data.product.prices[quantity].installments6_warranty_price_text
            : this.form.installments === 3
              ? js_data.product.prices[quantity].installments3_warranty_price_text
              : js_data.product.prices[quantity].warranty_price_text;
          return acc;
        }, {});
    },

    price_total_text() {
      return this.form.deal
        ? this.price_perdeal_total_text[this.form.deal]
        : '';
    },

    price_perdeal_total_text() {
      return Object.keys(js_data.product.prices)
        .filter(quantity => !!js_data.product.prices[quantity].value)
        .reduce((acc, quantity) => {
          acc[quantity] = this.form.installments === 6
            ? this.form.warranty
              ? js_data.product.prices[quantity].installments6_total_amount_text
              : js_data.product.prices[quantity].installments6_value_text
            : this.form.installments === 3
              ? this.form.warranty
                ? js_data.product.prices[quantity].installments3_total_amount_text
                : js_data.product.prices[quantity].installments3_value_text
              : this.form.warranty
                ? js_data.product.prices[quantity].total_amount_text
                : js_data.product.prices[quantity].value_text;
          return acc;
        }, {});
    },

    xprice_text() {
      return this.price_multiplier + this.price_text;
    },

    xprice_perdeal_text() {
      return Object.keys(this.price_perdeal_text).reduce((acc, quantity) => {
        acc[quantity] = this.price_multiplier + this.price_perdeal_text[quantity];
        return acc;
      }, {});
    },

    xprice_perdeal_old_text() {
      return Object.keys(this.price_perdeal_old_text).reduce((acc, quantity) => {
        acc[quantity] = this.price_multiplier + this.price_perdeal_old_text[quantity];
        return acc;
      }, {});
    },

    xprice_perdeal_unit_text() {
      return Object.keys(this.price_perdeal_unit_text).reduce((acc, quantity) => {
        acc[quantity] = this.price_multiplier + this.price_perdeal_unit_text[quantity];
        return acc;
      }, {});
    },

    xprice_warranty_text() {
      return this.price_multiplier + this.price_warranty_text;
    },

    xprice_total_text() {
      return this.price_multiplier + this.price_total_text;
    },

  },

};
