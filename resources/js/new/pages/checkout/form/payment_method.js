import { required } from 'vuelidate/lib/validators';
import credit_card_type from 'credit-card-type';


const ebanx_map = {
  'visa': 'visa',
  'mastercard': 'mastercard',
  'american-express': 'amex',
  'diners-club': 'dinersclub',
  'discover': 'discover',
  'jcb': 'jcb',
  'unionpay': null,
  'maestro': null,
  'mir': null,
  'elo': 'elo',
  'hiper': null,
  'hipercard': 'hipercard',
};


export default {

  data() {
    return {
      form: {
        payment_method: null,
      },
    };
  },


  validations() {
    return {
      payment_method: {

      },
    };
  },


  watch: {

    'form.card_number'(value) {
      value = value || '';
      value = value.replace(/[^0-9]/g, '');

      const credit_card_type_list = credit_card_type(value);

      const lib_payment_method = value.length > 0 && credit_card_type_list.length > 0
        ? credit_card_type_list[0].type
        : null;

      const payment_method = lib_payment_method && ebanx_map[lib_payment_method]
        ? ebanx_map[lib_payment_method]
        : null;

      this.form.payment_method = this.payment_methods && this.payment_methods[payment_method]
        ? payment_method
        : null;
    },

  },

};
