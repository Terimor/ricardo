import { required, minLength, email, numeric } from 'vuelidate/lib/validators'
import postcode from 'postcode-validator'
import { parsePhoneNumberFromString } from 'libphonenumber-js'
import creditCardType from 'credit-card-type'
import fieldsByCountry from '../resourses/fieldsByCountry';


const smc7validation = function () {
  const config = {
    form: {
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
        isValidPhone (val) {

          const phoneNumber = parsePhoneNumberFromString(val || '', this.form.countryCodePhoneField)

          if (phoneNumber) {
            return phoneNumber.isValid()
          }

          return true
        }
      },
      streetAndNumber: {
        required
      },
      city: {
        required
      },
      state: {
        required
      },
      zipCode: {
        required,
        isValidZipcode (val) {
          return postcode.validate(val, this.form.country || checkoutData.countryCode)
        }
      },
      cardNumber: {
        type: numeric,
        required,
        isValid (val) {
          const creditCardTypeList = creditCardType(val);

          const commonRule = val.length > 12 && val.length <= 19;

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
      country: {
        required
      },
      cvv: {
        required,
        minLength: minLength(3)
      },
    }
  };

  return config
};

export default smc7validation
