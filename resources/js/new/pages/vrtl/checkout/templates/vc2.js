import checkout from '../../checkout';


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


    created() {
      this.form.deal = 1;
      this.form.payment_provider = 'credit-card';
    },

  });
});
