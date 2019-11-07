import './resourses/polyfills';
import './services/globals';
import globals from './mixins/globals';

require('./bootstrap')


const app = new Vue({
  el: '#app',
  mixins: [globals],
  data() {
    let result = {};

    if (window.checkoutData && checkoutData.paymentMethods) {
      result.paymentMethods = JSON.parse(JSON.stringify(checkoutData.paymentMethods));
    }

    return result;
  },
  mounted() {
    document.documentElement.classList.remove('js-hidden');
  },
});


