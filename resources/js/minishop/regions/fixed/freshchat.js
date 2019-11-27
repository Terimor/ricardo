export default {

  data: {
    freshchat_opened: false,
  },


  mounted() {
    addEventListener('mousewheel', this.freshchat_adjust);
    addEventListener('resize', this.freshchat_adjust);

    js_deps.wait(['freshchat'], () => {
      fcWidget.on('widget:opened', this.freshchat_opened_event);
      fcWidget.on('widget:closed', this.freshchat_closed_event);
    });
  },


  computed: {

    freshchat_class() {
      return {
        'fc-open': this.freshchat_opened,
      };
    },

  },


  methods: {

    freshchat_adjust() {
      setTimeout(() => {
        const html = document.documentElement;
        const image = this.$refs.freshchat_image;
        const footer = this.$refs.footer;

        if (html.clientWidth < 768) {
          const diff = html.scrollHeight - (html.scrollTop + html.clientHeight);
          image.classList[diff < footer.clientHeight ? 'add' : 'remove']('d-none');
        } else {
          image.classList.remove('d-none');
        }
      }, 500);
    },

    freshchat_opened_event() {
      this.freshchat_opened = true;
    },

    freshchat_closed_event() {
      this.freshchat_opened = false;
    },

    freshchat_click() {
      fcWidget.open();
    },

  },

};
