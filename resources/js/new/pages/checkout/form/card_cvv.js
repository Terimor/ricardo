import { required, numeric, minLength, maxLength } from 'vuelidate/lib/validators';


export default {

  data() {
    return {
      form: {
        card_cvv: null,
      },
      card_cvv_dialog_visible: false,
    };
  },


  validations() {
    return this.form.payment_provider === 'credit-card'
      ? {
          card_cvv: {
            required,
            numeric,
            min_length: minLength(3),
            max_length: maxLength(4),
          },
        }
      : null;
  },


  methods: {

    card_cvv_suffix_open() {
      this.card_cvv_dialog_visible = true;
    },

    card_cvv_dialog_close() {
      this.card_cvv_dialog_visible = false;
    },

    card_cvv_input() {
      let value = this.form.card_cvv || '';

      value = value.replace(/[^0-9]/g, '');
      value = value.substr(0, 4);

      this.form.card_cvv = value;
    },

  },

};
