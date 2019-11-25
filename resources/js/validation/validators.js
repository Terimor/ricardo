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

export function setPhoneValidationRule(rules, name) {
  rules[name] = {
    required,
    isValid(val) {
      val = val || '';

      if (/^\+/.test(val) || val.length === 1) {
        return false;
      }

      if (window.libphonenumber) {
        const phoneNumber = libphonenumber.parsePhoneNumberFromString(val, this.form.countryCodePhoneField.toUpperCase());

        if (!phoneNumber || !phoneNumber.isValid()) {
          return false;
        }
      }

      return true;
    },
  };
}

export function setCountryValidationRule(rules, name) {
  rules[name] = {
    required,
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
