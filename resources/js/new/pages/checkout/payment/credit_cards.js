export default {

  computed: {

    credit_cards_class_list() {
      return {
        ['fa-chevron-' + (!this.is_rtl ? 'right' : 'left')]: true,
      };
    },

  },


  methods: {

    credit_cards_click() {
      this.payment_provider_change('credit-card');
      setTimeout(() => this.scroll_to_ref('form'), 100);
    },

  },

};
