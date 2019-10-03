require('../bootstrap');

import { t } from '../utils/i18n';

import { required, minLength,email } from 'vuelidate/lib/validators'


const tracking = new Vue({
  el: '#tracking',

  data: {
    formData: {
      name: '',
      email: ''
    },
    iframeUrl: '',
    showModal: false,
  },
  validations: {
    formData: {
      name: {
        required,
        minLength: minLength(4)
      },
      email: {
        required,
        email
      },
    },
  },
  computed: {
    isValid() {
      return !this.$v.formData.name.$invalid && !this.$v.formData.email.$invalid;
    },

    // TODO add translations
    //textTrackingTitle: () => t('tracking.title'),
    textTrackingTitle: () => ('Поиск отслеживания заказа'),

    //textName: () => t('tracking.name'),
    textName: () => ('Введите ваше имя'),

    //textNameRequired: () => t('tracking.name_required'),
    textNameRequired: () => ('Это поле обязательно для заполнения'),

    //textEmail: () => t('tracking.email'),
    textEmail: () => ('Введите ваш email'),

    //textEmailRequired: () => t('tracking.email_required'),
    textEmailRequired: () => ('Это поле обязательно для заполнения'),

    //textFormButton: () => t('tracking.search'),
    textFormButton: () => ('Искать заказ'),

    //textToMenuButton: () => t('tracking.go_to_home'),
    textToMenuButton: () => ('Вернуться в меню'),

    //textModalTitle: () => t('tracking.order_tracking'),
    textModalTitle: () => ('Order tracking'),

    //textModalButton: () => t('tracking.contact_us'),
    textModalButton: () => ('Click here to contact us by chat or email'),
  },
  methods: {
    trackOrder() {
      const url = 'http://sprtdls.aftership.com/';

      if(this.isValid) {
        // TODO here should be request to backend
        /*axios.post('url', this.formData)
          .then(function (response) {
            console.log(response);
          })
          .catch(function (error) {
            console.log(error);
          });*/
        this.showModal = true;
        this.iframeUrl = url + 'LL234085164LU';
      }
    }
  }

})
