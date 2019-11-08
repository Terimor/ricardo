import { required, minLength, email, numeric } from 'vuelidate/lib/validators'
//import { isValidZipcode } from './validators';
import creditCardType from 'credit-card-type'
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
        if (window.libphonenumber) {
          const phoneNumber = libphonenumber.parsePhoneNumberFromString(val || '', this.form.countryCodePhoneField.toUpperCase())

          if (phoneNumber) {
            return phoneNumber.isValid()
          }
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
      /*isValidZipcode(value) {
        return isValidZipcode(value, this.form.country || checkoutData.countryCode);
      },*/
    },
    country: {
      required
    },
    cardNumber: {
      required,
      isValid (val) {
        val = val ? val.replace(/\s/g, '') : '';

        const creditCardTypeList = creditCardType(val);
        const commonRule = val.length > 12 && val.length <= 19;

        return creditCardTypeList.length > 0
          ? creditCardTypeList[0].lengths.includes(val.length) || commonRule
          : false;
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
  }
/*
  if (this.form.country === 'de') {
    allRules.dateOfBirth = {
      isValidDate (val) {
        const [day, month, year] = (val || '').split('/')
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
    };
  }
*/
  if (this.extraFields.district) {
    allRules.district = {
      required,
      isValid(value) {
        return new RegExp(this.extraFields.district.pattern).test(value);
      },
    };
  }

  if (this.extraFields.card_type) {
    allRules.cardType = {
      required,
    };
  }

  if (this.extraFields.document_type) {
    allRules.documentType = {
      required,
    };
  }

  if (this.extraFields.document_number) {
    allRules.documentNumber = {
      isValidNumber(value) {
        const pattern = typeof this.extraFields.document_number.pattern === 'object'
          ? this.extraFields.document_number.pattern[this.form.documentType] || ''
          : this.extraFields.document_number.pattern;

        return new RegExp(pattern).test(value);
      }
    };
  }

  return { form: allRules };
}
export default emc1Validation
