import { required, minLength, email, numeric } from 'vuelidate/lib/validators';
import postcode from 'postcode-validator';


export function isValidZipcode(value, country) {
  if (country === 'gb') {
    country = 'uk';
  }

  return postcode.validate(value, country);
}


export function setEmailValidationRule(rules, name) {
  rules[name] = {
    required,
    type: email,
  };
}

export function setMonthValidationRule(rules, name) {
  rules[name] = {
    required,
  };
}

export function setYearValidationRule(rules, name) {
  rules[name] = {
    required,
  };
}
