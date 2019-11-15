import { required, minLength, email, numeric } from 'vuelidate/lib/validators';
import postcode from 'postcode-validator';


let emailsCache = {};


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
    isValid(value) {
      const element = document.querySelector('#email-field input');

      if (emailsCache[value] !== undefined) {
        return emailsCache[value];
      }

      if (!value || !element || element === document.activeElement) {
        return true;
      }

      return fetch('/validate-email?email=' + value)
        .then(res => res.json())
        .then(res => {
          emailsCache[value] = res.success;
          return res.success;
        })
        .catch(err => {
          return false;
        });
    },
  };
}
