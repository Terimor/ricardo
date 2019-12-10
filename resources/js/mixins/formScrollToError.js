export default {
  methods: {
    scrollToError (selector) {
      this.$nextTick(() => {
        const inputsArr = [...document.querySelectorAll(selector || '.scroll-when-error')];

        let targetInput = inputsArr.find((item) => {
          return item.classList.contains('invalid');
        });

        if (targetInput) {
          let position = targetInput.getBoundingClientRect().top - 20;
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
      });
    }
  }
}
