require('./bootstrap')

import Vuelidate from 'vuelidate'
import ElementUI from 'element-ui'

Vue.use(Vuelidate)
Vue.use(ElementUI)

Vue.config.productionTip = false

Vue.component('example-component', require('./components/ExampleComponent.vue').default)
Vue.component('app-component', require('./App.vue').default)
Vue.component('emc1-component', require('./components/emc1').default)
Vue.component('header-block', require('./components/common/Header.vue').default)
Vue.component('text-field', require('./components/common/TextField.vue').default)
Vue.component('phone-field', require('./components/common/PhoneField.vue').default)
Vue.component('select-field', require('./components/common/Select.vue').default)
Vue.component('radio-button-group', require('./components/common/RadioButtonGroup.vue').default)
// preloaders
Vue.component('preloader-3', require('./components/common/preloaders/Preloader3').default)

const app = new Vue({
  el: '#app',
});
