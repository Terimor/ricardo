require('./bootstrap')

import Vuelidate from 'vuelidate'
import ElementUI from 'element-ui'
import locale from 'element-ui/lib/locale/lang/en'

Vue.use(ElementUI, { locale })
Vue.use(Vuelidate)

Vue.config.productionTip = false

Vue.component('app-component', require('./App.vue').default)
Vue.component('timer-component', require('./components/common/Timer').default)
Vue.component('emc1-component', require('./components/emc1').default)
Vue.component('text-field', require('./components/common/TextField.vue').default)
Vue.component('date-picker-field', require('./components/common/DatePicker.vue').default)
Vue.component('text-field-with-placeholder', require('./components/common/TextFieldWithPlaceholder.vue').default)
Vue.component('phone-field', require('./components/common/PhoneField.vue').default)
Vue.component('select-field', require('./components/common/Select.vue').default)
Vue.component('radio-button-group', require('./components/common/RadioButtonGroup.vue').default)
Vue.component('leave-modal', require('./components/common/LeaveModal.vue').default)
Vue.component('payment-form', require('./components/common/PaymentForm.vue').default)
// preloaders
Vue.component('preloader-3', require('./components/common/preloaders/Preloader3').default)

// upsells
Vue.component('upsells-component', require('./components/Upsells').default)

// common
Vue.component('green-button', require('./components/common/GreenButton').default)

const app = new Vue({
  el: '#app',
});
