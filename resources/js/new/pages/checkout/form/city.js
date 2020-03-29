import { required } from 'vuelidate/lib/validators';


export default {

  data() {
    return {
      form: {
        city: (js_data.customer && js_data.customer.address && js_data.customer.address.city) || null,
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
