import checkout from '../../checkout';
import content from './fmc5/content';
import bottom from './fmc5/bottom';


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
      content,
      bottom,
    ],


    created() {
      if (js_query_params['3ds'] === 'failure') {
        setTimeout(() => this.scroll_to_ref('payment_error'), 1000);
        this.step = 3;
      }
    },

  });
});
