export default {

  mounted() {
    this.support_toolbar_init();
  },


  computed: {

    support_toolbar_contact_link() {
      return this.$refs.support_toolbar
        ? this.$refs.support_toolbar.querySelector('.contact-link')
        : null;
    },

  },


  methods: {

    support_toolbar_init() {
      if (this.$refs.support_toolbar) {
        this.support_toolbar_bind_contact_link();
      }
    },

    support_toolbar_bind_contact_link() {
      if (this.support_toolbar_contact_link) {
        this.support_toolbar_contact_link.href = this.searchPopulate('/contact-us');
      }
    },

  },

};
