import { required } from 'vuelidate/lib/validators';


export default {

  data() {
    return {
      form: {
        city: null,
      },
    };
  },


  validations() {
    return {
      city: {
        required,
      },
    };
  },

};
