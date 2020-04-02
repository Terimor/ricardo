import { required } from 'vuelidate/lib/validators';


export default {

  data() {
    return {
      form: {
        street: null,
      },
    };
  },


  validations() {
    return {
      street: {
        required,
      },
    };
  },

};
