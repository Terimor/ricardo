import * as validators from './validators';


export default function() {
  let rules = {
    street: validators.getStreetRules(),
    city: validators.getCityRules(),
    zipcode: validators.getZipCodeRules(),
    country: validators.getCountryRules(),
  };


  return {
    form: rules,
  };
}
