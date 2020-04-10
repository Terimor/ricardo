export default {

  data: {
    ready_state: document.readyState,
    window_width: window.innerWidth,
    window_scroll_top: document.documentElement.scrollTop,
  },


  created() {
    document.addEventListener('readystatechange', () => {
      this.ready_state = document.readyState;
    });

    addEventListener('resize', () => {
      this.window_width = window.innerWidth;
    });

    addEventListener('scroll', () => {
      this.window_scroll_top = document.documentElement.scrollTop;
    });
  },

};
