import { required } from 'vuelidate/lib/validators';


export default {

  data() {
    return {
      form: {
        country: js_data.countries.indexOf(js_data.country_code) !== -1
          ? js_data.country_code
          : null,
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

};
