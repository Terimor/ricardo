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

    restore_customer(callback, phone_callback) {
      let fname = null;
      let lname = null;
      let email = null;
      let countryCodePhoneField = null;
      let phone = null;
      let street = null;
      let city = null;
      let zipcode = null;

      let country = js_data.countries.indexOf(js_data.country_code) !== -1
        ? js_data.country_code
        : null;

      if (!js_data.customer) {
        return;
      }

      fname = js_data.customer.first_name || fname;
      lname = js_data.customer.last_name || lname;
      email = js_data.customer.email || email;
      street = js_data.customer.address && js_data.customer.address.street || street;
      city = js_data.customer.address && js_data.customer.address.city || city;
      zipcode = js_data.customer.address && js_data.customer.address.zip || zipcode;

      country = js_data.customer.address && js_data.customer.address.country && js_data.countries.indexOf(js_data.customer.address.country) !== -1
        ? js_data.customer.address.country
        : country;

      callback(fname, lname, email, street, city, zipcode, country);
      
      if (js_data.customer.phone && js_data.customer.address && js_data.customer.address.country) {
        phone = js_data.customer.phone;

        js_deps.wait_for(
          () => !!window.intlTelInputGlobals,
          () => {
            if (window.intlTelInputGlobals) {
              const country_codes = intlTelInputGlobals.getCountryData().reduce((acc, item) => {
                acc[item.iso2] = item.dialCode;
                return acc;
              }, {});

              const country_code = country_codes[js_data.customer.address.country];

              if (country_code) {
                if (js_data.customer.phone.indexOf(country_code) === 0) {
                  countryCodePhoneField = js_data.customer.address.country;
                  phone = js_data.customer.phone.substr(country_code.length);
                }
              }
            }

            phone_callback(countryCodePhoneField, phone);
          },
        );
      }
    },

  },

};
