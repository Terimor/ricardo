require('../bootstrap')

import carousel from 'vue-owl-carousel';
import { stateList } from '../resourses/state';
import emc1Validation from '../validation/emc1-validation'
import { paypalCreateOrder, paypalOnApprove } from '../utils/emc1';
import { preparePurchaseData, goToThankYouPromos } from '../utils/checkout';
import { t } from '../utils/i18n';
import { scrollTo } from '../utils/common';
import { getCountOfInstallments } from '../utils/installments';
import notification from '../mixins/notification';
import queryToComponent from '../mixins/queryToComponent';
import purchasMixin from '../mixins/purchas';

const promo = new Vue({
  el: "#promo",

  mixins: [
    notification,
    queryToComponent,
    purchasMixin,
  ],

  data: () => ({
    showPreloader: true,
    isFormShown: false,
    implValue: 1,
    installments: 1,
    isShownForm: false,
    selectedPlan: null,
    warrantyPriceText: null,
    warrantyOldPrice: null,
    discount: null,
    variant: null,
    purchase: [],
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
      variant: '',
      installments: 1,
      paymentType: '',
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
      country: checkoutData.countryCode.toUpperCase(),
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
      creditCardRadioList: [
        {
          label: t('checkout.credit_cards'),
          value: 'credit-card',
          class: 'green-button-animated'
        }, {
          label: t('checkout.bank_payments'),
          value: 'bank-payment',
          class: 'bank-payment'
        }
      ],
    }
  }),
  validations: emc1Validation,

  components: {
    carousel,
  },

  installmentsList: [
    {
      value: 1,
      label: t('checkout.installments.full_amount'),
      text: t('checkout.installments.full_amount'),
    },
    {
      value: 3,
      label: t('checkout.installments.pay_3'),
      text: t('checkout.installments.pay_3'),
    },
    {
      value: 6,
      label: t('checkout.installments.pay_6'),
      text: t('checkout.installments.pay_6'),
    }
  ],

  beforeCreate() {
    document.body.classList.add('tpl-vmp41');
  },

  created() {
    if (this.queryParams['3ds'] === 'success') {
      goToThankYouPromos(this.queryParams['order'], this.queryParams['cur']);
      return;
    }

    if (this.queryParams['3ds'] === 'failure') {
      const selectedProductData = JSON.parse(localStorage.getItem('selectedProductData'));

      if (selectedProductData) {
        this.form.deal = selectedProductData.deal || this.form.deal;
        this.form.variant = selectedProductData.variant || this.form.variant;
        this.form.isWarrantyChecked = selectedProductData.isWarrantyChecked || this.form.isWarrantyChecked;
        this.form.installments = selectedProductData.installments || this.form.installments;
        this.form.paymentType = selectedProductData.paymentType || this.form.paymentType;
        this.form.cardType = selectedProductData.cardType || this.form.cardType;
        this.form.fname = selectedProductData.fname || this.form.fname;
        this.form.lname = selectedProductData.lname || this.form.lname;
        this.form.dateOfBirth = selectedProductData.dateOfBirth || this.form.dateOfBirth;
        this.form.email = selectedProductData.email || this.form.email;
        this.form.phone = selectedProductData.phone || this.form.phone;
        this.form.countryCodePhoneField = selectedProductData.countryCodePhoneField || this.form.countryCodePhoneField;
        this.form.street = selectedProductData.street || this.form.street;
        this.form.number = selectedProductData.streetNumber || this.form.number;
        this.form.complemento = selectedProductData.complemento || this.form.complemento;
        this.form.city = selectedProductData.city || this.form.city;
        this.form.state = selectedProductData.state || this.form.state;
        this.form.zipcode = selectedProductData.zipcode || this.form.zipcode;
        this.form.country = selectedProductData.country || this.form.country;
        this.isFormShown = true;
      }
    }
  },

  mounted() {
    this.installments =
      this.checkoutData.countryCode === 'BR' ? 3 :
        this.checkoutData.countryCode === 'MX' ? 1 :
          1
    this.changeWarrantyValue();

    this.variantList = this.skusList.map((it) => ({
      label: it.name,
      text: `<div><img src="${it.quantity_image[1]}" alt=""><span>${it.name}</span></div>`,
      value: it.code,
      imageUrl: it.quantity_image[1],
    }));

    const qty = +this.queryParams.qty;
    const deal = this.purchase.find(({ totalQuantity }) => qty === totalQuantity);

    if (deal) {
      const plan = deal.discountName || 'STARTER';
      this.setSelectedPlan(plan, qty);

      setTimeout(() => {
        this.scrollTo('.j-variant-section');
      }, 500);
    }
  },

  computed: {
    checkoutData() {
      return checkoutData;
    },

    withInstallments () {
      return this.checkoutData.countryCode === 'BR'
        || this.checkoutData.countryCode === 'MX'
        || this.checkoutData.countryCode === 'CO'
    },

    countriesList() {
      let countries = []

      Object.entries(checkoutData.countries).map(([key, value]) => {
        countries.push({
          value: key.toUpperCase(),
          text: value,
          label: value,
        });
      });

      return countries;
    },

    countOfInstallments() {
      return getCountOfInstallments(this.installments);
    },

    productData () {
      return checkoutData.product
    },

    skusList () {
      return checkoutData.product.skus;
    },

    codeOrDefault () {
      return this.queryParams.product || this.checkoutData.product.skus[0].code;
    },

    hasTimer() {
      return document.getElementById('timer-component');
    },

    textPromoDiscount() {
      return t('checkout.promo.buy_now', { discount: this.discount || 0 });
    },

    textDiscountStarter: () => t('checkout.discount_starter'),
  },

  methods: {
    scrollTo: scrollTo,
    paypalOnApprove: paypalOnApprove,

    paypalCreateOrder () {
      const searchParams = new URL(document.location.href).searchParams;
      const currency = searchParams.get('cur') || checkoutData.product.prices.currency;

      this.setDataToLocalStorage({
        deal: this.form.deal,
        variant: this.form.variant,
        isWarrantyChecked: this.form.isWarrantyChecked,
      });

      return paypalCreateOrder({
        xsrfToken: document.head.querySelector('meta[name="csrf-token"]').content,
        sku_code: this.form.variant,
        sku_quantity: this.form.deal,
        is_warranty_checked: this.form.isWarrantyChecked,
        page_checkout: document.location.href,
        cur: currency,
        offer: new URL(document.location.href).searchParams.get('offer'),
        affiliate: new URL(document.location.href).searchParams.get('affiliate'),
      })
    },

    setSelectedPlan(plan, deal) {
      this.selectedPlan = plan;
      this.form.deal = deal
      this.scrollTo('.j-variant-section');
    },

    setSelectedVariant(variant) {
      this.form.variant = variant;
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

    activateForm() {
      this.isFormShown = true;

      this.scrollTo('.j-payment-form');
    },

    changeWarrantyValue () {
      const prices = checkoutData.product.prices;
      this.implValue = this.implValue || 3;

      switch(this.implValue) {
        case 1:
          this.warrantyPriceText = prices[1].value_text;
          this.warrantyOldPrice = prices[1].old_value_text;
          this.discount = prices[1].discount_percent;
          break;
        case 3:
          this.warrantyPriceText = prices[1].installments3_value_text;
          this.warrantyOldPrice = prices[1].installments3_old_value_text;
          this.discount = prices[1].discount_percent;
          break;
        case 6:
          this.warrantyPriceText = prices[1].installments6_value_text;
          this.warrantyOldPrice = prices[1].installments6_old_value_text;
          this.discount = prices[1].discount_percent;
          break;
        default:
          break;
      }

      const currentVariant = this.skusList.find(it => it.code === this.variant)

      this.purchase = preparePurchaseData({
        purchaseList: this.productData.prices,
        long_name: this.productData.long_name,
        variant: currentVariant && currentVariant.name,
        installments: this.implValue,
        image: this.productData.image[0],
      })
    },
  }
})
