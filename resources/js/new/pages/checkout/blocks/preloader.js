export default {

  data() {
    return {
      preloader_step: 1,
      preloader_progress: 0,
      preloader_visible: false,
    };
  },


  mounted() {
    if (this.preloader_enabled) {
      this.preloader_init();
    }
  },


  computed: {

    preloader_enabled() {
      return js_query_params.preload === '{preload}' || +js_query_params.preload === 3;
    },

  },


  methods: {

    preloader_init() {
      this.preloader_visible = true;
      setTimeout(this.preloader_process, 1000);
    },

    preloader_process() {
      this.preloader_progress++;

      if (this.preloader_progress === 33) {
        setTimeout(this.preloader_process, 1500);
        this.preloader_step = 2;
        return;
      }

      if (this.preloader_progress === 67) {
        setTimeout(this.preloader_process, 1500);
        this.preloader_step = 3;
        return;
      }

      if (this.preloader_progress < 100) {
        setTimeout(this.preloader_process, 44);
      } else {
        setTimeout(() => this.preloader_visible = false, 1300);
      }
    },

  },

};
