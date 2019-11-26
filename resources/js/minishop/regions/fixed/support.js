export default {

  mounted() {
    this.support_toolbar_adjust();
    this.support_toolbar_bind_chat();
    this.support_toolbar_bind_contact();

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
      const chat_link = support_toolbar.querySelector('.chat-link');

      if (chat_link) {
        chat_link.addEventListener('click', this.freshchat_click);
      }
    },

    support_toolbar_bind_contact() {
      const support_toolbar = this.$refs.support_toolbar;
      const contact_link = support_toolbar.querySelector('.contact-link');
      contact_link.href = this.searchPopulate('/contact-us');
    },

  },

};
