require('../bootstrap')

import carousel from 'vue-owl-carousel';
import { stateList } from '../resourses/state';
import emc1Validation from '../validation/emc1-validation'

const promo = new Vue({
  el: "#promo",

  data: () => ({
    isShownForm: false,
    selectedPlan: null,
    selectedVariant: null,
    paymentMethod: null,
    stateList: (stateList[checkoutData.countryCode] || []).map((it) => ({
      value: it,
      text: it,
      label: it,
    })),
    form: {
      isWarrantyChecked: false,
      countryCodePhoneField: checkoutData.countryCode,
      deal: null,
      variant: (function() {
        try {
          return checkoutData.product.skus[0].code
        } catch(_) {}
      }()),
      installments: 1,
      paymentType: 'credit-card',
      fname: null,
      lname: null,
      dateOfBirth: '',
      email: null,
      phone: null,
      cardType: 'credit',
      street: null,
      number: null,
      complemento: null,
      city: null,
      state: null,
      zipcode: null,
      country: checkoutData.countryCode,
      cardNumber: '',
      month: null,
      year: null,
      cvv: null,
      documentNumber: ''
    },
    cardNames: [
      {
        value: 'visa',
        text: 'VISA',
        label: 'VISA',
        imgUrl: '/images/cc-icons/visa.png'
      }, {
        value: 'mastercard',
        text: 'MasterCard',
        label: 'MasterCard',
        imgUrl: '/images/cc-icons/mastercard.png'
      }, {
        value: 'diners-club',
        text: 'DinnersClub',
        label: 'DinnersClub',
        imgUrl: '/images/cc-icons/diners-club.png'
      }, {
        value: 'discover',
        text: 'Discover',
        label: 'Discover',
        imgUrl: '/images/cc-icons/discover.png'
      }, {
        value: 'paypal',
        text: 'PayPal',
        label: 'PayPal',
        imgUrl: '/images/cc-icons/payPal.png'
      }
    ],
    mockData: {
      countryList: [
        {
          value: 'US',
          text: 'USA',
          label: 'USA',
        }, {
          value: 'RU',
          text: 'Russia',
          label: 'Russia',
        }, {
          value: 'UA',
          text: 'Ukraine',
          label: 'Ukraine',
        }, {
          value: 'PT',
          text: 'Portugal',
          label: 'Portugal',
        }, {
          value: 'BR',
          text: 'Brazil',
          label: 'Brazil',
        }
      ],
    },
  }),
  validations: emc1Validation,

  components: {
    carousel,
  },


  mounted() {
    this.form.installments =
      this.checkoutData.countryCode === 'BR' ? 3 :
        this.checkoutData.countryCode === 'MX' ? 1 :
          1
  },

  computed: {
    checkoutData() {
      return checkoutData;
    },
  },

  methods: {
    setSelectedPlan(plan) {
      this.selectedPlan = plan;

      console.log(this.$data)
    },

    setSelectedVariant(variant) {
      this.selectedVariant = variant;
      this.isShownForm = true;

      console.log(this.$data)
    },

    selectPaymentMethod(method) {
      this.paymentMethod = method;
    },

    setAddress (address) {
      this.form = {
        ...this.form,
        ...address
      }
    },
  }
})
