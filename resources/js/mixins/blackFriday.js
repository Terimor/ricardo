export default {

  mounted() {
    if (!window.blackFridayEnabled) {
      return;
    }

    this.blackFridayShow();
    this.blackFridayBindClick();

    addEventListener('resize', this.blackFridayResize);
  },


  methods: {

    blackFridayResize() {
      setTimeout(() => {
        const blackFriday = this.$root.$refs.blackFriday;
        document.body.style['padding-top'] = blackFriday.clientHeight + 'px';
      }, 100);
    },

    blackFridayBindClick() {
      const blackFriday = this.$root.$refs.blackFriday;
      const link = blackFriday.querySelector('.link');

      if (link) {
        link.addEventListener('click', this.blackFridayHide);
      }
    },

    blackFridayShow() {
      setTimeout(() => {
        const blackFriday = this.$root.$refs.blackFriday;
        const height = blackFriday.clientHeight;

        blackFriday.style['height'] = 0;
        blackFriday.classList.remove('hidden');

        setTimeout(() => {
          document.body.style['padding-top'] = height + 'px';
          blackFriday.style['height'] = height + 'px';

          setTimeout(() => {
            blackFriday.style.removeProperty('height');
          }, 1000);
        }, 100);
      }, 100);
    },

    blackFridayHide() {
      const blackFriday = this.$root.$refs.blackFriday;
      blackFriday.style['height'] = blackFriday.clientHeight + 'px';

      setTimeout(() => {
        document.body.style['padding-top'] = 0;
        blackFriday.style['height'] = 0;
      }, 100);
    },

  },

};
