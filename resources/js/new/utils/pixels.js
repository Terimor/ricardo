let cache = [];
let initialized = false;


export default {

  watch: {

    ready_state: {
      handler(value) {
        if (!initialized && this.ready_state === 'complete') {
          this.print_pixels('checkout');
          initialized = true;
        }
      },
      immediate: true,
    },

  },


  methods: {

    print_pixels(type) {
      if (js_data.pixels) {
        js_data.pixels
          .filter(pixel => pixel.type === type)
          .filter(pixel => cache.indexOf(pixel) === -1)
          .forEach(pixel => {
            this.print_pixel(pixel);
            cache.push(pixel);
          });
      }
    },


    print_pixel(pixel) {
      const element = document.createElement('div');
      element.innerHTML = pixel.code;

      function replace_script(element) {
        if (element.tagName === 'SCRIPT') {
          const script = document.createElement('script');
          script.innerHTML = element.innerHTML;
          element.parentNode.replaceChild(script, element);
        } else {
          [...element.children].forEach(replace_script);
        }
      }

      [...element.children].forEach(child => {
        document.body.appendChild(child);
        replace_script(child);
      });
    },

  },

};
