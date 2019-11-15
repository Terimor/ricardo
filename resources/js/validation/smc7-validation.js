import { required, minLength, email, numeric } from 'vuelidate/lib/validators'
import { setEmailValidationRule } from './validators';
import creditCardType from 'credit-card-type'
import * as dateFns from 'date-fns';


const searchParams = new URL(location).searchParams;

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
        minLength: minLength(1)
      },
      lname: {
        required,
        minLength: minLength(1)
      },
      phone: {
        required,
        isValidPhone (val) {
          if (window.libphonenumber) {
            const phoneNumber = libphonenumber.parsePhoneNumberFromString(val || '', this.form.countryCodePhoneField.toUpperCase())

            if (phoneNumber) {
              return phoneNumber.isValid()
            }
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
        /*isValidZipcode(value) {
          return isValidZipcode(value, this.form.country || checkoutData.countryCode);
        },*/
      },
      cardNumber: {
        required,
        isValid (val) {
          val = val ? val.replace(/\s/g, '') : '';

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
      country: {
        required
      },
      cvv: {
        required,
        minLength: minLength(3)
      },
    }
  };

  if (searchParams.get('tpl') === 'smc7p') {
    config.form.terms = {
      isValid(value) {
        return value === true;
      },
    };
  }

  setEmailValidationRule(config.form, 'email');

  this.setExtraFieldsValidationRules(config.form);

  return config;
};

export default smc7validation
