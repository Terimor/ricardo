import './resourses/polyfills';
import './services/globals';
import fixed from './new/regions/fixed';
import globals from './mixins/globals';
import documentMixin from './mixins/document';
import * as extraFields from './mixins/extraFields';


let deps = ['vue', 'element'];

if (location.pathname.substr(1).split('/').shift() !== 'thankyou-promos' && location.hostname !== 'smartbell.pro') {
  deps.push('intl_tel_input');
}

js_deps.wait(deps, () => {
  require('./bootstrap');

  new Vue({

    el: '#app',


    mixins: [
      fixed,
      globals,
      documentMixin,
      extraFields.appMixin,
    ],

  });
});
