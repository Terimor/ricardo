import { required } from 'vuelidate/lib/validators';


export default {

  data() {
    return {
      form: {
        phone: null,
        phone_country: js_data.country_code,
        phone_code: '',
      },
    };
  },


  validations() {
    return {
      phone: {
        required,
        valid(value) {
          value = value || '';

          if (/^\+/.test(value) || value.length === 1) {
            return false;
          }

          if (window.libphonenumber) {
            const phoneNumber = libphonenumber.parsePhoneNumberFromString(value, this.form.phone_country.toUpperCase());

            if (!phoneNumber || !phoneNumber.isValid()) {
              return false;
            }
          }

          return true;
        },
      },
    };
  },


  methods: {

    phone_init() {
      js_deps.wait_for(
        () => {
          return !!window.intlTelInput && !!window.intlTelInputGlobals
            && !!this.$refs.phone_field && !!this.$refs.phone_field.querySelector('input');
        },
        () => {
          setTimeout(() => {
            const element = this.$refs.phone_field.querySelector('input');

            if (element.phone_init) {
              return;
            }

            element.phone_init = true;

            window.intlTelInput(element, {
              initialCountry: this.form.phone_country,
              separateDialCode: true,
            });

            const phone_codes = window.intlTelInputGlobals.getCountryData()
              .reduce((acc, item) => {
                acc[item.iso2] = item.dialCode;
                return acc;
              }, {});

            this.form.phone_code = phone_codes[js_data.country_code] || '';

            element.addEventListener('countrychange', () => {
              const country = intlTelInputGlobals.getInstance(element).getSelectedCountryData();

              this.form.phone_country = country.iso2 || js_data.country_code;
              this.form.phone_code = country.dialCode || phone_codes[js_data.country_code] || '';

              this.phone_check_padding();
            });

            this.phone_check_padding();
          }, 100);
        },
      );
    },

    phone_check_padding() {
      const element = this.$refs.phone_field.querySelector('input');

      if (element && document.querySelector('html[dir="rtl"]') && element.style.paddingLeft) {
        element.style.paddingRight = element.style.paddingLeft;
      }
    },

    phone_input() {
      let value = this.form.phone || '';
      value = value.replace(/^0/, '');

      this.form.phone = value;
    },

  },

};
