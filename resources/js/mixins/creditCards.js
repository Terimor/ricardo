export default {

  computed: {

    creditCards() {
      return {
        visa: { title: 'Visa', image: '/images/cc-icons/visa.png' },
        mastercard: { title: 'Mastercard', image: '/images/cc-icons/mastercard.png' },
        americanexpress: { title: 'American Express', image: '/images/cc-icons/american-express.png' },
        discover: { title: 'Discover', image: '/images/cc-icons/discover.png' },
        dinersclub: { title: 'Diners Club', image: '/images/cc-icons/diners-club.png' },
        jcb: { title: 'JCB', image: '/images/cc-icons/jcb.png' },
        elo: { title: 'Elo', image: '/images/cc-icons/elo.png' },
        hipercard: { title: 'Hipercard', image: '/images/cc-icons/hipercard.png' },
        aura: { title: 'Aura', image: '/images/cc-icons/aura.png' },
        maestro: { title: 'Maestro', image: '/images/cc-icons/maestro.png' },
      };
    },

    creditCardsAsianCountries() {
      return ['jp', 'bd', 'cn', 'hk', 'id', 'la', 'mn', 'mm', 'ph', 'sg', 'kr', 'tw', 'th', 'vn'];
    },

    creditCardsAvailableList() {
      const country = checkoutData.countryCode;

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
