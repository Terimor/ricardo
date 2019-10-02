require('../bootstrap');

import { t } from '../utils/i18n';

import { required, minLength } from 'vuelidate/lib/validators'



const tracking = new Vue({
  el: '#tracking',

  data: {
    name: '',
    email: ''
  },
  validations: {
    name: {
      required,
      minLength: minLength(4)
    },
  },
  computed: {
    textName: () => ('Введите ваше имя'),
    textNameRequired: () => ('Это поле обязательно для заполнения'),
    textEmail: () => ('Введите ваш email'),
    textEmailRequired: () => ('Это поле обязательно для заполнения'),
  }

})
