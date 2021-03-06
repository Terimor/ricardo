export default {

  data() {
    return {
      paypal_button: {
        actions: null,
      },
    };
  },


  watch: {

    paypal_button_valid(value) {
      if (value && this.paypal_button.actions) {
        this.paypal_button.actions.enable();
      }
    },

  },


  computed: {

    paypal_button_valid() {
      return !!this.form.deal && !!this.form.variant;
    },

    paypal_button_class_list() {
      return {
        ['fa-chevron-' + (!this.is_rtl ? 'right' : 'left')]: true,
      };
    },

  },


  methods: {

    paypal_button_init() {
      js_deps.wait(['paypal'], () => {
        js_deps.wait_for(
          () => {
            return !!this.$refs.paypal_button
              && !!this.$refs.paypal_button.querySelector('.paypal-button-original');
          },
          () => {
            setTimeout(() => {
              const element = this.$refs.paypal_button.querySelector('.paypal-button-original');

              if (element.paypal_button_init) {
                return;
              }

              element.paypal_button_init = true;

              this.paypal_button_original_init(element);
            }, 100);
          },
        );
      });
    },

    paypal_button_original_init(element) {
      let self = this;

      paypal.Buttons({

        onInit(data, actions) {
          self.paypal_button.actions = actions;

          if (!self.paypal_button_valid) {
            actions.disable();
          }
        },

        createOrder(data, actions) {
          return self.paypal_create_order();
        },

        onClick() {
          self.paypal_button_click();
        },

        onApprove(data, actions) {
          return self.paypal_verify_order(data.orderID);
        },

        onError(err) {
          self.is_submitted = false;
        },

        onCancel(data, actions) {
          self.is_submitted = false;
        },

        style: {
          height: 55,
        },

      }).render(element);
    },

    paypal_button_click() {
      this.$v.form.deal.$touch();
      this.$v.form.variant.$touch();

      if (this.$v.form.deal.$invalid || this.$v.form.variant.$invalid) {
        return setTimeout(() => this.scroll_to_error(), 100);
      }
    },

  },

};
