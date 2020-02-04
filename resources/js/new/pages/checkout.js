import app from '../app';
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


  computed: {

    is_vrtl_checkout() {
      return location.pathname.substr(1).split('/').shift() === 'vrtl';
    },

  },


  methods: {

    set_browser_title() {
      const initial_title = document.title;
      window.onfocus = () => document.title = initial_title;
      window.onblur = () => document.title = this.t('checkout.page_title.wait');
    },

    scroll_3ds_failure() {
      if (js_query_params['3ds'] === 'failure') {
        setTimeout(() => this.scroll_to_ref('payment_error'), 1000);
      }
    },

  },

};
