import './resourses/polyfills';
import './services/globals';
import globals from './mixins/globals';
import * as extraFields from './mixins/extraFields';

require('./bootstrap')


const app = new Vue({
  el: '#app',
  mixins: [
    globals,
    extraFields.appMixin,
  ],
  mounted() {
    document.documentElement.classList.remove('js-hidden');
  },
});


