export default {

  computed: {

    cdnUrl() {
      return cdnUrl;
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
