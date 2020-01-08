import { required } from 'vuelidate/lib/validators';


export default {

  data() {
    return {
      form: {
        document_number: null,
      },
    };
  },


  validations() {
    return this.extra_fields.document_number
      ? {
          document_number: {
            required(value) {
              if (this.extra_fields.document_number.pattern) {
                if (typeof this.extra_fields.document_number.pattern === 'object') {
                  if (this.extra_fields.document_number.pattern[this.form.document_type] && !new RegExp(this.extra_fields.document_number.pattern[this.form.document_type]).test('')) {
                    return !!value;
                  }
                } else if (!new RegExp(this.extra_fields.document_number.pattern).test('')) {
                  return !!value;
                }
              }

              return true;
            },
            valid(value) {
              if (this.extra_fields.document_number.pattern) {
                if (typeof this.extra_fields.document_number.pattern === 'object') {
                  if (this.extra_fields.document_number.pattern[this.form.document_type]) {
                    return new RegExp(this.extra_fields.document_number.pattern[this.form.document_type]).test(value);
                  }
                } else {
                  return new RegExp(this.extra_fields.document_number.pattern).test(value);
                }
              }

              return true;
            },
          },
        }
      : null;
  },


  computed: {

    document_number_mask() {
      const mask = typeof this.extra_fields.document_number.placeholder === 'object'
        ? this.extra_fields.document_number.placeholder[this.form.document_type] || ''
        : this.extra_fields.document_number.placeholder;

      return this.input_cut_mask(mask, this.form.document_number);
    },

  },


  methods: {

    document_number_input() {
      const mask = typeof this.extra_fields.document_number.placeholder === 'object'
        ? this.extra_fields.document_number.placeholder[this.form.document_type] || ''
        : this.extra_fields.document_number.placeholder;

      const schema = !Array.isArray(this.extra_fields.document_number.schema)
        ? this.extra_fields.document_number.schema[this.form.document_type] || []
        : this.extra_fields.document_number.schema;

      this.form.document_number = this.input_apply_mask(mask, schema, this.form.document_number);
    },

  },

};
