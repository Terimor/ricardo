export default {

  watch: {

    'document.readyState': {
      immediate: true,
      handler() {
        if (this.document.readyState === 'complete') {
          this.freshchat_init();
        }
      },
    },

  },


  methods: {

    freshchat_init() {
      if (this.$refs.freshchat_image) {
        js_deps.wait(['freshchat'], () => {
          fcWidget.on('widget:opened', this.freshchat_opened_event);
          fcWidget.on('widget:closed', this.freshchat_closed_event);
        });

        addEventListener('mousewheel', this.freshchat_adjust);
        addEventListener('resize', this.freshchat_adjust);

        this.$refs.freshchat_image.classList.remove('d-none');
      }
    },

    freshchat_adjust() {
      setTimeout(() => {
        const html = document.documentElement;

        if (html.clientWidth < 768) {
          const diff = html.scrollHeight - (html.scrollTop + html.clientHeight);
          this.$refs.freshchat_image.classList[diff < this.$refs.footer.clientHeight ? 'add' : 'remove']('d-none');
        } else {
          this.$refs.freshchat_image.classList.remove('d-none');
        }
      }, 500);
    },

    freshchat_opened_event() {
      this.$refs.freshchat_image.classList.add('d-none');
    },

    freshchat_closed_event() {
      this.$refs.freshchat_image.classList.remove('d-none');
    },

    freshchat_click() {
      fcWidget.open();
    },

  },

};
