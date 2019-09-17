import VueMq from 'vue-mq'
import './services/queryParams';
import './UIsettings';

window.axios.defaults.headers.common = {
  'X-Requested-With': 'XMLHttpRequest',
  'X-CSRF-TOKEN' : document.querySelector('meta[name="csrf-token"]').getAttribute('content')
};

window.axios.defaults.baseUrl = window.location.origin;

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
