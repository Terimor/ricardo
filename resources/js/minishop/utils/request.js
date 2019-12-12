export default {

  mounted() {
    this.searchPopulatePageLinks();
  },


  computed: {

    searchParams() {
      return new URLSearchParams(location.href);
    },

  },


  methods: {

    goto(pathname, exclude) {
      pathname = this.searchPopulate(pathname, exclude);
      document.location = pathname;
    },

    searchPopulate(pathname, exclude = []) {
      let url = new URL(pathname, location);

      if (this.searchParams) {
        this.searchParams.forEach((value, key) => {
          if (!url.searchParams.has(key) && !exclude.includes(key)) {
            url.searchParams.set(key, value);
          }
        });
      }

      return url.pathname + url.search + url.hash;
    },

    searchPopulatePageLinks() {
      const elements = [].slice.call(document.querySelectorAll('a[href]'));

      for (let element of elements) {
        const href = element.getAttribute('href');

        if (href && href.substr(0, 1) === '/') {
          element.setAttribute('href', this.searchPopulate(element.href));
        }
      }
    },

  },

};
