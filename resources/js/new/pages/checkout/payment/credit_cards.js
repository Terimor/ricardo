export default {

  methods: {

    credit_cards_click() {
      this.payment_provider_change('credit-card');
      setTimeout(() => this.scroll_to_ref('form'), 100);
    },

  },

};
