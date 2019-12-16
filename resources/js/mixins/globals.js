const searchParams = new URL(location).searchParams;


export default {

  computed: {

    cdn_url() {
      return js_data.cdn_url;
    },

    isAffIDEmpty() {
      return (!searchParams.get('aff_id') || searchParams.get('aff_id') === '0')
        && (!searchParams.get('affid') || searchParams.get('affid') === '0');
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
