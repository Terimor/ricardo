import './services/globals';
import globals from './mixins/globals';

require('./bootstrap')


const app = new Vue({
  el: '#app',
  mixins: [globals],
  mounted() {
    document.body.classList.remove('js-hidden');
  },
});


