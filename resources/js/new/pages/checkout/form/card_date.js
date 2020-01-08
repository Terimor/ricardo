import { required } from 'vuelidate/lib/validators';
import * as dateFns from 'date-fns';


export default {

  data() {
    return {
      form: {
        card_date: null,
      },
    };
  },


  validations() {
    return {
      card_date: {
        required,
        valid(value) {
          value = value || '';

          if (!/^[0-9]{2}\/[0-9]{2}$/.test(value)) {
            return false;
          }

          const month = +value.split('/')[0];
          const year = +value.split('/')[1];

          return month >= 1 && month <= 12 && year >= 0 && year <= 99;
        },
        not_expired(value) {
          value = value || '';

          const month = (+value.split('/')[0] - 1) || 0;
          const year = 2000 + (+value.split('/')[1] || 0);

          return dateFns.isFuture(new Date(year, month));
        },
      },
    };
  },


  computed: {

    card_date_mask() {
      return this.input_cut_mask('MM/YY', this.form.card_date);
    },

  },


  methods: {

    card_date_input() {
      this.form.card_date = this.input_apply_mask('xx/xx', ['\\d', '\\d', '/', '\\d', '\\d'], this.form.card_date);
    },

  },

};
