let index = -1;
let user_index = -1;


export default {

  data() {
    return {
      recently_bought_active: null,
      recently_bought_just_bought: '',
      recently_bought_user_active: '',
    };
  },


  created() {
    this.recently_bought_init();
  },


  computed: {

    recently_bought_queue() {
      let queue = [
        'just_bought',
        'just_bought',
        'user_active',
        !this.is_paypal_hidden ? 'paypal' : null,
        'just_bought',
        'just_bought',
        'bestseller',
      ];

      return queue.filter(name => !!name);
    },

  },


  methods: {

    recently_bought_init() {
      const timeout = 2000 + (this.preloader_enabled ? 10000 : 0);

      if (+js_query_params.recentlybought === 0) {
        return;
      }

      setTimeout(() => {
        setInterval(() => {
          index++;

          if (index === this.recently_bought_queue.length) {
            index = 0;
          }

          this.recently_bought_active = this.recently_bought_queue[index];

          setTimeout(() => {
            this.recently_bought_active = null;
          }, 5000);

          if (this.recently_bought_active === 'just_bought') {
            this.recently_bought_prepare_just_bought();
          }

          if (this.recently_bought_active === 'user_active') {
            this.recently_bought_prepare_user_active();
          }
        }, 7000);
      }, timeout);
    },

    recently_bought_prepare_just_bought() {
      const quantities = [1, 3, 5];

      user_index++;

      if (user_index === js_data.recently_bought_names.length) {
        user_index = 0;
      }

      const quantity = quantities[Math.floor(Math.random() * 3)];

      let just_bought = this.t('checkout.notification.just_bought', {
        first_name: js_data.recently_bought_names[user_index],
        city: js_data.recently_bought_cities[user_index] || js_data.recently_bought_cities[0],
        count: quantity,
        product: js_data.product.product_name,
      });

      if (js_data.product.unit_qty > 1) {
        just_bought += ' ' + this.t('product.unit_qty.total', {
          count: quantity * js_data.product.unit_qty,
        });
      }

      this.recently_bought_just_bought = just_bought;
    },

    recently_bought_prepare_user_active() {
      this.recently_bought_user_active = this.t('checkout.notification.users_active', {
        count: Math.floor(Math.random() * 12) + 33,
      });
    },

    recently_bought_paypal_click() {
      this.scroll_to_ref('paypal_button');
    },

  },

};
