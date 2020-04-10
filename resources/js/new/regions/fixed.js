import covid from '../fixed/covid';


export default {

  mixins: [
    covid,
  ],


  data() {
    return {
      fixed_height: 0,
    };
  },


  mounted() {
    this.fixed_height_init();
  },


  watch: {

    window_width: {
      immediate: true,
      handler() {
        this.fixed_height_calc();
      },
    },

  },


  computed: {

    fixed_margin_top() {
      return {
        'margin-top': this.window_scroll_top > 0
          ? this.fixed_height + 'px'
          : null,
      };
    },

  },


  methods: {

    fixed_height_init() {
      let index = 0;

      const interval = setInterval(() => {
        this.fixed_height_calc();

        if (index++ >= 50) {
          clearInterval(interval);
        }
      }, 100);
    },

    fixed_height_calc() {
      if (this.$refs.fixed) {
        this.fixed_height = this.$refs.fixed.clientHeight;
      }
    },

  },

};
