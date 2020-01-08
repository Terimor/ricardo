import wait from '../utils/wait';


export default {

  mounted() {
    if (!js_data.is_black_friday) {
      return;
    }

    this.blackFridayShow();
    this.blackFridayBindClick();

    addEventListener('resize', this.blackFridayResize);
  },


  methods: {

    blackFridayResize() {
      setTimeout(() => {
        const blackFriday = this.$root.$refs.black_friday;
        document.body.style['padding-top'] = blackFriday.clientHeight + 'px';
      }, 100);
    },

    blackFridayBindClick() {
      const blackFriday = this.$root.$refs.black_friday;
      const link = blackFriday.querySelector('.link');

      if (link) {
        link.addEventListener('click', this.blackFridayHide);
      }
    },

    blackFridayShow() {
      const blackFriday = this.$root.$refs.black_friday;

      wait(
        () => blackFriday.clientHeight > 0,
        () => {
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
        },
      );
    },

    blackFridayHide() {
      const blackFriday = this.$root.$refs.black_friday;
      blackFriday.style['height'] = blackFriday.clientHeight + 'px';

      setTimeout(() => {
        document.body.style['padding-top'] = 0;
        blackFriday.style['height'] = 0;
      }, 100);
    },

  },

};
