export default {

  methods: {

    scrollToElement(element) {
      if (element) {
        let position = element.getBoundingClientRect().top - 20;
        position += document.documentElement.scrollTop;

        if (js_data.is_black_friday) {
          position -= this.$root.$refs.black_friday.clientHeight;
        }

        if (js_data.is_christmas) {
          position -= this.$root.$refs.christmas.clientHeight;
        }

        if (window.scrollTo) {
          try {
            scrollTo({
              top: position,
              behavior: 'smooth',
            });
          }
          catch (err) {
            scrollTo(0, position);
          }
        }
      }
    },
    
    scrollToError() {
      this.$nextTick(() => {
        const inputsArr = [].slice.call(document.querySelectorAll('.scroll-when-error'));
        const element = inputsArr.find((item) => item.classList.contains('invalid'));
        this.scrollToElement(element);
      });
    },

    scrollToSelector(selector) {
      this.$nextTick(() => {
        const element = document.querySelector(selector);
        this.scrollToElement(element);
      });
    },

  },

};
