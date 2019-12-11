export default {

  mounted() {
    this.welcome_init();
  },


  computed: {

    welcome_chat_link() {
      return this.$refs.welcome.querySelector('.chat-link');
    },

    welcome_contact_link() {
      return this.$refs.welcome.querySelector('.contact-link');
    },

    welcome_scroll_link() {
      return this.$refs.welcome.querySelector('.scroll-link');
    },

  },


  methods: {

    welcome_init() {
      this.bind_welcome_chat_link();
      this.bind_welcome_contact_link();
      this.bind_welcome_scroll_link();
    },

    bind_welcome_chat_link() {
      if (this.welcome_chat_link) {
        this.welcome_chat_link.addEventListener('click', this.freshchat_click);
      }
    },

    bind_welcome_contact_link() {
      if (this.welcome_contact_link) {
        this.welcome_contact_link.href = this.searchPopulate('/contact-us');
      }
    },

    bind_welcome_scroll_link() {
      if (this.welcome_scroll_link) {
        this.welcome_scroll_link.addEventListener('click', () => {
          this.scroll_to_ref('products');
        });
      }
    },

  },

};
