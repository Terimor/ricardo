export default {

  methods: {

    step2_submit() {
      const fields = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'street',
        'district',
        'city',
        'state',
        'zipcode',
        'country',
      ];

      if (!this.form_check_fields_valid(fields)) {
        return setTimeout(() => this.scroll_to_error(), 100);
      }

      this.step++;

      setTimeout(() => this.scroll_to_ref('step'), 100);
    },

  },

};
