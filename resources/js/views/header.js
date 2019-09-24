import queryToComponent from '../mixins/queryToComponent';

require('../bootstrap')

const header = new Vue({
  el: '#header',
  name: 'header',

  mixins: [
    queryToComponent,
  ],

  mounted() {
    window.addEventListener('scroll', () => {
      const header = document.getElementById('header');

      if (document.body.scrollTop > 100) {
        header.style.top = -header.clientHeight
      } else {
        header.style.top = 0
      }
    });
  },

  computed: {
    isCheckout() {
      return document.location.pathname.split('/').pop() === 'checkout';
    },

    isTimerVisible() {
      return this.isCheckout && +this.queryParams.show_timer === 1;
    },
  },
})
