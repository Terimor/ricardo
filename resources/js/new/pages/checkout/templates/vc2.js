import checkout from '../../checkout';


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
    ],


    created() {
      this.form.deal = 1;
      this.form.payment_provider = 'credit-card';
    },

  });
});
