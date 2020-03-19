import { required } from 'vuelidate/lib/validators';
import credit_card_type from 'credit-card-type';


export default {

  data() {
    return {
      form: {
        card_number: null,
      },
    };
  },


  validations() {
    return this.form.payment_provider === 'credit-card'
      ? {
          card_number: {
            required,
            valid(value) {
              value = value || '';
              value = value.replace(/[^0-9]/g, '');

              const credit_card_type_list = credit_card_type(value);
              const common_rule = value.length > 12 && value.length <= 19;

              return credit_card_type_list.length > 0
                ? credit_card_type_list[0].lengths.indexOf(value.length) !== -1 || common_rule
                : false;
            },
          },
        }
      : null;
  },


  computed: {

    card_number_prefix_url() {
      return this.payment_methods && this.form.payment_method && this.payment_methods[this.form.payment_method]
        ? this.payment_methods[this.form.payment_method].logo
        : null;
    },

  },


  methods: {

    card_number_input() {
      let value = this.form.card_number || '';

      while (value.replace(/[^0-9]/g, '').length > 19) {
        value = value.substr(0, value.length - 1);
      }

      this.form.card_number = value;
    },

  },

};
