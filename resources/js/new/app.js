import Vuelidate from 'vuelidate';
import polyfills from './utils/polyfills';
import mdocument from './utils/document';
import i18n from './utils/i18n';
import request from './utils/request';
import scroll from './utils/scroll';
import header from './regions/header';


js_deps.wait(['vue'], () => {
  Vue.config.productionTip = false;
  Vue.use(Vuelidate);
});


export default {

  mixins: [
    i18n,
    mdocument,
    request,
    scroll,
    header,
  ],


  mounted() {
    this.lazyload_update();
  },


  updated() {
    this.lazyload_update();
  },


  computed: {

    js_data() {
      return js_data;
    },

    is_rtl() {
      return !!document.querySelector('html[dir="rtl"]');
    },

    is_ie11() {
      return window.navigator && navigator.userAgent && /Trident/.test(navigator.userAgent) || false;
    },

    is_affid_empty() {
      return (!js_query_params.aff_id || js_query_params.aff_id === '0')
        && (!js_query_params.affid || js_query_params.affid === '0');
    },

  },


  methods: {

    lazyload_update() {
      if (window.js_deps && js_deps.wait_for) {
        js_deps.wait_for(
          () => window.lazyLoadInstance,
          () => {
            [].forEach.call(document.querySelectorAll('img.lazy.loaded'), element => {
              element.removeAttribute('data-was-processed');
            });

            lazyLoadInstance.update();
          },
        );
      }
    },

  },

};
