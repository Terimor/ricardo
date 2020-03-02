export default {

  data: {
    ready_state: document.readyState,
    window_width: window.innerWidth,
  },


  created() {
    document.addEventListener('readystatechange', () => {
      this.ready_state = document.readyState;
    });

    addEventListener('resize', () => {
      this.window_width = window.innerWidth;
    })
  },

};
