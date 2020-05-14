export default {

  data() {
    return {
      slider_offset: 0,
    };
  },


  computed: {

    slider_count() {
      return this.images_count > 4
        ? this.images_count - 4
        : 0;
    },

    slider_left_active() {
      return this.slider_count > 0 && this.slider_offset > 0;
    },

    slider_right_active() {
      return this.slider_count > 0 && this.slider_offset < this.slider_count;
    },

    slider_style() {
      return document.dir === 'rtl' ? {
        'margin-right': '-' + (25 * this.slider_offset) + '%',
        'margin-left': (25 * this.slider_offset) + '%',
      } : {
        'margin-left': '-' + (25 * this.slider_offset) + '%',
        'margin-right': (25 * this.slider_offset) + '%',
      };
    },

  },


  methods: {

    slider_left() {
      if (this.slider_left_active) {
        this.slider_offset--;
      }
    },

    slider_right() {
      if (this.slider_right_active) {
        this.slider_offset++;
      }
    },

    slider_select(index) {
      this.image_index = index;
    },

  },

};
