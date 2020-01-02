import { required, minLength, maxLength, email, numeric } from 'vuelidate/lib/validators';
import creditCardType from 'credit-card-type';
//import postcode from 'postcode-validator';
import * as dateFns from 'date-fns';


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

export function getBuildingRules() {
  return {
    required(value) {
      if (this.extraFields.building.pattern && !new RegExp(this.extraFields.building.pattern).test('')) {
        return !!value;
      }

      return true;
    },
    isValid(value) {
      if (this.extraFields.building.pattern) {
        return new RegExp(this.extraFields.building.pattern).test(value);
      }

      return true;
    },
  };
}

export function getComplementRules() {
  return {
    required(value) {
      if (this.extraFields.complement.pattern && !new RegExp(this.extraFields.complement.pattern).test('')) {
        return !!value;
      }

      return true;
    },
    isValid(value) {
      if (this.extraFields.complement.pattern) {
        return new RegExp(this.extraFields.complement.pattern).test(value);
      }

      return true;
    },
  };
}

export function getDistrictRules() {
  return {
    required(value) {
      if (this.extraFields.district.pattern && !new RegExp(this.extraFields.district.pattern).test('')) {
        return !!value;
      }

      return true;
    },
    isValid(value) {
      if (this.extraFields.district.pattern) {
        return new RegExp(this.extraFields.district.pattern).test(value);
      }

      return true;
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
    required(value) {
      if (this.extraFields.state.pattern && !new RegExp(this.extraFields.state.pattern).test('')) {
        return !!value;
      }

      return true;
    },
    isValid(value) {
      if (this.extraFields.state.pattern) {
        return new RegExp(this.extraFields.state.pattern).test(value);
      }

      return true;
    },
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
        ? creditCardTypeList[0].lengths.indexOf(value.length) !== -1 || commonRule
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
    required(value) {
      if (this.extraFields.document_number.pattern) {
        if (typeof this.extraFields.document_number.pattern === 'object') {
          if (this.extraFields.document_number.pattern[this.form.document_type] && !new RegExp(this.extraFields.document_number.pattern[this.form.document_type]).test('')) {
            return !!value;
          }
        } else if (!new RegExp(this.extraFields.document_number.pattern).test('')) {
          return !!value;
        }
      }

      return true;
    },
    isValid(value) {
      if (this.extraFields.document_number.pattern) {
        if (typeof this.extraFields.document_number.pattern === 'object') {
          if (this.extraFields.document_number.pattern[this.form.document_type]) {
            return new RegExp(this.extraFields.document_number.pattern[this.form.document_type]).test(value);
          }
        } else {
          return new RegExp(this.extraFields.document_number.pattern).test(value);
        }
      }

      return true;
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
