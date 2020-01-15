const fixed_refs = [
  'support_toolbar',
  'black_friday',
  'christmas',
];


export default {

  methods: {

    scroll_to(element) {
      if (element) {
        let position = element.getBoundingClientRect().top - 20;
        position += document.documentElement.scrollTop;

        for (let ref_name of fixed_refs) {
          if (this.$root.$refs[ref_name]) {
            position -= this.$root.$refs[ref_name].clientHeight;
          }
        }

        if (window.scrollTo) {
          if (document.documentElement.style.scrollBehavior !== undefined) {
            scrollTo({
              top: position,
              behavior: 'smooth',
            });
          } else {
            scrollTo(0, position);
          }
        }
      }
    },

    scroll_to_ref(ref_name) {
      this.scroll_to(this.$refs[ref_name]);
    },

  },

};
