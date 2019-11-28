import { required, minLength, email, numeric } from 'vuelidate/lib/validators'
import * as validators from './validators';
import creditCardType from 'credit-card-type'
import * as dateFns from 'date-fns';


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
      cvv: {
        required,
        minLength: minLength(3)
      },
    }
  };

  validators.setEmailValidationRule.call(this, config.form, 'email');
  validators.setPhoneValidationRule.call(this, config.form, 'phone');
  validators.setCountryValidationRule.call(this, config.form, 'country');
  validators.setCardHolderValidationRule.call(this, config.form, 'cardHolder');
  validators.setMonthValidationRule.call(this, config.form, 'month');
  validators.setYearValidationRule.call(this, config.form, 'year');
  validators.setTermsValidationRule.call(this, config.form, 'terms');

  this.setExtraFieldsValidationRules(config.form);

  return config;
};

export default smc7validation
