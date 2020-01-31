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

};
