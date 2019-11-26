export default {

  mounted() {
    this.support_toolbar_adjust();
    this.support_toolbar_bind_chat();

    addEventListener('resize', this.support_toolbar_adjust);
  },


  methods: {

    support_toolbar_adjust() {
      setTimeout(() => {
        const support_toolbar = this.$refs.support_toolbar;
        document.body.style['padding-top'] = support_toolbar.clientHeight + 'px';
        support_toolbar.classList.remove('invisible');
      }, 100);
    },

    support_toolbar_bind_chat() {
      const support_toolbar = this.$refs.support_toolbar;
      const openchat = support_toolbar.querySelector('.openchat');

      if (openchat) {
        openchat.addEventListener('click', this.freshchat_click);
      }
    },

  },

};
