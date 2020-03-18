import { required } from 'vuelidate/lib/validators';


export default {

  data() {
    return {
      form: {
        installments: 1,
      },
    };
  },


  created() {
    this.installments_init();
  },


  validations() {
    return this.extra_fields.installments && this.form.payment_provider === 'credit-card'
      ? {
          installments: {
            required,
          },
        }
      : null;
  },


  watch: {

    extra_fields() {
      this.installments_init();
    },

    'form.card_type'() {
      if (!this.installments_visible) {
        this.installments_init();
      }
    },

  },


  computed: {

    installments_visible() {
      const values = {
        card_type: this.form.card_type || null, 
      };

      if (!this.extra_fields.installments) {
        return false;
      }

      const visibility = this.extra_fields.installments
        ? this.extra_fields.installments.visibility || {}
        : {};

      return Object.keys(visibility)
        .reduce((visible, prop_name) => {
          return visibility[prop_name].indexOf(values[prop_name]) !== -1;
        }, true);
    },

    installments_items() {
      return this.extra_fields.installments
        ? this.extra_fields.installments.items.map(item => ({
            label: this.t(item.phrase),
            value: item.value,
          }))
        : [];
    },

  },


  methods: {

    installments_init() {
      if (this.extra_fields.installments && this.extra_fields.installments.default) {
        this.form.installments = this.extra_fields.installments.default || 1;
      }
    },

  },

};
