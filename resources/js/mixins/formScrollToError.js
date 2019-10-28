export default {
  methods: {
    scrollToError () {
      this.$nextTick(() => {
        const inputsNodeList = document.querySelectorAll('.scroll-when-error');
        const inputsArr = Array.from(inputsNodeList);

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
