export default {

  computed: {

    eps_button_class_list() {
      return {
        ['fa-chevron-' + (!this.is_rtl ? 'right' : 'left')]: true,
      };
    },

  },


  methods: {

    eps_button_click() {
      this.form.payment_provider = 'eps';
    },

  },

};
