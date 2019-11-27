export default {

  data: {
    searchParams: new URL(location).searchParams,
  },


  mounted() {
    this.searchPopulatePageLinks();
  },


  methods: {

    goto(pathname) {
      pathname = this.searchPopulate(pathname);
      document.location = pathname;
    },

    searchPopulate(pathname) {
      let url = new URL(pathname, location);

      this.searchParams.forEach((value, key) => {
        if (!url.searchParams.has(key)) {
          url.searchParams.set(key, value);
        }
      });

      return url.pathname + url.search + url.hash;
    },

    searchPopulatePageLinks() {
      const elements = [...document.querySelectorAll('a[href]')];

      for (let element of elements) {
        const href = element.getAttribute('href');

        if (href && href.substr(0, 1) === '/') {
          element.setAttribute('href', this.searchPopulate(element.href));
        }
      }
    },

  },

};
