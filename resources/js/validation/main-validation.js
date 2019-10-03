import { required, minLength, email } from 'vuelidate/lib/validators'


const mainvalidation = function () {
  return {
    formData: {
      name: {
        required,
        minLength: minLength(5)
      },
      email: {
        required,
        type: email
      }
    }
  };
};

export default mainvalidation
