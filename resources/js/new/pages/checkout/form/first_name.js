import { required } from 'vuelidate/lib/validators';


export default {

  data() {
    return {
      form: {
        first_name: null,
      },
    };
  },


  validations() {
    return {
      first_name: {
        required,
      },
    };
  },

};
