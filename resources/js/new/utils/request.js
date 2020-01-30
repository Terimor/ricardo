let initialized = false;


export default {

  watch: {

    ready_state: {
      handler(value) {
        if (!initialized && this.ready_state === 'complete') {
          this.search_populate_page_links();
          initialized = true;
        }
      },
      immediate: true,
    },

  },


  methods: {

    goto(pathname, exclude = []) {
      pathname = this.search_populate(pathname, exclude);
      document.location = pathname;
    },

    search_populate_page_links() {
      const elements = [].slice.call(document.querySelectorAll('a[href]'));

      for (let element of elements) {
        const href = element.getAttribute('href');

        if (href && href.substr(0, 1) === '/') {
          element.setAttribute('href', this.search_populate(element.href));
        }
      }
    },

    search_populate(url, exclude = []) {
      url = url || '';

      let pathname = url.split('?')[0];
      let search = url.split('?')[1] || '';
      search = search.split('#')[0];

      let url_query_params = search
        .split('&')
        .filter(item => !!item)
        .map(item => item.split('='))
        .reduce((acc, item) => {
          acc[decodeURIComponent(item[0])] = decodeURIComponent(item[1]);
          return acc;
        }, {});

      for (let name of Object.keys(js_query_params)) {
        if (!url_query_params[name] && exclude.indexOf(name) === -1) {
          url_query_params[name] = js_query_params[name];
        }
      }

      let new_search = [];

      for (let name of Object.keys(url_query_params)) {
        new_search.push(encodeURIComponent(name) + '=' + encodeURIComponent(url_query_params[name] || ''));
      }

      new_search = new_search.length > 0
        ? '?' + new_search.join('&')
        : '';

      return pathname + new_search;
    },

    fetch_json(resp) {
      if (!resp.ok) {
        throw new Error(resp.statusText);
      }

      return resp.json();
    },

    fetch_get(url, headers = {}, options = {}) {
      url = this.search_populate(url);

      return fetch(url, {
        method: 'get',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          ...headers,
        },
        ...options,
      });
    },


    fetch_post(url, body = {}, headers = {}, options = {}) {
      for (let name of Object.keys(js_query_params)) {
        if (body[name] === undefined) {
          body[name] = js_query_params[name];
        }
      }

      return fetch(url, {
        method: 'post',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          ...headers,
        },
        body: JSON.stringify({
          ...body,
        }),
        ...options,
      });
    },

  },

};
