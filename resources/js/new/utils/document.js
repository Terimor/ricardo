export default {

  data: {
    ready_state: document.readyState,
  },


  created() {
    document.addEventListener('readystatechange', () => {
      this.ready_state = document.readyState;
    });
  },

};
