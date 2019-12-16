export default {

  computed: {

    cdn_url() {
      return js_data.cdn_url;
    },

    isAffIDEmpty() {
      return (!js_query_params.aff_id || js_query_params.aff_id === '0')
        && (!js_query_params.affid || js_query_params.affid === '0');
    },

    sortedCountryList() {
      return checkoutData.countries
        .map(name => {
          const label = this.$t('country.' + name);

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

};
