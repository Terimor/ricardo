export default {

  data: {
    document: {
      readyState: document.readyState,
    },
    window_width: window.innerWidth,
    window_scroll_top: document.documentElement.scrollTop,
  },


  created() {
    document.addEventListener('readystatechange', () => {
      this.readyState = document.readyState;
    });

    addEventListener('resize', () => {
      this.window_width = window.innerWidth;
    });

    addEventListener('scroll', () => {
      this.window_scroll_top = document.documentElement.scrollTop;
    });
  },

};
