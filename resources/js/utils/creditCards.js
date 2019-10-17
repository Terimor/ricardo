export const creditCards = {
  americanexpress: {
    title: 'American Express',
    image: window.cdnUrl + '/assets/images/cc-icons/american-express-curved-128px.png',
  },
  aura: {
    title: 'Aura',
    image: window.cdnUrl + '/assets/images/cc-icons/aura-curved.png',
  },
  banccontact: {
    title: 'Banccontact',
    image: window.cdnUrl + '/assets/images/cc-icons/banccontact.png',
  },
  dinersclub: {
    title: 'Diners Club',
    image: window.cdnUrl + '/assets/images/cc-icons/diners-curved.png',
  },
  discover: {
    title: 'Discover',
    image: window.cdnUrl + '/assets/images/cc-icons/discover-curved-128px.png',
  },
  elo: {
    title: 'Elo',
    image: window.cdnUrl + '/assets/images/cc-icons/elo-curved.png',
  },
  eps: {
    title: 'EPS',
    image: window.cdnUrl + '/assets/images/cc-icons/eps-curved.png',
  },
  giropay: {
    title: 'Giropay',
    image: window.cdnUrl + '/assets/images/cc-icons/giropay-curved.png',
  },
  hipercard: {
    title: 'Hipercard',
    image: window.cdnUrl + '/assets/images/cc-icons/hipercard-curved.png',
  },
  ideal: {
    title: 'iDEAL',
    image: window.cdnUrl + '/assets/images/cc-icons/ideal-curved.png',
  },
  jcb: {
    title: 'JCB',
    image: window.cdnUrl + '/assets/images/cc-icons/jcb-curved.png',
  },
  maestro: {
    title: 'Maestro',
    image: window.cdnUrl + '/assets/images/cc-icons/maestro.png',
  },
  mastercard: {
    title: 'Mastercard',
    image: window.cdnUrl + '/assets/images/cc-icons/mastercard-curved-128px.png',
  },
  multibango: {
    title: 'Multibango',
    image: window.cdnUrl + '/assets/images/cc-icons/multibango.png',
  },
  paypal: {
    title: 'Visa',
    image: window.cdnUrl + '/assets/images/cc-icons/payPal.png',
  },
  prezelewy24: {
    title: 'Prezelewy24',
    image: window.cdnUrl + '/assets/images/cc-icons/prezelewy24-curved.png',
  },
  sofort: {
    title: 'Sofort',
    image: window.cdnUrl + '/assets/images/cc-icons/sofort-curved.png',
  },
  visa: {
    title: 'Visa',
    image: window.cdnUrl + '/assets/images/cc-icons/visa-curved-128px.png',
  },
};


export function getCreditCardsAsianCountries() {
  return ['in', 'jp', 'bd', 'cn', 'hk', 'id', 'la', 'mn', 'mm', 'ph', 'sg', 'kr', 'tw', 'th', 'vn'];
}


export function getCreditCardsAvailableList(country, withPaypal) {
  let cards = ['visa', 'mastercard', 'americanexpress', 'discover', 'dinersclub'];

  if (getCreditCardsAsianCountries().indexOf(country) !== -1) {
    cards.push('jcb');
  }

  if (country === 'br') {
    cards = ['mastercard', 'visa', 'americanexpress', 'dinersclub', 'elo', 'hipercard', 'aura'];
  }

  if (country === 'mx') {
    cards = ['mastercard', 'visa', 'americanexpress'];
  }

  if (country === 'co') {
    cards = ['mastercard', 'visa', 'maestro', 'americanexpress', 'dinersclub'];
  }

  if (withPaypal) {
    cards.push('paypal');
  }

  return cards;
}
