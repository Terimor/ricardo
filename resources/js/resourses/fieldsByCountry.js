export default function (countryCode) {
  return {
    documentNumber: countryCode === 'BR' || countryCode === 'CO',
    dateOfBirth: countryCode === 'DE',
    number: countryCode === 'BR',
    complemento: countryCode === 'BR',
    state: countryCode === 'BR' || countryCode === 'MX',
  }
}
