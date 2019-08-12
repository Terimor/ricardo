import VueMq from 'vue-mq'

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

let token = document.head.querySelector('meta[name="csrf-token"]')

if (token) {
  window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content
} else {
  console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token')
}

Vue.config.productionTip = false

Vue.use(VueMq, {
  breakpoints: {
    xs: 375,
    s: 768,
    m: 960,
    l: 1120,
    xl: 1280,
    xxl: Infinity
  }
})
