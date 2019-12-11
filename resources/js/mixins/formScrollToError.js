export default {

  methods: {

    scrollTopElement(element) {
      if (element) {
        let position = element.getBoundingClientRect().top - 20;
        position += document.documentElement.scrollTop;

        if (window.blackFridayEnabled) {
          position -= this.$root.$refs.blackFriday.clientHeight;
        }

        if (window.christmasEnabled) {
          position -= this.$root.$refs.christmas.clientHeight;
        }

        scrollTo({
          top: position,
          behavior: 'smooth',
        });
      }
    },
    
    scrollToError() {
      this.$nextTick(() => {
        const inputsArr = [].slice.call(document.querySelectorAll('.scroll-when-error'));
        const element = inputsArr.find((item) => item.classList.contains('invalid'));
        this.scrollTopElement(element);
      });
    },

    scrollToSelector(selector) {
      this.$nextTick(() => {
        const element = document.querySelector(selector);
        this.scrollTopElement(element);
      });
    },

  },

};
