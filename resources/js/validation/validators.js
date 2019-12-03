import { required, minLength, maxLength, email, numeric } from 'vuelidate/lib/validators';
import creditCardType from 'credit-card-type';
//import postcode from 'postcode-validator';
import * as dateFns from 'date-fns';


const searchParams = new URL(location).searchParams;
const tpl = searchParams.get('tpl') || 'emc1';


export function setEmailValidationRule(rules, name) {
  rules[name] = {
    required,
    type: email,
  };
}

export function setPhoneValidationRule(rules, name) {
  rules[name] = {
    required,
    isValid(value) {
      value = value || '';

      if (/^\+/.test(value) || value.length === 1) {
        return false;
      }

      if (window.libphonenumber) {
        const phoneNumber = libphonenumber.parsePhoneNumberFromString(value, this.form.countryCodePhoneField.toUpperCase());

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
      value = value || '';

      if (this.form.country === 'br') {
        if (value.replace(/[^0-9]/g, '').length < 8) {
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

export function setCardNumberValidationRule(rules, name) {
  rules[name] = {
    required,
    isValid(value) {
      value = value || '';
      value = value.replace(/[^0-9]/g, '');

      const creditCardTypeList = creditCardType(value);
      const commonRule = value.length > 12 && value.length <= 19;

      return creditCardTypeList.length > 0
        ? creditCardTypeList[0].lengths.includes(value.length) || commonRule
        : false;
    },
  };
}

export function setCardDateValidationRule(rules, name) {
  rules[name] = {
    required,
    isValid(value) {
      value = value || '';

      if (!/^[0-9]{2}\/[0-9]{2}$/.test(value)) {
        return false;
      }

      const month = +value.split('/')[0];
      const year = +value.split('/')[1];

      return month >= 1 && month <= 12 && year >= 0 && year <= 99;
    },
    isNotExpired(value) {
      value = value || '';

      const month = (+value.split('/')[0] - 1) || 0;
      const year = 2000 + (+value.split('/')[1] || 0);

      return dateFns.isFuture(new Date(year, month));
    },
  };
}

export function setCVVValidationRule(rules, name) {
  rules[name] = {
    required,
    numeric,
    minLength: minLength(3),
    maxLength: maxLength(4),
  };
}

export function setTermsValidationRule(rules, name) {
  if (tpl === 'smc7p' || this.$root.isAffIDEmpty) {
    rules[name] = {
      required,
      isValid(value) {
        return value === true;
      },
    };
  }
}
