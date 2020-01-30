export default {

  methods: {

    step3_submit() {
      if (this.is_submitted) {
        return;
      }

      const fields = [
        'warranty',
        'card_holder',
        'card_type',
        'card_number',
        'card_date',
        'card_cvv',
        'document_type',
        'document_number',
        'terms',
      ];

      if (!this.form_check_fields_valid(fields)) {
        return setTimeout(() => this.scroll_to_error(), 100);
      }

      this.credit_card_create_order();
    },

  },

};
