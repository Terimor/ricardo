export default {

  mounted() {
    this.home_welcome_init();
  },


  computed: {

    home_welcome_chat_link() {
      return this.$refs.home_welcome.querySelector('.chat-link');
    },

    home_welcome_contact_link() {
      return this.$refs.home_welcome.querySelector('.contact-link');
    },

    home_welcome_scroll_link() {
      return this.$refs.home_welcome.querySelector('.scroll-link');
    },

  },


  methods: {

    home_welcome_init() {
      this.home_welcome_bind_chat_link();
      this.home_welcome_bind_contact_link();
      this.home_welcome_bind_scroll_link();
    },

    home_welcome_bind_chat_link() {
      if (this.home_welcome_chat_link) {
        this.home_welcome_chat_link.addEventListener('click', this.freshchat_click);
      }
    },

    home_welcome_bind_contact_link() {
      if (this.home_welcome_contact_link) {
        this.home_welcome_contact_link.href = this.searchPopulate('/contact-us');
      }
    },

    home_welcome_bind_scroll_link() {
      if (this.home_welcome_scroll_link) {
        this.home_welcome_scroll_link.addEventListener('click', () => {
          this.$refs.home_products.scrollIntoView({
            behavior: 'smooth',
          });
        });
      }
    },

  },

};
