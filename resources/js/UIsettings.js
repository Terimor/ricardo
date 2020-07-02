import Vuelidate from 'vuelidate'

Vue.use(Vuelidate)

Vue.config.productionTip = false

Vue.component('app-component', require('./App.vue').default)
Vue.component('timer-component', require('./components/common/Timer').default)
Vue.component('emc1-component', require('./components/emc1').default)
Vue.component('smc7-component', require('./components/smc7').default)
Vue.component('vmc4-component', require('./components/vmc4').default)
Vue.component('text-field', require('./components/common/TextField.vue').default)
Vue.component('date-picker-field', require('./components/common/DatePicker.vue').default)
Vue.component('text-field-with-placeholder', require('./components/common/TextFieldWithPlaceholder.vue').default)
Vue.component('phone-field', require('./components/common/PhoneField.vue').default)
Vue.component('select-field', require('./components/common/Select.vue').default)
Vue.component('radio-button-group', require('./components/common/RadioButtonGroup.vue').default)
Vue.component('payment-provider-radio-list', require('./components/common/PaymentProviderRadioList.vue').default)
Vue.component('payment-providers-apm', require('./components/common/PaymentProvidersAPM.vue').default)
Vue.component('leave-modal', require('./components/common/LeaveModal.vue').default)
Vue.component('payment-form', require('./components/common/PaymentForm.vue').default)
Vue.component('payment-form-smc7', require('./components/common/PaymentFormSMC7.vue').default)
Vue.component('payment-form-vmc4', require('./components/common/PaymentFormVMC4.vue').default)
Vue.component('paypal-button', require('./components/common/PaypalButton.vue').default)
Vue.component('pay-method-item', require('./components/common/PayMethodItem.vue').default)
Vue.component('notice', require('./components/common/Notice.vue').default)
// preloaders
Vue.component('preloader-3', require('./components/common/preloaders/Preloader3').default)

// upsells
Vue.component('upsells-component', require('./components/Upsells').default)
Vue.component('upsells-virtual', require('./components/UpsellsVirtual').default)

// common
Vue.component('green-button', require('./components/common/GreenButton').default)
Vue.component('modal', require('./components/common/modal').default)
Vue.component('order-status', require('./components/OrderStatus').default)
