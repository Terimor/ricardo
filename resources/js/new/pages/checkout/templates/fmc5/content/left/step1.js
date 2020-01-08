export default {

  methods: {

    step1_submit() {
      const fields = [
        'deal',
        'variant',
        'installments',
      ];

      if (!this.form_check_fields_valid(fields)) {
        return setTimeout(() => this.scroll_to_error(), 100);
      }

      this.step++;

      setTimeout(() => this.scroll_to_ref('step'), 100);
    },

  },

};
