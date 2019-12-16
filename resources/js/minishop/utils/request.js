export default {

  mounted() {
    this.searchPopulatePageLinks();
  },


  methods: {

    goto(pathname, exclude) {
      pathname = this.searchPopulate(pathname, exclude);
      document.location = pathname;
    },

    searchPopulate(pathname, exclude = []) {
      const url = new URL(pathname, location);

      let url_query_params = url.search
        .substr(1).split('&').filter(item => !!item).map(item => item.split('='))
        .reduce((acc, item) => {
          acc[decodeURIComponent(item[0])] = decodeURIComponent(item[1]);
          return acc;
        }, {});

      for (let name of Object.keys(js_query_params)) {
        if (!url_query_params[name] && !exclude.includes(name)) {
          url_query_params[name] = js_query_params[name];
        }
      }

      let url_search = [];

      for (let name of Object.keys(url_query_params)) {
        url_search.push(encodeURIComponent(name) + '=' + encodeURIComponent(url_query_params[name] || ''));
      }

      url_search = url_search.length > 0
        ? '?' + url_search.join('&')
        : '';

      return url.pathname + url_search;
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
