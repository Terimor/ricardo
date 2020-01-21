import checkout from '../../checkout';
import summary from './vc1/summary';


js_deps.wait(['vue'], () => {
  new Vue({

    el: '#app',


    validations() {
      return {
        ...checkout.validations.call(this),
      };
    },


    mixins: [
      checkout,
      summary,
    ],


    created() {
      this.form.deal = 1;

      if (js_query_params['3ds'] === 'failure') {
        setTimeout(() => this.scroll_to_ref('payment_error'), 1000);
      }
    },

  });
});
