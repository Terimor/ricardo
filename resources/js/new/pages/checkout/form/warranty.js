import { required } from 'vuelidate/lib/validators';


export default {

  data() {
    return {
      form: {
        warranty: false,
      },
    };
  },


  validations() {
    return {
      warranty: {
        required,
      },
    };
  },


  computed: {

    warranty_price_text() {
      let price_text = this.form.installments !== 1
        ? this.form.installments + '× '
        : '';

      switch (this.form.installments) {
        case 1:
          price_text += js_data.product.prices[this.form.deal].warranty_price_text;
          break;
        case 3:
          price_text += js_data.product.prices[this.form.deal].installments3_warranty_price_text;
          break;
        case 6:
          price_text += js_data.product.prices[this.form.deal].installments6_warranty_price_text;
          break;
      }

      return price_text;
    },

  },


  methods: {
 
    warranty_change(value) {
      this.form.warranty = value;
    },

  },

};
