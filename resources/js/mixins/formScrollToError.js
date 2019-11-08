export default {
  methods: {
    scrollToError () {
      this.$nextTick(() => {
        const inputsArr = [...document.querySelectorAll('.scroll-when-error')];

        let targetInput = inputsArr.find((item) => {
          return item.classList.contains('invalid');
        });

        if (targetInput && targetInput.scrollIntoView) {
          targetInput.scrollIntoView();
        }
      });
    }
  }
}
