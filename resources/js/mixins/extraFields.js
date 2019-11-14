import creditCardType from 'credit-card-type';
import { required } from 'vuelidate/lib/validators'


const formFields = {
  payment_method: null,
  installments: 1,
  state: null,
  district: null,
  card_type: null,
  document_type: null,
  document_number: null,
};

const ebanxMap = {
  'visa': 'visa',
  'mastercard': 'mastercard',
  'american-express': 'amex',
  'diners-club': 'dinersclub',
  'discover': 'discover',
  'jcb': 'jcb',
  'unionpay': null,
  'maestro': null,
  'mir': null,
  'elo': 'elo',
  'hiper': null,
  'hipercard': 'hipercard',
};

const searchParams = new URL(location).searchParams;


export const appMixin = {

  data() {
    return {
      paymentMethods: window.checkoutData && checkoutData.paymentMethods
        ? JSON.parse(JSON.stringify(checkoutData.paymentMethods))
        : {},
    };
  },

};


export const tplMixin = {

  data() {
    return {
      form: {
        ...formFields,
      },
    };
  },


  created() {
    this.setDefaultExtraValues();
    this.getExtraFieldsFromLocalStorage();
  },


  computed: {

    extraFields() {
      const firstMethod = Object.keys(this.$root.paymentMethods)
        .filter(name => name !== 'instant_transfer')
        .shift();

      const payment_method = this.form.payment_method || firstMethod;

      return this.$root.paymentMethods[payment_method]
        ? this.$root.paymentMethods[payment_method].extra_fields || {}
        : {};
    },

    paymentMethodURL() {
      return this.$root.paymentMethods && this.form.payment_method && this.$root.paymentMethods[this.form.payment_method]
        ? this.$root.paymentMethods[this.form.payment_method].logo
        : window.cdnUrl + '/assets/images/cc-icons/iconcc.png';
    },

    installmentsVisible() {
      const values = {
        card_type: this.form.card_type, 
      };

      if (!this.extraFields.installments) {
        return false;
      }

      const visibility = this.extraFields.installments
        ? this.extraFields.installments.visibility || {}
        : {};

      return Object.keys(visibility)
        .reduce((visible, propName) => {
          return visibility[propName].indexOf(values[propName]) !== -1;
        }, true);
    },

    quantityOfInstallments() {
      return this.form.installments !== 1
        ? this.form.installments + '× '
        : '';
    },

  },


  methods: {

    reloadPaymentMethods(country) {
      return fetch('/payment-methods-by-country?country=' + country)
        .then(res => res.json())
        .then(res => {
          this.$root.paymentMethods = res;
          this.setDefaultExtraValues();
        })
        .catch(err => {

        });
    },

    setPaymentMethodByCardNumber(cardNumber) {
      cardNumber = cardNumber ? cardNumber.replace(/[^0-9]/g, '') : '';
      const libPaymentMethodsList = creditCardType(cardNumber);

      const libPaymentMethod = cardNumber.length > 0 && libPaymentMethodsList.length > 0
        ? libPaymentMethodsList[0].type
        : null;

      const payment_method = libPaymentMethod && ebanxMap[libPaymentMethod]
        ? ebanxMap[libPaymentMethod]
        : null;

      this.form.payment_method = this.$root.paymentMethods && this.$root.paymentMethods[payment_method]
        ? payment_method
        : null;
    },

    setDefaultExtraValues() {
      this.form.installments = this.extraFields.installments
        ? this.extraFields.installments.default
        : 1;

      this.form.state = this.extraFields.state
        ? this.extraFields.state.default
        : this.form.state;

      this.form.card_type = this.extraFields.card_type
        ? this.extraFields.card_type.default
        : null;

      this.form.document_type = this.extraFields.document_type
        ? this.extraFields.document_type.default
        : null;
    },

    setExtraFieldsForLocalStorage(data) {
      for (let name of Object.keys(formFields)) {
        if (!data[name]) {
          data[name] = this.form[name];
        }
      }
    },

    getExtraFieldsFromLocalStorage() {
      if (searchParams.get('3ds') === 'failure') {
        try {
          const selectedProductData = JSON.parse(localStorage.getItem('selectedProductData')) || {};

          for (let name of Object.keys(formFields)) {
            this.form[name] = selectedProductData[name] || this.form[name];
          }
        }
        catch (err) {

        }
      }
    },

    setExtraFieldsValidationRules(rules) {
      if (this.extraFields.district) {
        rules.district = {
          required,
          isValid(value) {
            return new RegExp(this.extraFields.district.pattern || '/.+/').test(value);
          },
        };
      }

      if (this.extraFields.card_type) {
        rules.card_type = {
          required,
        };
      }

      if (this.extraFields.document_type) {
        rules.document_type = {
          required,
        };
      }

      if (this.extraFields.document_number) {
        rules.document_number = {
          isValidNumber(value) {
            const pattern = this.extraFields.document_number.pattern
              ? typeof this.extraFields.document_number.pattern === 'object'
                ? this.extraFields.document_number.pattern[this.form.document_type] || ''
                : this.extraFields.document_number.pattern
              : '/.+/';

            return new RegExp(pattern).test(value);
          }
        };
      }
    },

    setExtraFieldsForCardPayment(data) {
      if (this.extraFields.installments) {
        data.card.installments = this.form.installments;
      }

      if (this.extraFields.district) {
        data.address.district = this.form.district;
      }

      if (this.extraFields.card_type) {
        data.card.type = this.form.card_type;
      }

      if (this.extraFields.document_type) {
        data.contact.document_type = this.form.document_type;
      }

      if (this.extraFields.document_number) {
        data.contact.document_number = this.form.document_number;
      }
    },

  },


  watch: {

    'form.country'(country) {
      this.reloadPaymentMethods(country);
    },

    'form.card_type'(value) {
      if (!this.installmentsVisible) {
        this.form.installments = 1;
      }
    },

  },

};