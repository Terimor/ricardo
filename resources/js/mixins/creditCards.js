export default {

  computed: {

    creditCards() {
      return {
        americanexpress: { title: 'American Express', image: '/images/cc-icons/american-express-curved-128px.png' },
        aura: { title: 'Aura', image: '/images/cc-icons/aura-curved.png' },
        banccontact: { title: 'Banccontact', image: '/images/cc-icons/banccontact.png' },
        dinersclub: { title: 'Diners Club', image: '/images/cc-icons/diners-curved.png' },
        discover: { title: 'Discover', image: '/images/cc-icons/discover-curved-128px.png' },
        elo: { title: 'Elo', image: '/images/cc-icons/elo-curved.png' },
        eps: { title: 'EPS', image: '/images/cc-icons/eps-curved.png' },
        giropay: { title: 'Giropay', image: '/images/cc-icons/giropay-curved.png' },
        hipercard: { title: 'Hipercard', image: '/images/cc-icons/hipercard-curved.png' },
        ideal: { title: 'iDEAL', image: '/images/cc-icons/ideal-curved.png' },
        jcb: { title: 'JCB', image: '/images/cc-icons/jcb-curved.png' },
        maestro: { title: 'Maestro', image: '/images/cc-icons/maestro.png' },
        mastercard: { title: 'Mastercard', image: '/images/cc-icons/mastercard-curved-128px.png' },
        multibango: { title: 'Multibango', image: '/images/cc-icons/multibango.png' },
        prezelewy24: { title: 'Prezelewy24', image: '/images/cc-icons/prezelewy24-curved.png' },
        sofort: { title: 'Sofort', image: '/images/cc-icons/sofort-curved.png' },
        visa: { title: 'Visa', image: '/images/cc-icons/visa-curved-128px.png' },
      };
    },

    creditCardsAsianCountries() {
      return ['in', 'jp', 'bd', 'cn', 'hk', 'id', 'la', 'mn', 'mm', 'ph', 'sg', 'kr', 'tw', 'th', 'vn'];
    },

    creditCardsAvailableList() {
      const country = this.country || checkoutData.countryCode;

      let cards = ['visa', 'mastercard', 'americanexpress', 'discover', 'dinersclub'];

      if (this.creditCardsAsianCountries.indexOf(country) !== -1) {
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

      return cards;
    },

    creditCardsAvailableHTML() {
      let output = '';

      output += '<div class="cards">';

      for (const name of this.creditCardsAvailableList) {
        output += '<div class="card card-' + name + '"><img src="' + this.creditCards[name].image + '" title="' + this.creditCards[name].title + '" /></div>';
      }

      output += '</div>';

      return output;
    },

  },

};
