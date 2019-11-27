export default {

  data: {
    document: {
      readyState: document.readyState,
    },
  },


  created() {
    document.addEventListener('readystatechange', () => {
      this.document.readyState = document.readyState;
    });
  },

};
