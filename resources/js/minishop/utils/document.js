export default {

  data: {
    document: {
      readyState: document.readyState,
    },
  },


  created() {
    document.addEventListener('readystatechange', () => {
      if (this.document) {
        this.document.readyState = document.readyState;
      }
    });
  },

};
