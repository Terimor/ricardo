import { required, minLength, maxLength, email, numeric } from 'vuelidate/lib/validators';
import creditCardType from 'credit-card-type';
//import postcode from 'postcode-validator';
import * as dateFns from 'date-fns';


const searchParams = new URL(location).searchParams;
const tpl = searchParams.get('tpl') || 'emc1';


export function getDealRules() {
  return {
    required,
  };
}

export function getVariantRules() {
  return {
    required,
  };
}

export function getInstallmentsRules() {
  return {
    required,
  };
}

export function getFirstNameRules() {
  return {
    required,
  };
}

export function getLastNameRules() {
  return {
    required,
  };
}

export function getEmailRules() {
  return {
    required,
    type: email,
  };
}

export function getPhoneRules() {
  return {
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

export function getStreetRules() {
  return {
    required,
  };
}

export function getDistrictRules() {
  return {
    required,
    isValid(value) {
      return new RegExp(this.extraFields.district.pattern || '/.+/').test(value);
    },
  };
}

export function getCityRules() {
  return {
    required,
  };
}

export function getStateRules() {
  return {
    required,
  };
}

export function getZipCodeRules() {
  return {
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

export function getCountryRules() {
  return {
    required,
  };
}

export function getCardTypeRules() {
  return {
    required,
  };
}

export function getCardHolderRules() {
  return {
    required,
  };
}

export function getCardNumberRules() {
  return {
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

export function getCardDateRules() {
  return {
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

export function getCVVRules() {
  return {
    required,
    numeric,
    minLength: minLength(3),
    maxLength: maxLength(4),
  };
}

export function getDocumentTypeRules() {
  return {
    required,
  };
}

export function getDocumentNumberRules() {
  return {
    required,
    isValid(value) {
      const pattern = this.extraFields.document_number.pattern
        ? typeof this.extraFields.document_number.pattern === 'object'
          ? this.extraFields.document_number.pattern[this.form.document_type] || ''
          : this.extraFields.document_number.pattern
        : '/.+/';

      return new RegExp(pattern).test(value);
    },
  };
}

export function getTermsRules() {
  return {
    required,
    isValid(value) {
      return value === true;
    },
  };
}
