require('../bootstrap')

import carousel from 'vue-owl-carousel';
import { stateList } from '../resourses/state';
import emc1Validation from '../validation/emc1-validation'
import { paypalCreateOrder, paypalOnApprove } from '../utils/upsells';
import { preparePurchaseData } from '../utils/checkout';

const promo = new Vue({
  el: "#promo",

  data: () => ({
    implValue: '1',
    installments: '1',
    isShownForm: false,
    selectedPlan: null,
    warrantyPriceText: null,
    warrantyOldPrice: null,
    discount: null,
    variant: null,
    variantList: [],
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

  installmentsList: [
    {
      value: '1',
      text: 'Pay in 1 installments',
      label: 'Pay in 1 installments',
    },
    {
      value: '3',
      text: 'Pay in 3 installments',
      label: 'Pay in 3 installments',
    },
    {
      value: '5',
      text: 'Pay in 5 installments',
      label: 'Pay in 5 installments',
    }
  ],

  mounted() {
    this.form.installments =
      this.checkoutData.countryCode === 'BR' ? 3 :
        this.checkoutData.countryCode === 'MX' ? 1 :
          1
    this.changeWarrantyValue()

    this.variantList = this.skusList.map((it) => ({
      label: it.name,
      text: `<div><img src="${it.images[0]}" alt=""><span>${it.name}</span></div>`,
      value: it.code,
      imageUrl: it.images[0]
    }))
  },

  computed: {
    checkoutData() {
      return checkoutData;
    },

    quantityOfInstallments () {
      const implValue = this.implValue
      return implValue && implValue !== 1 ? implValue + '× ' : ''
    },

    productData () {
      return checkoutData.product
    },

    skusList () {
      return checkoutData.product.skus;
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
      this.variant = variant;
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

    getImplValue(value) {
      this.implValue = value;
      if (this.implValue) this.changeWarrantyValue();
    },

    changeWarrantyValue () {
      const prices = product.prices;
      this.implValue = this.implValue || 3;
      switch(this.implValue) {
        case String(1):
          this.warrantyPriceText = prices[this.implValue].value_text;
          this.warrantyOldPrice = prices[this.implValue].old_value_text;
          this.discount = prices[this.implValue].discount_percent;
        case String(3):
          this.warrantyPriceText = prices[this.implValue].installments3_value_text;
          this.warrantyOldPrice = prices[this.implValue].installments3_old_value_text;
          this.discount = prices[this.implValue].discount_percent;
        case String(5):
          this.warrantyPriceText = prices[this.implValue].installments6_value_text;
          this.warrantyOldPrice = prices[this.implValue].installments6_old_value_text;
          this.discount = prices[this.implValue].discount_percent;
        default:
          break;
      }

      const currentVariant = this.skusList.find(it => it.code === this.variant)

      this.purchase = preparePurchaseData({
        purchaseList: this.productData.prices,
        long_name: this.productData.long_name,
        variant: currentVariant && currentVariant.name,
        installments: this.installments,
      })

      console.log(JSON.parse(JSON.stringify(this.purchase)))
    },

    scrollTo(selector) {
      setTimeout(() => {
        document.querySelector(selector).scrollIntoView()
      }, 1)
    }
  }
})
