require('../bootstrap')

const header = new Vue({
  el: '#header',
  name: 'header',

  mounted() {
    window.addEventListener('scroll', () => {
      const header = document.getElementById('header')

      if (document.body.scrollTop > 100) {
        header.style.top = -header.clientHeight
      } else {
        header.style.top = 0
      }
    })
  },
})
