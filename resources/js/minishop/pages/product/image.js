export default {

  data() {
    return {
      image_index: 0,
    };
  },


  computed: {

    images_count() {
      let result = js_data.product.image.length;

      if (js_data.product.vimeo_id) {
        result++;
      }

      return result;
    },

    image_style() {
      return {
        'margin-left': '-' + (100 * this.image_index) + '%',
        'margin-right': (100 * this.image_index) + '%',
      };
    },

  },

};
