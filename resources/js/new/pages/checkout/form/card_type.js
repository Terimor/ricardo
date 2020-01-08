import { required } from 'vuelidate/lib/validators';


export default {

  data() {
    return {
      form: {
        card_type: null,
      },
    };
  },


  created() {
    this.card_type_init();
  },


  validations() {
    return this.extra_fields.card_type
      ? {
          card_type: {
            required,
          },
        }
      : null;
  },


  watch: {

    extra_fields() {
      this.card_type_init();
    },

  },


  computed: {

    card_type_items() {
      return this.extra_fields.card_type
        ? this.extra_fields.card_type.items.map(item => ({
            label: this.t(item.phrase),
            value: item.value,
          }))
        : [];
    },

  },


  methods: {

    card_type_init() {
      if (this.extra_fields.card_type && this.extra_fields.card_type.default) {
        this.form.card_type = this.extra_fields.card_type.default;
      }
    },

  },

};
