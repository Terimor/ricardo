import { required } from 'vuelidate/lib/validators';


export default {

  data() {
    return {
      form: {
        street: (js_data.customer && js_data.customer.address && js_data.customer.address.street) || null,
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
