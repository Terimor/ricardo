export default function (countryCode) {
  return {
    documentNumber: countryCode === 'br' || countryCode === 'co',
    dateOfBirth: countryCode === 'de',
    number: countryCode === 'br',
    complemento: countryCode === 'br',
  }
}
