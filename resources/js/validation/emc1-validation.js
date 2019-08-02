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
    number: {
      required,
      type: numeric
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

        console.log(date)

        return dateFns.isValid(date) &&
          val.length === 10 &&
          diff >= 18 &&
          diff < 100 &&
          day < 32 &&
          month < 13
      }
    },
    documentNumber: {
      isValidNumber (val) {
        return checkoutData.countryCode === 'CO' ? val.length === 10 :
        checkoutData.countryCode === 'BR' ? val.length === 14 :
        true
      }
    }
  }
}
export default emc1Validation
