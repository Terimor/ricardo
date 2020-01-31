export default {

  computed: {

    price_exchange_rate() {
      return js_data.product.prices.exchange_rate || 1;
    },

    price_multiplier() {
      return this.form.installments !== 1
        ? this.form.installments + '× '
        : '';
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

      let total = this.price_total_value.toString();
      total = total.replace(/\B(?=(\d{3})+(?!\d))/g, ' ');

      if (this.form.deal) {
        result = js_data.product.prices[this.form.deal].value_text;
        const match = result.match(/[0-9.,\s]+/).shift().trim();

        result = result.replace(match, total);
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

    price_value() {
      return +this.price_text.replace(/,/g, '.').replace(/[^0-9.]/g, '') || 0;
    },

    price_warranty_value() {
      return +this.price_warranty_text.replace(/,/g, '.').replace(/[^0-9.]/g, '') || 0;
    },

    price_total_value() {
      return Math.round((this.price_value + (this.form.warranty ? this.price_warranty_value : 0)) * 100) / 100;
    },

    price_total_value_usd() {
      return Math.round(((this.price_value + (this.form.warranty ? this.price_warranty_value : 0)) / this.price_exchange_rate) * 100) / 100;
    },

  },

};
