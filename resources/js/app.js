import './resourses/polyfills';
import './services/globals';
import globals from './mixins/globals';

require('./bootstrap')


const app = new Vue({
  el: '#app',
  mixins: [globals],
  data() {
    return {
      paymentMethods: window.checkoutData && checkoutData.paymentMethods
        ? JSON.parse(JSON.stringify(checkoutData.paymentMethods))
        : [],
    };
  },
  mounted() {
    document.documentElement.classList.remove('js-hidden');
  },
});


