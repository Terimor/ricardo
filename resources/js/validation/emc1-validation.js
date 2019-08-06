import { required, minLength, email, numeric } from 'vuelidate/lib/validators'
import postcode from 'postcode-validator'
import { parsePhoneNumberFromString } from 'libphonenumber-js'
import creditCardType from 'credit-card-type'
import fieldsByCountry from '../resourses/fieldsByCountry';

const emc1Validation = function () {
  const allRules = {
    deal: {
      required
    },
    variant: {
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
        const phoneNumber = parsePhoneNumberFromString(val || '', this.form.countryCodePhoneField)
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
        return postcode.validate(val, this.form.country || checkoutData.countryCode)
      }
    },
    country: {
      required
    },
    cardNumber: {
      type: numeric,
      required,
      isValid (val) {
        const creditCardTypeList = creditCardType(val)

        const commonRule = val.length > 12 && val.length <= 19

        if (creditCardTypeList.length === 1) {
          return creditCardTypeList[0].lengths.includes(val.length) || commonRule
        }

        return commonRule
      }
    },
    month: {
      required,
      isValid (val) {
        return val > 0 && val <= 12
      }
    },
    year: {
      required,
      isValid (val) {
        return dateFns.isFuture(new Date(val, this.form.month))
      }
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

  const dynamicConfig = {
      ...fieldsByCountry(checkoutData.countryCode),
      documentNumber: this.form.paymentType === 'credit-card',
      cardNumber: this.form.paymentType === 'credit-card',
      month: this.form.paymentType === 'credit-card',
      year: this.form.paymentType === 'credit-card',
      cvv: this.form.paymentType === 'credit-card',
  }

  const res = {
    form: Object.entries(allRules)
      .reduce((acc, [key, value]) => {
          console.log()
        if (dynamicConfig[key] || dynamicConfig[key] == null) {
          acc[key] = value
        }

        return acc
      }, {})
  }

  return res
}
export default emc1Validation
