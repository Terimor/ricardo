import { required } from 'vuelidate/lib/validators';


export default {

  data() {
    return {
      form: {
        card_holder: null,
      },
    };
  },


  validations() {
    return this.is_affid_empty
      ? {
          card_holder: {
            required,
          },
        }
      : null;
  },

};
