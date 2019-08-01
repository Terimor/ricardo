import { required, minLength, email, numeric } from 'vuelidate/lib/validators'
import postcode from 'postcode-validator'
import { parsePhoneNumberFromString } from 'libphonenumber-js'

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
      type: numeric,
      isValidPhone (val) {
        const phoneNumber = parsePhoneNumberFromString(val, checkoutData.countryCode)
        if (phoneNumber) {
          return phoneNumber.isValid()
        }

        return true
      }
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
      required,
      isValidZipcode (val) {
        return postcode.validate(val, checkoutData.countryCode)
      }
    },
    country: {
      required
    },
    cardNumber: {
      type: numeric,
      required
    },
    month: {
      required
    },
    year: {
      required
    },
    cvv: {
      required,
      minLength: minLength(3)
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
