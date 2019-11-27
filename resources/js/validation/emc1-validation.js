import { required, minLength, email, numeric } from 'vuelidate/lib/validators'
import * as validators from './validators';
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

  validators.setEmailValidationRule(allRules, 'email');
  validators.setPhoneValidationRule(allRules, 'phone');
  validators.setCountryValidationRule(allRules, 'country');
  validators.setCardHolderValidationRule(allRules, 'cardHolder');
  validators.setMonthValidationRule(allRules, 'month');
  validators.setYearValidationRule(allRules, 'year');
  validators.setTermsValidationRule(allRules, 'terms');

  this.setExtraFieldsValidationRules(allRules);

  return { form: allRules };
}
export default emc1Validation
