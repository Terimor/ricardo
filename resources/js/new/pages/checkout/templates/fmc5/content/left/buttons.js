export default {

  methods: {

    back_click() {
      this.step--;
      setTimeout(() => this.scroll_to_ref('step'), 100);
    },

    next_click() {
      if (this.step === 1) {
        return this.step1_submit();
      }

      if (this.step === 2) {
        return this.step2_submit();
      }

      if (this.step === 3) {
        return this.step3_submit();
      }
    },

  },

};
