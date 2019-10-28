import '../resourses/polyfills';

require('../bootstrap');

import { t } from '../utils/i18n';
import globals from '../mixins/globals';
import mainValidation from '../validation/main-validation'


const orderTracking = new Vue({
  el: '#orderTracking',
  mixins: [globals],
  data: {
    formData: {
      name: '',
      email: ''
    },
    iframeUrl: '',
    showModal: false,
  },
  validations: mainValidation,
  mounted() {
    document.body.classList.remove('js-hidden');
  },
  computed: {
    isValid() {
      return !this.$v.formData.name.$invalid && !this.$v.formData.email.$invalid;
    },

    textTrackingTitle: () => t('tracking.title'),
    textName: () => t('tracking.name'),
    textNameRequired: () => t('tracking.name_required'),
    textEmail: () => t('tracking.email'),
    textEmailRequired: () => t('tracking.email_required'),
    textFormButton: () => t('tracking.search'),
    textToMenuButton: () => t('tracking.go_to_home'),
    textModalTitle: () => t('tracking.order_tracking'),
    textModalButton: () => t('tracking.contact_us'),
  },
  methods: {
    trackOrder() {
      const url = 'http://sprtdls.aftership.com/';

      if(this.isValid) {
        // TODO there should be request to backend
        this.showModal = true;
        this.iframeUrl = url + 'LL234085164LU';
      }
    }
  }

})
