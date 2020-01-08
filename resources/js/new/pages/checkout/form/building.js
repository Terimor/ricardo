export default {

  data() {
    return {
      form: {
        building: null,
      },
    };
  },


  validations() {
    return this.extra_fields.building
      ? {
          building: {
            required(value) {
              if (this.extra_fields.building.pattern && !new RegExp(this.extra_fields.building.pattern).test('')) {
                return !!value;
              }

              return true;
            },
            valid(value) {
              if (this.extra_fields.building.pattern) {
                return new RegExp(this.extra_fields.building.pattern).test(value);
              }

              return true;
            },
          },
        }
      : null;
  },

};
