import { required } from 'vuelidate/lib/validators';
//import postcode from 'postcode-validator';

let cache = {};


export default {

  data() {
    return {
      form: {
        zipcode: (js_data.customer && js_data.customer.address && js_data.customer.address.zip) || null,
      },
      zipcode_address: null,
    };
  },


  validations() {
    return {
      zipcode: {
        required,
        min_length(value) {
          value = value || '';

          if (this.form.country === 'br') {
            return value.length === 8;
          }

          return true;
        },
        /*valid(value) {
          let country = this.form.country;

          if (country === 'gb') {
            country = 'uk';
          }

          return postcode.validate(value, country);
        },*/
      },
    };
  },


  watch: {

    zipcode_address(value) {
      if (value && (value.city || value.state || value.address)) {
        this.form.city = value.city || null;
        this.form.state = value.state || null;
        this.form.street = value.address || null;
      }
    },

  },


  computed: {

    zipcode_label() {
      return this.t('checkout.payment_form.zipcode', {}, {
        country: this.form.country,
      });
    },

  },


  methods: {

    zipcode_input() {
      this.zipcode_address = null;
      this.form.zipcode = this.form.zipcode.replace(/[^A-z0-9]/g, '')
      this.form.zipcode = this.form.zipcode.substr(0, 12);
    },

    zipcode_blur() {
      const value = this.form.zipcode || '';

      if (this.form.country === 'br') {
        if (this.$v.form.zipcode.$invalid) {
          return;
        }

        if (cache[value]) {
          this.zipcode_address = cache[value];
          return
        }

        this.is_loading.address = true;

        this.fetch_get('/address-by-zip?zipcode=' + value)
          .then(this.fetch_json)
          .then(body => {
            cache[value] = body;
            this.zipcode_address = body;
            this.is_loading.address = false;
          })
          .catch(err => {
            this.is_loading.address = false;
          });
      }
    },

  },

};
