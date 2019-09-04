require('../bootstrap')

import carousel from 'vue-owl-carousel';
import { stateList } from '../resourses/state';
import emc1Validation from '../validation/emc1-validation'
import { paypalCreateOrder, paypalOnApprove } from '../utils/upsells';

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
    paypalCreateOrder () {
      return paypalCreateOrder({
        xsrfToken: document.head.querySelector('meta[name="csrf-token"]').content,
        sku_code: this.codeOrDefault,
        sku_quantity: this.form.deal,
        is_warranty_checked: this.form.isWarrantyChecked,
        page_checkout: document.location.href,
        offer: new URL(document.location.href).searchParams.get('offer'),
        affiliate: new URL(document.location.href).searchParams.get('affiliate'),
      })
    },

    paypalOnApprove: paypalOnApprove,

    setSelectedPlan(plan) {
      this.selectedPlan = plan;

      this.scrollTo('.j-variant-section');
    },

    setSelectedVariant(variant) {
      this.selectedVariant = variant;
      this.isShownForm = true;

      this.scrollTo('.j-complete-order');
    },

    selectPaymentMethod(method) {
      this.paymentMethod = method;

      this.scrollTo('.j-payment-form');
    },

    setAddress (address) {
      this.form = {
        ...this.form,
        ...address
      }
    },

    scrollTo(selector) {
      setTimeout(() => {
        document.querySelector(selector).scrollIntoView()
      }, 1)
    }
  }
})
