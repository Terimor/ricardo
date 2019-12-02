import wait from '../utils/wait';


export default {

  mounted() {
    if (!window.christmasEnabled) {
      return;
    }

    this.christmasShow();
    this.christmasBindClick();

    addEventListener('resize', this.christmasResize);
  },


  methods: {

    christmasResize() {
      setTimeout(() => {
        const christmas = this.$root.$refs.christmas;
        document.body.style['padding-top'] = christmas.clientHeight + 'px';
      }, 100);
    },

    christmasBindClick() {
      const christmas = this.$root.$refs.christmas;
      const link = christmas.querySelector('.link');

      if (link) {
        link.addEventListener('click', this.christmasHide);
      }
    },

    christmasShow() {
      const christmas = this.$root.$refs.christmas;

      wait(
        () => christmas.clientHeight > 0,
        () => {
          const height = christmas.clientHeight;

          christmas.style['height'] = 0;
          christmas.classList.remove('hidden');

          setTimeout(() => {
            document.body.style['padding-top'] = height + 'px';
            christmas.style['height'] = height + 'px';

            setTimeout(() => {
              christmas.style.removeProperty('height');
            }, 1000);
          }, 100);
        },
      );
    },

    christmasHide() {
      const christmas = this.$root.$refs.christmas;
      christmas.style['height'] = christmas.clientHeight + 'px';

      setTimeout(() => {
        document.body.style['padding-top'] = 0;
        christmas.style['height'] = 0;
      }, 100);
    },

  },

};
