export default {

  computed: {

    apm_button_class_list() {
      return {
        ['fa-chevron-' + (!this.is_rtl ? 'right' : 'left')]: true,
      };
    },

  },


  methods: {

    apm_button_click(name) {
      this.form.payment_provider = name;
    },

  },

};
