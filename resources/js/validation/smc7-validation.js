import { required, minLength, email, numeric } from 'vuelidate/lib/validators'
import * as validators from './validators';


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
    }
  };

  validators.setEmailValidationRule.call(this, config.form, 'email');
  validators.setPhoneValidationRule.call(this, config.form, 'phone');
  validators.setZipCodeValidationRule.call(this, config.form, 'zipCode');
  validators.setCountryValidationRule.call(this, config.form, 'country');
  validators.setCardHolderValidationRule.call(this, config.form, 'cardHolder');
  validators.setCardNumberValidationRule.call(this, config.form, 'cardNumber');
  validators.setCardDateValidationRule.call(this, config.form, 'cardDate');
  validators.setCVVValidationRule.call(this, config.form, 'cvv');
  validators.setTermsValidationRule.call(this, config.form, 'terms');

  this.setExtraFieldsValidationRules(config.form);

  return config;
};

export default smc7validation
