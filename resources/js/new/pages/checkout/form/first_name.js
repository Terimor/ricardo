import { required } from 'vuelidate/lib/validators';


export default {

  data() {
    return {
      form: {
        first_name: (js_data.customer && js_data.customer.first_name) || null,
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


  methods: {

    first_name_blur() {
      this.check_for_leads_request();
    },

  },

};
