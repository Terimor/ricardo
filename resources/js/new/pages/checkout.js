import app from '../app';
import fingerprint from '../services/fingerprint';
import ipqualityscore from '../services/ipqualityscore';
import timer from './checkout/blocks/timer';
import preloader from './checkout/blocks/preloader';
import recently_bought from './checkout/blocks/recently_bought';
import leave_modal from './checkout/blocks/leave_modal';
import form from './checkout/form';
import payment from './checkout/payment';
import prices from './checkout/prices';


export default {

  mixins: [
    app,
    fingerprint,
    ipqualityscore,
    timer,
    preloader,
    recently_bought,
    leave_modal,
    form,
    payment,
    prices,
  ],


  created() {
    this.set_browser_title();
    this.scroll_3ds_failure();
  },


  validations() {
    return {
      form: {
        ...form.validations.call(this),
      },
    };
  },


  methods: {

    set_browser_title() {
      const title_normal = document.title;
      const title_wait = this.t('checkout.page_title.wait');
      window.onfocus = () => document.title = title_normal;
      window.onblur = () => document.title = title_wait;
    },

    scroll_3ds_failure() {
      if (js_query_params['3ds'] === 'failure') {
        setTimeout(() => this.scroll_to_ref('payment_error'), 1000);
      }
    },

  },

};
