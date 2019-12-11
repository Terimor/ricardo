export default {

  mounted() {
    this.support_toolbar_init();
  },


  computed: {

    support_toolbar_chat_link() {
      return this.$refs.support_toolbar.querySelector('.chat-link');
    },

    support_toolbar_contact_link() {
      return this.$refs.support_toolbar.querySelector('.contact-link');
    },

  },


  methods: {

    support_toolbar_init() {
      if (this.$refs.support_toolbar) {
        this.support_toolbar_show();
        this.support_toolbar_bind_chat_link();
        this.support_toolbar_bind_contact_link();

        addEventListener('resize', this.support_toolbar_adjust);
      }
    },

    support_toolbar_show() {
      setTimeout(() => {
        const height = this.$refs.support_toolbar.clientHeight;

        this.$refs.support_toolbar.style.height = 0;
        this.$refs.support_toolbar.classList.remove('invisible');

        setTimeout(() => {
          this.$refs.app.style['padding-top'] = height + 'px';
          this.$refs.support_toolbar.style.height = height + 'px';

          setTimeout(() => {
            this.$refs.support_toolbar.style.removeProperty('height');
          }, 1000);
        }, 100);
      }, 100);
    },

    support_toolbar_adjust() {
      this.$refs.app.style['padding-top'] = this.$refs.support_toolbar.clientHeight + 'px';
    },

    support_toolbar_bind_chat_link() {
      if (this.support_toolbar_chat_link) {
        this.support_toolbar_chat_link.addEventListener('click', this.freshchat_click);
      }
    },

    support_toolbar_bind_contact_link() {
      if (this.support_toolbar_contact_link) {
        this.support_toolbar_contact_link.href = this.searchPopulate('/contact-us');
      }
    },

  },

};
