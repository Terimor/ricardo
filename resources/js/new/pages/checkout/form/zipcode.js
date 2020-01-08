import { required } from 'vuelidate/lib/validators';
//import postcode from 'postcode-validator';

let cache = {};


export default {

  data() {
    return {
      form: {
        zipcode: null,
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

        fetch('/address-by-zip?zipcode=' + value)
          .then(res => res.json())
          .then(res => {
            cache[value] = res;
            this.zipcode_address = res;
            this.is_loading.address = false;
          })
          .catch(err => {
            this.is_loading.address = false;
          });
      }
    },

  },

};
