export default {

  computed: {

    cdn_url() {
      return js_data.cdn_url;
    },

    paypalEnabled() {
      return !!document.querySelector('#paypal-script');
    },

    isAffIDEmpty() {
      return (!js_query_params.aff_id || js_query_params.aff_id === '0')
        && (!js_query_params.affid || js_query_params.affid === '0');
    },

    sortedCountryList() {
      return js_data.countries
        .map(name => {
          const label = this.$t('country.' + name) || '';

          return {
            value: name,
            lc: label.toLowerCase(),
            text: label,
            label,
          };
        })
        .sort((a, b) => {
          if (a.lc > b.lc) return 1;
          if (a.lc < b.lc) return -1;
          return 0;
        });
    },

  },

  methods: {

    lazyload_update() {
      if (window.js_deps && js_deps.wait_for) {
        js_deps.wait_for(
          () => window.lazyLoadInstance,
          () => {
            [].forEach.call(document.querySelectorAll('img.lazy.loaded'), element => {
              element.removeAttribute('data-was-processed');
            });

            lazyLoadInstance.update();
          },
        );
      }
    },

  },

};
