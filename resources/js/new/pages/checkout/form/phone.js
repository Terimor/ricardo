import { required } from 'vuelidate/lib/validators';
import logger from '../../../../mixins/logger';


export default {

  data() {
    return {
      country_codes: null,
      form: {
        phone: null,
        phone_country: js_data.country_code,
        phone_code: '',
      },
    };
  },


  mixins: [
    logger
  ],


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


  created() {
    js_deps.wait(['intl_tel_input'], () => {
      this.country_codes = intlTelInputGlobals.getCountryData().reduce((acc, item) => {
        acc[item.iso2] = item.dialCode;
        return acc;
      }, {});
    });

    this.phone_init();
  },


  methods: {

    phone_init() {
      js_deps.wait_for(
        () => {
          return !!window.intlTelInput && !!this.country_codes
            && !!this.$refs.phone_field && !!this.$refs.phone_field.querySelector('input');
        },
        () => {
          if (this.$refs.phone_field && !!this.$refs.phone_field.querySelector('input')) {
            const element = this.$refs.phone_field.querySelector('input');

            if (element.phone_init) {
              return;
            }

            element.phone_init = true;

            window.intlTelInput(element, {
              initialCountry: this.form.phone_country,
              separateDialCode: true,
            });

            this.form.phone_code = this.country_codes[js_data.country_code] || '';

            element.addEventListener('countrychange', () => {
              const country = intlTelInputGlobals.getInstance(element).getSelectedCountryData();

              this.form.phone_country = (country && country.iso2) || js_data.country_code;
              this.form.phone_code = (country && country.dialCode) || this.country_codes[js_data.country_code] || '';

              this.phone_check_padding();
            });

            this.phone_check_padding();
          } else {
            this.log_data('Phone init error', {
              force: true,  
              'window.intlTelInput': !!window.intlTelInput,
              'this.country_codes': !!this.country_codes,
              'this.$refs.phone_field': !!this.$refs.phone_field,
              'this.$refs.phone_field.querySelector': !!this.$refs.phone_field.querySelector('input')
            });
          }
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

    phone_blur() {
      this.check_for_leads_request();
    },

  },

};
