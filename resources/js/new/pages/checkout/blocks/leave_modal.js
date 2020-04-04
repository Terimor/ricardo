let already_shown = false;


export default {

  data() {
    return {
      leave_modal_visible: false,
    };
  },


  created() {
    if (this.leave_modal_enabled) {
      this.leave_modal_init();
    }
  },


  computed: {

    leave_modal_enabled() {
      return +js_query_params.exit !== 0;
    },

  },


  methods: {

    leave_modal_init() {
return;
      const timeout = 5000 + (this.preloader_enabled ? 10000 : 0);

      setTimeout(() => {
        document.addEventListener('mouseleave', () => {
          if (!already_shown) {
            this.leave_modal_visible = true;
            already_shown = true;
          }
        });
      }, timeout);
    },

    leave_modal_agree_click() {
      this.leave_modal_visible = false;
      this.scroll_to_ref('content');
    },

    leave_modal_close_click() {
      this.leave_modal_visible = false;
    },

  },

};
