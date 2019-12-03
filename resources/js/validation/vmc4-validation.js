import { required, minLength, email, numeric } from 'vuelidate/lib/validators'
import * as validators from './validators';


export default function() {
  let vmc4Rules = {
    deal: validators.getDealRules(),
    variant: validators.getVariantRules(),
  };

  let rules = {
    stepTwo: {
      fname: validators.getFirstNameRules(),
      lname: validators.getLastNameRules(),
      email: validators.getEmailRules(),
      phone: validators.getPhoneRules(),
    },
    stepThree: {
      city: validators.getCityRules(),
      state: validators.getStateRules(),
      zipCode: validators.getZipCodeRules(),
      country: validators.getCountryRules(),
      cardNumber: validators.getCardNumberRules(),
      cardDate: validators.getCardDateRules(),
      cvv: validators.getCVVRules(),
    },
  };

  if (this.$root.isAffIDEmpty) {
    rules.stepThree.cardHolder = validators.getCardHolderRules();
    rules.stepThree.terms = validators.getTermsRules();
  }

  this.$parent.setExtraFieldsValidationRules(vmc4Rules);

  if (this.extraFields.state) {
    delete rules.stepThree.state;
  }

  return {
    vmc4Form: vmc4Rules,
    form: rules,
  };
}
