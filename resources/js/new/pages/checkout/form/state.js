import { required } from 'vuelidate/lib/validators';


export default {

  data() {
    return {
      form: {
        state: (js_data.customer && js_data.customer.address && js_data.customer.address.state) || null,
      },
    };
  },


  created() {
    this.state_init();
  },


  validations() {
    return this.extra_fields.state
      ? {
          state: {
            required(value) {
              if (this.extra_fields.state.pattern && !new RegExp(this.extra_fields.state.pattern).test('')) {
                return !!value;
              }

              return true;
            },
            valid(value) {
              if (this.extra_fields.state.pattern) {
                return new RegExp(this.extra_fields.state.pattern).test(value);
              }

              return true;
            },
          },
        }
      : null;
  },


  watch: {

    extra_fields() {
      this.state_init();
    },

  },


  computed: {

    state_label() {
      return this.t('checkout.payment_form.state', {}, {
        country: this.form.country,
      });
    },

    state_items() {
      return this.extra_fields.state
        ? this.extra_fields.state.items
        : [];
    },

  },


  methods: {

    state_init() {
      if (this.extra_fields.state && this.extra_fields.state.default) {
        this.form.state = this.extra_fields.state.default || null;
      }
    },

  },

};
