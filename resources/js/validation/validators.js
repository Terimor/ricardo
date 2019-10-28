import postcode from 'postcode-validator';


export function isValidZipcode(value, country) {
  try {
    if (country === 'gb') {
      country = 'uk';
    }

    return postcode.validate(value, country);
  }
  catch (err) {
    return true;
  }
}
