import Vuelidate from 'vuelidate';
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


  computed: {

    is_rtl() {
      return !!document.querySelector('html[dir="rtl"]');
    },

    is_affid_empty() {
      return (!js_query_params.aff_id || js_query_params.aff_id === '0')
        && (!js_query_params.affid || js_query_params.affid === '0');
    },

  },

};
