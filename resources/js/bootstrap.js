import VueMq from 'vue-mq'
import BootstrapVue from 'bootstrap-vue'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { t, timage } from './utils/i18n';
import './services/queryParams';
import './services/ipqs';
import './services/fingerprintjs2';
import './UIsettings';

import 'bootstrap-vue/dist/bootstrap-vue.css'

Vue.config.productionTip = false

Vue.prototype.$t = t;
Vue.prototype.$timage = timage;

Vue.use(BootstrapVue)
Vue.component('font-awesome-icon', FontAwesomeIcon)

Vue.use(VueMq, {
  breakpoints: {
    xs: 375,
    s: 768,
    m: 960,
    l: 1120,
    xl: 1280,
    xxl: Infinity
  }
})
