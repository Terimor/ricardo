import './resourses/polyfills';
import './services/globals';
import globals from './mixins/globals';

require('./bootstrap')


const app = new Vue({
  el: '#app',
  mixins: [globals],
  data() {
    return {
      paymentMethods: { ...checkoutData.paymentMethods },
    };
  },
  mounted() {
    document.body.classList.remove('js-hidden');
  },
});


