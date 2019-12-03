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
    state: validators.getStateRules(),
    zipCode: validators.getZipCodeRules(),
    country: validators.getCountryRules(),
    cardNumber: validators.getCardNumberRules(),
    cardDate: validators.getCardDateRules(),
    cvv: validators.getCVVRules(),
  };

  if (this.$root.isAffIDEmpty) {
    rules.cardHolder = validators.getCardHolderRules();
    rules.terms = validators.getTermsRules();
  }

  this.setExtraFieldsValidationRules(rules);

  return {
    form: rules,
  };
}
