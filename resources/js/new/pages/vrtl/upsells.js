import app from '../../app';


js_deps.wait(['vue'], () => {
  new Vue({

    el: '#app',


    mixins: [
      app,
    ],


    data() {
      return {
        step: 1,
        cart: {},
      };
    },


    created() {
      localStorage.removeItem('vrtl_show_upsells');

      if (js_data.upsells.length === 0) {
        this.goto_thankyou();
      }
    },


    watch: {

      step() {
        if (window.scrollTo) {
          scrollTo(0, 0);
        }
      },

    },


    computed: {

      order_upgraded_visible() {
        const prev_index = this.step - 3;
        const upsell = js_data.upsells[prev_index];

        return this.cart[upsell.product_id] !== undefined;
      },

    },


    methods: {

      add_upsell() {
        const index = this.step !== 1
          ? this.step - 2
          : 0;

        const upsell = js_data.upsells[index];
        Vue.set(this.cart, upsell.product_id, 1);

        if (js_data.upsells[index + 1]) {
          this.step = this.step !== 1
            ? this.step + 1
            : 3;
        } else {
          this.goto_checkout();
        }
      },

      cancel() {
        const index = this.step !== 1
          ? this.step - 2
          : 0;

        if (this.step === 1 || js_data.upsells[index + 1]) {
          this.step++;
        } else {
          this.goto_checkout();
        }
      },

      goto_checkout() {
        this.goto_thankyou();
      },

      goto_thankyou() {
        this.goto('/vrtl/thankyou');
      },

    },

  });
});
