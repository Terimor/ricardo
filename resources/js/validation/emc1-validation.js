import { required, minLength, email, numeric } from 'vuelidate/lib/validators'
import { isValidZipcode } from './validators';
import creditCardType from 'credit-card-type'
import fieldsByCountry from '../resourses/fieldsByCountry';
import * as dateFns from 'date-fns';

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
      minLength: minLength(1)
    },
    lname: {
      required,
      minLength: minLength(1)
    },
    email: {
      required,
      type: email
    },
    phone: {
      required,
      isValidPhone (val) {
        const phoneNumber = libphonenumber.parsePhoneNumberFromString(val || '', this.form.countryCodePhoneField.toUpperCase())

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
      isValidZipcode(value) {
        return isValidZipcode(value, this.form.country || checkoutData.countryCode);
      },
    },
    country: {
      required
    },
    cardNumber: {
      required,
      isValid (val) {
        val = val.replace(/\s/g, '');

        const creditCardTypeList = creditCardType(val);
        const commonRule = val.length > 12 && val.length <= 19;

        if (creditCardTypeList.length === 0) {
          return false;
        }

        return creditCardTypeList[0].lengths.includes(val.length) || commonRule;
      }
    },
    month: {
      required,
    },
    year: {
      required,
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
        return checkoutData.countryCode === 'co' ? val.length === 10 :
          checkoutData.countryCode === 'br' ? val.length === 14 :
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
        if (dynamicConfig[key] || dynamicConfig[key] == null) {
          acc[key] = value
        }

        return acc
      }, {})
  }

  return res
}
export default emc1Validation
