const fixed_refs = [
  //'support_toolbar',
  //'black_friday',
  //'christmas',
];


export default {

  methods: {

    scroll_to_ref(ref_name) {
      const element = this.$refs[ref_name];
      this.scroll_to(element);
    },

    scroll_to_selector(selector) {
      const element = document.querySelector(selector);
      this.scroll_to(element);
    },

    scroll_to_error() {
      const element = document.querySelector('.scroll-when-error.invalid');
      this.scroll_to(element);
    },

    scroll_to(element) {
      if (element) {
        let position = element.getBoundingClientRect().top - 20;
        position += Math.max(window.pageYOffset, document.documentElement.scrollTop, document.body.scrollTop);

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

  },

};
