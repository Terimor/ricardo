export default {

  computed: {

    summary_price_text() {
      let price_text = this.form.installments !== 1
        ? this.form.installments + '× '
        : '';

      switch (this.form.installments) {
        case 1:
          price_text += js_data.product.prices[this.form.deal].value_text;
          break;
        case 3:
          price_text += js_data.product.prices[this.form.deal].installments3_value_text;
          break;
        case 6:
          price_text += js_data.product.prices[this.form.deal].installments6_value_text;
          break;
      }

      return price_text;
    },

    summary_total() {
      let price_text = this.form.installments !== 1
        ? this.form.installments + '× '
        : '';

      let price = 0;
      let warranty = 0;

      switch (this.form.installments) {
        case 1:
          price = js_data.product.prices[this.form.deal].value_text;
          warranty = js_data.product.prices[this.form.deal].warranty_price_text;
          break;
        case 3:
          price = js_data.product.prices[this.form.deal].installments3_value_text;
          warranty = js_data.product.prices[this.form.deal].installments3_warranty_price_text;
          break;
        case 6:
          price = js_data.product.prices[this.form.deal].installments6_value_text;
          warranty = js_data.product.prices[this.form.deal].installments6_warranty_price_text;
          break;
      }

      price = +price.replace(/,/g, '.').replace(/[^0-9.]/g, '');
      warranty = +warranty.replace(/,/g, '.').replace(/[^0-9.]/g, '');

      if (this.form.warranty) {
        price += warranty;
      }

      price = Math.round(price * 100) / 100;
      price = price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');

      const value_text = js_data.product.prices[this.form.deal].value_text;
      const match = value_text.match(/[0-9.,\s]+/).shift().trim();

      return price_text + value_text.replace(match, price);
    },

  },

};
