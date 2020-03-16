import * as validators from './validators';


export default function() {
  let rules = {
    deal: validators.getDealRules(),
    variant: validators.getVariantRules(),
    fname: validators.getFirstNameRules(),
    lname: validators.getLastNameRules(),
    email: validators.getEmailRules(),
    phone: validators.getPhoneRules(),
    streetAndNumber: validators.getStreetRules(),
    city: validators.getCityRules(),
    zipCode: validators.getZipCodeRules(),
    country: validators.getCountryRules(),
  };

  if (this.form.paymentProvider === 'credit-card') {
    rules.cardNumber = validators.getCardNumberRules();
    rules.cardDate = validators.getCardDateRules();
    rules.cvv = validators.getCVVRules();
  }

  if (this.$root.isAffIDEmpty) {
    if (this.form.paymentProvider === 'credit-card') {
      rules.cardHolder = validators.getCardHolderRules();
    }

    rules.terms = validators.getTermsRules();
  }

  this.setExtraFieldsValidationRules(rules);

  return {
    form: rules,
  };
}
