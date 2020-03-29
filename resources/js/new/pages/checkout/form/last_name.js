import { required } from 'vuelidate/lib/validators';


export default {

  data() {
    return {
      form: {
        last_name: (js_data.customer && js_data.customer.last_name) || null,
      },
    };
  },


  validations() {
    return {
      last_name: {
        required,
      },
    };
  },


  methods: {

    last_name_blur() {
      this.check_for_leads_request();
    },

  },

};
