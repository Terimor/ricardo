import { required } from 'vuelidate/lib/validators';


export default {

  data() {
    return {
      form: {
        document_type: null,
      },
    };
  },


  created() {
    this.document_type_init();
  },


  validations() {
    return this.extra_fields.document_type && this.form.payment_provider === 'credit-card'
      ? {
          document_type: {
            required,
          },
        }
      : null;
  },


  watch: {

    extra_fields() {
      this.document_type_init();
    },

  },


  computed: {

    document_type_items() {
      return this.extra_fields.document_type
        ? this.extra_fields.document_type.items.map(item => ({
            label: this.t(item.phrase),
            value: item.value,
          }))
        : [];
    },

  },


  methods: {

    document_type_init() {
      if (this.extra_fields.document_type && this.extra_fields.document_type.default) {
        this.form.document_type = this.extra_fields.document_type.default || null;
      }
    },

  },

};
