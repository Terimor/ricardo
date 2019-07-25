import { required, minLength, email, numeric } from 'vuelidate/lib/validators'

const emc1Validation = {
  form: {
    deal: {
      required
    },
    variant: {
      required
    },
    isCreditCard: {
      required
    },
    fname: {
      required,
      minLength: minLength(2)
    },
    lname: {
      required,
      minLength: minLength(2)
    },
    email: {
      required,
      type: email
    },
    phone: {
      required,
      type: numeric
    },
    street: {
      required
    },
    city: {
      required
    },
    state: {
      required
    },
    zipcode: {
      required
    },
    country: {
      required
    },
    cardNumber: {
      required
    },
    month: {
      required
    },
    year: {
      required
    },
    cvv: {
      required
    }
  }
}
export default emc1Validation
