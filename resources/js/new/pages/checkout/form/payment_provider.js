import { required } from 'vuelidate/lib/validators';


export default {

  data() {
    return {
      form: {
        payment_provider: null,
      },
    };
  },


  created() {
    this.payment_provider_init();
  },


  validations() {
    return {
      payment_provider: {
        required,
      },
    };
  },


  watch: {
    'form.payment_provider'(value) {
      window.selectedPayment = value;
      history.pushState({}, '', '');
    },
  },


  methods: {

    payment_provider_init() {
      if (this.is_paypal_hidden) {
        this.form.payment_provider = 'credit-card';
      }
    },

    payment_provider_change(value) {
      this.form.payment_provider = value;
    },

  },

};
