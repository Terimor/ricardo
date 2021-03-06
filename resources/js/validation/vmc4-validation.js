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
      street: validators.getStreetRules(),
      city: validators.getCityRules(),
      zipCode: validators.getZipCodeRules(),
      country: validators.getCountryRules(),
    },
  };

  if (this.form.paymentProvider === 'credit-card') {
    rules.stepThree.cardNumber = validators.getCardNumberRules();
    rules.stepThree.cardDate = validators.getCardDateRules();
    rules.stepThree.cvv = validators.getCVVRules();
  }

  if (this.$root.isAffIDEmpty) {
    if (this.form.paymentProvider === 'credit-card') {
      rules.stepThree.cardHolder = validators.getCardHolderRules();
    }

    rules.stepThree.terms = validators.getTermsRules();
  }

  this.$parent.setExtraFieldsValidationRules(vmc4Rules, this.form.paymentProvider);

  if (this.extraFields.state) {
    delete rules.stepThree.state;
  }

  return {
    vmc4Form: vmc4Rules,
    form: rules,
  };
}
