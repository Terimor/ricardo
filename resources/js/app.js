import './resourses/polyfills';
import './services/globals';
import globals from './mixins/globals';
import * as extraFields from './mixins/extraFields';


js_deps.wait(['vue', 'element', 'intl_tel_input'], () => {
  require('./bootstrap');

  new Vue({

    el: '#app',


    mixins: [
      globals,
      extraFields.appMixin,
    ],

  });
});
