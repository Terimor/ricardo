import app from '../app';
import timer from './checkout/blocks/timer';
import preloader from './checkout/blocks/preloader';
import recently_bought from './checkout/blocks/recently_bought';
import leave_modal from './checkout/blocks/leave_modal';
import deals from './checkout/deals';
import form from './checkout/form';
import payment from './checkout/payment';
import prices from './checkout/prices';
import leads from './checkout/leads';
import purchasMixin from '../../mixins/purchas';

import PurchasAlreadyExists from '../../components/common/PurchasAlreadyExists';


export default {

  mixins: [
    app,
    timer,
    preloader,
    recently_bought,
    leave_modal,
    deals,
    form,
    payment,
    prices,
    leads,
    purchasMixin
  ],

  components: {
    PurchasAlreadyExists
  },

  created() {
    this.set_browser_title();
    this.scroll_3ds_restore();
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
      return js_data.product && js_data.product.type === 'virtual';
    },

  },


  methods: {

    set_browser_title() {
      const initial_title = document.title;
      window.onfocus = () => document.title = initial_title;
      window.onblur = () => document.title = this.t('checkout.page_title.wait');
    },

    scroll_3ds_restore() {
      if (js_query_params['3ds'] === 'failure') {
        setTimeout(() => this.scroll_to_ref('payment_error'), 1000);
      }

      if (js_query_params['3ds'] === 'pending' && js_query_params.bs_pf_token) {
        setTimeout(() => this.scroll_to_ref('terms_field'), 1000);
      }
    },

  },

};
