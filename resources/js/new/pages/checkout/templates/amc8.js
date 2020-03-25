import checkout from '../../checkout';

const users_online_min = 51;
const users_online_max = 499;


js_deps.wait(['vue'], () => {
  new Vue({

    el: '#app',


    mixins: [
      checkout,
    ],


    validations() {
      return {
        ...checkout.validations.call(this),
      };
    },


    data() {
      return {
        users_online: Math.floor(Math.random() * (users_online_max - users_online_min + 1)) + users_online_min,
      };
    },


    created() {
      this.users_online_init();
    },


    computed: {

      step_numbers() {
        return {
          1: 1,
          2: 2,
          3: js_data.product.skus.length > 1 ? 3 : 2,
          4: js_data.product.skus.length > 1 ? 4 : 3,
          5: js_data.product.skus.length > 1 ? 5 : 4,
          6: js_data.product.skus.length > 1 ? 6 : 5,
        };
      },

    },


    methods: {

      users_online_init() {
        const duration = Math.floor(Math.random() * (5 - 1 + 1)) + 1;

        setTimeout(() => {
          this.users_online += Math.floor(Math.random() * 2) === 0
            ? -1
            : 1

          if (this.users_online < users_online_min) {
            this.users_online = users_online_min;
          }

          if (this.users_online > users_online_max) {
            this.users_online = users_online_max;
          }

          this.users_online_init();
        }, duration * 1000);
      },

    },

  });
});
