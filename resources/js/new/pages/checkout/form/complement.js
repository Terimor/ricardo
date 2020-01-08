export default {

  data() {
    return {
      form: {
        complement: null,
      },
    };
  },


  validations() {
    return this.extra_fields.complement
      ? {
          complement: {
            required(value) {
              if (this.extra_fields.complement.pattern && !new RegExp(this.extra_fields.complement.pattern).test('')) {
                return !!value;
              }

              return true;
            },
            valid(value) {
              if (this.extra_fields.complement.pattern) {
                return new RegExp(this.extra_fields.complement.pattern).test(value);
              }

              return true;
            },
          },
        }
      : null;
  },

};
