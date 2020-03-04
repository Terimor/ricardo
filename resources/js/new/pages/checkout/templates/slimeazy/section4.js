export default {

  data() {
    return {
      section4_slider_visible_count: 0,
      section4_slider_offset: 0,
    };
  },


  watch: {

    window_width: {
      immediate: true,
      handler(value) {
        this.section4_slider_visible_count = value > 990
          ? 3
          : value > 539
            ? 2
            : 1;
      },
    },

  },


  mounted() {
    setInterval(this.section4_slider_right, 5000);
  },


  computed: {

    section4_slider_slides_count() {
      return document.querySelectorAll('.section4 .slider .slide').length;
    },

    section4_slider_count() {
      return this.section4_slider_slides_count > this.section4_slider_visible_count
        ? this.section4_slider_slides_count - this.section4_slider_visible_count
        : 0;
    },

    section4_slider_left_active() {
      return this.section4_slider_count > 0 && this.section4_slider_offset > 0;
    },

    section4_slider_right_active() {
      return this.section4_slider_count > 0 && this.section4_slider_offset < this.section4_slider_count;
    },

    section4_slider_style() {
      return {
        'margin-left': '-' + ((100 / this.section4_slider_visible_count) * this.section4_slider_offset) + '%',
        'margin-right': ((100 / this.section4_slider_visible_count) * this.section4_slider_offset) + '%',
      };
    },

    section4_slider_slide_style() {
      return {
        width: (100 / this.section4_slider_visible_count) + '%',
      };
    },

  },


  methods: {

    section4_slider_left() {
      if (this.section4_slider_left_active) {
        this.section4_slider_offset--;
      } else {
        this.section4_slider_offset = this.section4_slider_count;
      }
    },

    section4_slider_right() {
      if (this.section4_slider_right_active) {
        this.section4_slider_offset++;
      } else {
        this.section4_slider_offset = 0;
      }
    },

  },

};
