import { required } from 'vuelidate/lib/validators';


export default {

  data() {
    return {
      form: {
        country: js_data.country_code,
      },
    };
  },


  validations() {
    return {
      country: {
        required,
      },
    };
  },


  computed: {

    country_items() {
      return js_data.countries
        .map(name => {
          const label = this.t('country.' + name);

          return {
            value: name,
            lc: label.toLowerCase(),
            label,
          };
        })
        .sort((a, b) => {
          if (a.lc < b.lc) return -1;
          if (a.lc > b.lc) return 1;
          return 0;
        });
    },

  },

};
