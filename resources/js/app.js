require('./bootstrap')

import Vuelidate from 'vuelidate'
import ElementUI from 'element-ui'

Vue.use(Vuelidate)
Vue.use(ElementUI)

Vue.config.productionTip = false

Vue.component('app-component', require('./App.vue').default)
Vue.component('timer', require('./components/common/Timer').default)
Vue.component('emc1-component', require('./components/emc1').default)
Vue.component('text-field', require('./components/common/TextField.vue').default)
Vue.component('phone-field', require('./components/common/PhoneField.vue').default)
Vue.component('select-field', require('./components/common/Select.vue').default)
Vue.component('radio-button-group', require('./components/common/RadioButtonGroup.vue').default)
Vue.component('leave-modal', require('./components/common/LeaveModal.vue').default)
Vue.component('payment-form', require('./components/common/PaymentForm.vue').default)
// preloaders
Vue.component('preloader-3', require('./components/common/preloaders/Preloader3').default)

const app = new Vue({
  el: '#app',
});
