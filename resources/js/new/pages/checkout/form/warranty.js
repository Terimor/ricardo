import { required } from 'vuelidate/lib/validators';


export default {

  data() {
    return {
      form: {
        warranty: false,
      },
    };
  },


  validations() {
    return {
      warranty: {
        required,
      },
    };
  },


  methods: {
 
    warranty_change(value) {
      this.form.warranty = value;
    },

  },

};
