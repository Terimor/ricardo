export default {

  data() {
    return {
      form: {
        district: null,
      },
    };
  },


  validations() {
    return this.extra_fields.district
      ? {
          district: {
            required(value) {
              if (this.extra_fields.district.pattern && !new RegExp(this.extra_fields.district.pattern).test('')) {
                return !!value;
              }

              return true;
            },
            valid(value) {
              if (this.extra_fields.district.pattern) {
                return new RegExp(this.extra_fields.district.pattern).test(value);
              }

              return true;
            },
          },
        }
      : null;
  },

};
