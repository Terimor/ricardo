import VueMq from 'vue-mq'
import { t, timage } from './utils/i18n';
import './services/queryParams';
import './services/ipqs';
import './UIsettings';


Vue.config.productionTip = false

Vue.prototype.$t = t;
Vue.prototype.$timage = timage;

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
