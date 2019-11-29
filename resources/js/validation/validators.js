import { required, minLength, email, numeric } from 'vuelidate/lib/validators';
//import postcode from 'postcode-validator';


const searchParams = new URL(location).searchParams;
const tpl = searchParams.get('tpl') || 'emc1';


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

export function setZipCodeValidationRule(rules, name) {
  rules[name] = {
    required,
    minLength(value) {
      if (this.form.country === 'br') {
        if (value && value.replace(/[^0-9]/g, '').length < 8) {
          return false;
        }
      }

      return true;
    },
    /*isValidZipcode(value) {
      let country = this.form.country;

      if (country === 'gb') {
        country = 'uk';
      }

      return postcode.validate(value, country);
    },*/
  };
}

export function setCountryValidationRule(rules, name) {
  rules[name] = {
    required,
  };
}

export function setCardHolderValidationRule(rules, name) {
  if (this.$root.isAffIDEmpty) {
    rules[name] = {
      required,
    };
  }
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

export function setTermsValidationRule(rules, name) {
  if (tpl === 'smc7p' || this.$root.isAffIDEmpty) {
    rules[name] = {
      required,
      isValid(val) {
        return val === true;
      },
    };
  }
}
