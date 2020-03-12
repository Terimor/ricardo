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


    mounted() {
      this.form.deal = 1;
    },

  });
});
