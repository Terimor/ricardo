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
    },
    dateOfBirth: {
      isValidDate (val) {
          const [day, month, year] = val.split('/')
          const date = new Date(year, month - 1, day)

          const diff = dateFns.differenceInYears(
              new Date(),
              new Date(year, month - 1, day)
          )

          return dateFns.isValid(date) && val.length === 10 && diff >= 18 && diff < 100
      }
    }
  }
}
export default emc1Validation
