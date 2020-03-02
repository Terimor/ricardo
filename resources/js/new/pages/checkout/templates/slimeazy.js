import checkout from '../../checkout';
import section4 from './slimeazy/section4';


js_deps.wait(['vue'], () => {
  new Vue({

    el: '#app',


    mixins: [
      checkout,
      section4,
    ],


    validations() {
      return {
        ...checkout.validations.call(this),
      };
    },

  });
});
