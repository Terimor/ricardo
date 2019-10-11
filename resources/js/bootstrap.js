import VueMq from 'vue-mq'
import './services/queryParams';
import './services/sentry';
import './services/ebanx';
import './services/ipqs';
import './UIsettings';

document.getElementById('header').classList.remove('hidden');

Vue.config.productionTip = false

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
