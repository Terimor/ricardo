import { required, minLength, email, numeric } from 'vuelidate/lib/validators'
import * as validators from './validators';
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

  validators.setEmailValidationRule.call(this, allRules, 'email');
  validators.setPhoneValidationRule.call(this, allRules, 'phone');
  validators.setZipCodeValidationRule.call(this, allRules, 'zipcode');
  validators.setCountryValidationRule.call(this, allRules, 'country');
  validators.setCardHolderValidationRule.call(this, allRules, 'cardHolder');
  validators.setCardNumberValidationRule.call(this, allRules, 'cardNumber');
  validators.setCardDateValidationRule.call(this, allRules, 'cardDate');
  validators.setCVVValidationRule.call(this, allRules, 'cvv');
  validators.setTermsValidationRule.call(this, allRules, 'terms');

  this.setExtraFieldsValidationRules(allRules);

  return { form: allRules };
}
export default emc1Validation
