<template>
    <div v-if="$v && !hidePage">
        <div class="container smc7">
            <div class="row">
                <div class="container paper smc7__product">
                    <div class="col-md-7 image-wrapper">
                        <img
                                id="product-image-head"
                                :src="setProductImage"
                                alt=""
                        >
                    </div>
                    <div
                            class="col-md-5 advantages"
                            v-html="checkoutData.product.description"
                    >
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="container">
                    <ProductOffer :product="checkoutData.product" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-7">
                    <div class="paper smc7__deal">
                        <div class="d-flex">
                            <div class="smc7__sale">
                                <div class="sale-badge dynamic-sale-badge ">
                                    <div class="dynamic-sale-badge__background"></div>
                                    <div class="dynamic-sale-badge__container">
                                        <span class="badge-discount-percentage">50%</span>
                                        <span>Off</span>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex flex-column smc7__deal__text">
                                <p v-html="freeShippingToday" />
                            </div>

                        </div>

                        <div class="smc7__step-1">
                            <h2><span>{{textStep}}</span> 1: <span>{{textChooseDeal}}</span></h2>
                            <div class="smc7__step-1__titles">
                                <h3>{{textArtcile}}</h3>
                                <h3>{{textPrice}}</h3>
                            </div>

                            <span class="error" v-show="$v.form.deal.$dirty && $v.form.deal.$invalid">{{ checkoutmainDealErrorText }}</span>

                            <radio-button-group
                              :withCustomLabels="true"
                              v-model="form.deal"
                            >
                              <radio-button-item-deal
                                      :value="form.deal"
                                      :showDiscount=false
                                      :customBackground=false
                                      v-for="item in purchase"
                                      :item="{
                                        ...item,
                                        value: item.totalQuantity,
                                      }"
                                      :key="item.value"
                                      :showShareArrow="item.totalQuantity === 5"/>
                            </radio-button-group>
                        </div>

                        <div class="smc7__step-2"
                          v-if="!isShowVariant"
                        >
                            <h2><span>{{textStep}}</span> 2: <span>{{textSelectVariant}}</span></h2>
                            <select-field
                              popperClass="smc7-popover-variant"
                              v-model="form.variant"
                              :rest="{
                                placeholder: 'Variant'
                              }"
                              :list="variantList"/>
                        </div>

                        <div class="smc7__step-3">
                            <h2><span>{{textStep}}</span> {{ isShowVariant ? 2 : 3  }}: <span>{{textContactInformation}}</span></h2>
                            <div class="full-name">
                                <text-field
                                  :validation="$v.form.fname"
                                  :validationMessage="textFirstNameRequired"
                                  theme="variant-1"
                                  :label="textFirstName"
                                  class="first-name"
                                  :rest="{
                                    placeholder: textFirstName,
                                    autocomplete: 'given-name'
                                  }"
                                  v-model="form.fname"/>
                                <text-field
                                  :validation="$v.form.lname"
                                  :validationMessage="textLastNameRequired"
                                  theme="variant-1"
                                  :label="textLastName"
                                  class="last-name"
                                  :rest="{
                                    placeholder: textLastName,
                                    autocomplete: 'family-name'
                                  }"
                                  v-model="form.lname"/>
                            </div>
                            <text-field
                              :validation="$v.form.email"
                              :validationMessage="textEmailRequired"
                              theme="variant-1"
                              :label="textEmailAddress"
                              :rest="{
                                placeholder: textEmailAddress,
                                autocomplete: 'email'
                              }"
                              v-model="form.email"/>
                            <phone-field
                              @onCountryChange="setCountryCodeByPhoneField"
                              :validation="$v.form.phone"
                              :validationMessage="textPhoneRequired"
                              :countryCode="form.countryCodePhoneField"
                              theme="variant-1"
                              :label="textPhoneNumber"
                              :rest="{
                                autocomplete: 'off'
                              }"
                              v-model="form.phone"/>
                        </div>
                    </div>
                </div>
                <div class="col-md-5 smc7__step-4">
                    <div class="paper">
                        <div class="d-flex">
                            <div class="smc7__step-4__product">
                                <h2>{{ checkoutData.product.long_name }}</h2>
                                <p>GET 50% OFF TODAY + FREE SHIPPING</p>
                            </div>
                            <img id="product-image-body" :src="setProductImage" alt="Product image">
                        </div>
                        <h2 class="step-title"><span>{{textStep}}</span> {{ isShowVariant ? 3 : 4  }}: <span>{{textContactInformation}}</span></h2>
                        <payment-form-smc7
                                :countryList="setCountryList"
                                :$v="$v"
                                :paymentForm="form"/>
                        <PurchasAlreadyExists v-if="isPurchasAlreadyExists"/>
                        <template v-else>
                            <p v-if="paymentError" id="payment-error" class="error-container" v-html="paymentError"></p>
                            <button
                              :disabled="isSubmitted"
                              v-if="form.paymentType !== 'paypal'"
                              @click="submit"
                              id="purchase-button"
                              type="button"
                              class="green-button-animated"
                              :class="{ 'green-button-active': !isSubmitted }">
                              <Spinner v-if="isSubmitted" />
                              <div v-if="isSubmitted" class="purchase-button-disabled"></div>
                              <span class="purchase-button-text" :style="{ visibility: isSubmitted ? 'hidden' : 'visible' }">{{textSubmitButton}}</span>
                            </button>
                            <paypal-button
                                    v-if="form.paymentType === 'paypal'"
                                    :createOrder="paypalCreateOrder"
                                    :onApprove="paypalOnApprove"
                                    :$v="$v.form.deal"
                                    @click="paypalSubmit"
                            >{{ paypalRiskFree }}</paypal-button>
                            <p v-if="paypalPaymentError" id="paypal-payment-error" class="error-container" v-html="paypalPaymentError"></p>
                        </template>
                        <div class="smc7__bottom">
                            <img :src="$root.cdnUrl + '/assets/images/safe_payment_en.png'" alt="safe payment">
                            <div class="smc7__bottom__safe">
                                <p><i class="fa fa-lock"></i>{{ textSafeSSLEncryption }}</p>
                                <p>{{ textCreditCardInvoiced }} "{{ productData.billing_descriptor }}"</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <el-dialog
                @click="isOpenPromotionModal = false"
                class="cvv-popup"
                :title="checkoutmainDealErrorText"
                :lock-scroll="false"
                :visible.sync="isOpenPromotionModal">
            <div class="cvv-popup__content">
                <p class="error-container">
                    {{ checkoutmainDealErrorText }}
                </p>

                <button
                        @click="isOpenPromotionModal = false"
                        style="height: 67px; margin: 0"
                        type="button"
                        class="green-button-animated">
                    <span class="purchase-button-text">{{ okText }}</span>
                </button>
            </div>
        </el-dialog>
    </div>
</template>

<script>
  import { preparePurchaseData } from "../utils/checkout";
  import RadioButtonItemDeal from "./common/RadioButtonItemDeal";
  import PurchasAlreadyExists from './common/PurchasAlreadyExists';
  import ProductOffer from '../components/common/ProductOffer';
  import smc7validation from "../validation/smc7-validation";
  import queryToComponent from '../mixins/queryToComponent';
  import scrollToError from '../mixins/formScrollToError';
  import {fade} from "../utils/common";
  import { t } from '../utils/i18n';
  import purchasMixin from '../mixins/purchas';
  import { paypalCreateOrder, paypalOnApprove } from '../utils/emc1';
  import { check as ipqsCheck } from '../services/ipqs';
  import { sendCheckoutRequest } from '../utils/checkout';
  import Spinner from './common/preloaders/Spinner';
  import { queryParams } from  '../utils/queryParams';

  export default {
    name: 'smc7',
    components: {
      RadioButtonItemDeal,
      ProductOffer,
      PurchasAlreadyExists,
      Spinner,
    },
    validations: smc7validation,
    mixins: [
      queryToComponent,
      purchasMixin,
      scrollToError,
    ],
    props: ['showPreloader', 'skusList'],
    data() {
      return {
        hidePage: false,
        productImage: checkoutData.product.image[0],
        paypalPaymentError: '',
        form: {
          isWarrantyChecked: false,
          countryCodePhoneField: checkoutData.countryCode,
          deal: null,
          fname: null,
          lname: null,
          email: null,
          phone: null,
          variant: (function () {
            try {
              return checkoutData.product.skus[0].code
            } catch (_) {
            }
          }()),
          country: checkoutData.countryCode,
          streetAndNumber: null,
          city: null,
          state: null,
          zipCode: null,
          installments: 1,
          paymentType: null,
          cardNumber: '',
          month: null,
          year: null,
          cvv: null,
          cardType: 'credit',
        },
        purchase: [],
        variantList: [],
        isOpenPromotionModal: false,
        isOpenSpecialOfferModal: false,
        isSubmitted: false,
        paymentError: '',
      }
    },
    created() {
      if (this.queryParams['3ds'] === 'failure') {
        const selectedProductData = JSON.parse(localStorage.getItem('selectedProductData'));

        if (selectedProductData) {
          this.paymentError = this.textPaymentError;
          this.form.deal = parseInt(selectedProductData.deal, 10) || this.form.deal;
          this.form.variant = selectedProductData.variant || this.form.variant;
          this.form.isWarrantyChecked = selectedProductData.isWarrantyChecked || this.form.isWarrantyChecked;
          this.form.installments = selectedProductData.installments || this.form.installments;
          this.form.cardType = selectedProductData.cardType || this.form.cardType;
          this.form.fname = selectedProductData.fname || this.form.fname;
          this.form.lname = selectedProductData.lname || this.form.lname;
          this.form.email = selectedProductData.email || this.form.email;
          this.form.phone = selectedProductData.phone || this.form.phone;
          this.form.countryCodePhoneField = selectedProductData.countryCodePhoneField || this.form.countryCodePhoneField;
          this.form.streetAndNumber = selectedProductData.streetAndNumber || this.form.streetAndNumber;
          this.form.city = selectedProductData.city || this.form.city;
          this.form.state = selectedProductData.state || this.form.state;
          this.form.zipCode = selectedProductData.zipcode || this.form.zipCode;
          this.form.country = selectedProductData.country || this.form.country;
        }
      }
    },
    computed: {
      discount: () => t('checkout.header_banner.discount'),
      textStep: () => t('checkout.step'),
      textChooseDeal: () => t('checkout.choose_deal'),
      textArtcile: () => t('checkout.article'),
      textPrice: () => t('checkout.header_banner.price'),
      freeShippingToday: () => t('checkout.free_shipping_today'),
      textSelectVariant: () => t('checkout.select_variant'),
      textContactInformation: () => t('checkout.contact_information'),
      textFirstName: () => t('checkout.payment_form.first_name'),
      textLastName: () => t('checkout.payment_form.last_name'),
      textEmailAddress: () => t('checkout.payment_form.email'),
      textPhoneNumber: () => t('checkout.payment_form.phone'),
      textSubmitButton: () => t('checkout.payment_form.submit_button'),
      paypalRiskFree: () => t('checkout.paypal.risk_free'),
      textCreditCardInvoiced: () => t('checkout.credit_card_invoiced'),
      textSafeSSLEncryption: () => t('checkout.safe_sll_encryption'),
      checkoutmainDealErrorText: () => t('checkout.main_deal.error'),
      okText: () => t('checkout.main_deal.error_popup.button'),
      textFirstNameRequired: () => t('checkout.payment_form.first_name.required'),
      textLastNameRequired: () => t('checkout.payment_form.last_name.required'),
      textEmailRequired: () => t('checkout.payment_form.email.required'),
      textPhoneRequired: () => t('checkout.payment_form.phone.required'),
      textPaymentError: () => t('checkout.payment_error'),

      setProductImage() {
          return this.productData.image[this.queryParams['image'] - 1] || this.productData.image[0];
      },

      isShowVariant() {
        return Number(queryParams().variant) === 0;
      },

      checkoutData() {
        return checkoutData;
      },
      productData() {
        return checkoutData.product;
      },

      setCountryList () {
        const countries = checkoutData.countries;
        let countriesList = [];

        Object.entries(countries).map(function([key, value]) {
          countriesList.push({
            value: key,
            text: value,
            label: value
          });
        });

        return countriesList;
      },

      codeOrDefault () {
        return this.queryParams.product || checkoutData.product.skus[0].code;
      },

      radioIdx() {
        return this.form.deal;
      },

      dialCode() {
        const allCountries = window.intlTelInputGlobals.getCountryData();
        const phoneCountryCode = this.form.countryCodePhoneField;
        const country = allCountries.filter(item => item.iso2 === phoneCountryCode).shift();

        return country ? country.dialCode : '1';
      },

    },
    watch: {
      'form.variant'(val) {
        fade('out', 300, document.querySelector('#product-image-head'), true)
          .then(() => {
            this.productImage = this.variantList.find(variant => variant.value === val).imageUrl;

            fade('in', 300, document.querySelector('#product-image-head'), true)
          });

        fade('out', 300, document.querySelector('#product-image-body'), true)
          .then(() => {
            this.productImage = this.variantList.find(variant => variant.value === val).imageUrl;

            fade('in', 300, document.querySelector('#product-image-body'), true)
          });

        this.setPurchase({
          variant: val,
          installments: this.form.installments,
        })
      },
    },
    methods: {
      submit() {
        const cardNumber = this.form.cardNumber.replace(/\s/g, '');

        this.$v.form.$touch();

        if (this.$v.form.deal.$invalid) {
          document.querySelector('.smc7__deal').scrollIntoView();
          this.setPromotionalModal(true);
          return;
        }
        if (this.$v.form.$pending || this.$v.form.$error) {
          this.scrollToError();
          return;
        }

        if (this.isSubmitted) {
          return;
        }

        this.paymentError = '';
        this.isSubmitted = true;

        let fields = {
          billing_first_name: this.form.fname,
          billing_last_name: this.form.lname,
          billing_country: this.form.country,
          billing_address_1: this.form.streetAndNumber,
          billing_city: this.form.city,
          billing_region: this.form.state,
          billing_postcode: this.form.zipCode,
          billing_email: this.form.email,
          billing_phone: this.dialCode + this.form.phone,
          credit_card_bin: cardNumber.substr(0, 6),
          credit_card_hash: window.sha256(cardNumber),
          credit_card_expiration_month: ('0' + this.form.month).slice(-2),
          credit_card_expiration_year: ('' + this.form.year).substr(2, 2),
          cvv_code: this.form.cvv,
        };

        this.setDataToLocalStorage({
          deal: this.form.deal,
          variant: this.form.variant,
          isWarrantyChecked: this.form.isWarrantyChecked,
          installments: this.form.installments,
          paymentType: 'credit-card',
          cardType: this.form.cardType,
          fname: this.form.fname,
          lname: this.form.lname,
          email: this.form.email,
          phone: this.form.phone,
          countryCodePhoneField: this.form.countryCodePhoneField,
          streetAndNumber: this.form.streetAndNumber,
          city: this.form.city,
          state: this.form.state,
          zipcode: this.form.zipCode,
          country: this.form.country,
        });

        Promise.resolve()
          .then(() => ipqsCheck(fields))
          .then(ipqsResult => {
            const data = {
              product: {
                sku: this.form.variant,
                qty: parseInt(this.form.deal, 10),
                is_warranty_checked: this.form.isWarrantyChecked,
              },
              contact: {
                phone: {
                  country_code: this.dialCode,
                  number: this.form.phone,
                },
                first_name: this.form.fname,
                last_name: this.form.lname,
                email: this.form.email,
              },
              address: {
                city: this.form.city,
                country: this.form.country,
                zip: this.form.zipCode,
                state: this.form.state,
                street: this.form.streetAndNumber,
              },
              card: {
                number: cardNumber,
                cvv: this.form.cvv,
                month: ('0' + this.form.month).slice(-2),
                year: '' + this.form.year,
                type: this.form.paymentType,
              },
              ipqs: ipqsResult,
            };

            sendCheckoutRequest(data)
              .then(res => {
                if (res.paymentError) {
                  this.paymentError = res.paymentError;
                  this.isSubmitted = false;
                }
              });
          })
      },
      setCountryCodeByPhoneField (val) {
        if (val.iso2) {
          this.form.countryCodePhoneField = val.iso2;
        }
      },
      setPurchase({variant, installments}) {
        this.purchase = preparePurchaseData({
          purchaseList: this.productData.prices,
          quantityToShow: [1, 2, 3, 4, 5],
          long_name: this.productData.product_name,
          variant,
          installments,
          customOrder: true
        })
      },
      setPromotionalModal(val) {
        this.isOpenPromotionModal = val
      },
      paypalCreateOrder () {
        const searchParams = new URL(document.location.href).searchParams;
        const currency = searchParams.get('cur') || checkoutData.product.prices.currency;

        this.setDataToLocalStorage({
          deal: this.form.deal,
          variant: this.form.variant,
          isWarrantyChecked: this.form.isWarrantyChecked,
          paymentType: this.form.paymentType,
        });

        this.paypalPaymentError = '';

        return paypalCreateOrder({
            xsrfToken: document.head.querySelector('meta[name="csrf-token"]').content,
            sku_code: this.codeOrDefault,
            sku_quantity: this.form.deal,
            is_warranty_checked: this.form.isWarrantyChecked,
            page_checkout: document.location.href,
            cur: currency,
            offer: searchParams.get('offer'),
            affiliate: searchParams.get('affiliate'),
          })
          .then(res => {
            if (res.paypalPaymentError) {
              this.paypalPaymentError = res.paypalPaymentError;
            }

            return res;
          });
      },
      paypalOnApprove: paypalOnApprove,

      paypalSubmit() {
        this.form.paymentType = 'paypal';

        if (this.$v.form.deal.$invalid) {
          document.querySelector('.smc7__deal').scrollIntoView();
          this.isOpenPromotionModal = true;
        }
      },
    },
    mounted() {
      this.variantList = this.productData.skus.map((it) => ({
        label: it.name,
        text: `<div><img src="${it.quantity_image[1]}" alt=""><span>${it.name}</span></div>`,
        value: it.code,
        imageUrl: it.quantity_image[1]
      }));

      this.setPurchase({
        variant: this.form.variant,
        installments: 1,
      })

      if (this.paymentError && !this.isPurchasAlreadyExists) {
        setTimeout(() => document.querySelector('#payment-error').scrollIntoView(), 1000);
      }
    }
  }
</script>

<style lang="scss">
    @import "../../sass/variables";

    .smc7.container {
        max-width: 970px;
    }

    .smc7 {
        &__product {
            display: flex;

            .image-wrapper {
                display: flex;
                justify-content: center;
                align-items: center;
                overflow: hidden;

                img {
                    height: 335px;
                    object-fit: contain;
                    padding: 34px;
                }
            }

            .advantages {
                display: flex;
                align-items: center;

                ul {
                    width: 100%;
                    list-style: none;
                    padding: 0;
                }

                li {
                    position: relative;
                    background: #e3e3e3;
                    margin: 3px 0;
                    padding: 15px 15px 15px 38px;
                    font-weight: 700;

                    &:before {
                        position: absolute;
                        top: 18px;
                        left: 15px;
                        display: inline-block;
                        font: normal normal normal 14px/1 FontAwesome;
                        font-size: inherit;
                        content: "\f00c";
                        color: green;
                    }
                }
            }
        }

        .offer {
            padding-top: 20px;
            padding-bottom: 20px;
            text-align: center;
        }

        &__deal {
            &__text {
                font-size: 18px;
                font-style: italic;
                padding: 0 20px;
                margin: 0;
            }
        }

        &__sale {
            .dynamic-sale-badge {
                &__background {
                    background-color: #c0392b;
                    box-shadow: 0 0 0 5px #c0392b;
                }
            }
            .badge-discount-percentage {
                font-size: 18px;
            }
        }

        .el-select .el-input.is-focus .el-input__inner,
        .el-select .el-input__inner:focus {
            border-color: #C0C4CC;
        }

        &__step-1 {
            .bestseller {
                display: none;
            }
            h2 {
                margin-bottom: 20px;
            }
            &__titles {
                display: flex;
                padding: 0 10px;
                h3 {
                    margin: 0 0 10px;
                }

                h3:first-child {
                    width: 60%;
                }

                h3:last-child {
                    width: 40%;
                    text-align: right;
                }
            }

            .radio-button-group .label-container-radio {
                &__label {
                    padding-bottom: 5px;
                }

                &__label,
                &__subtitle {
                    font-weight: 700;
                    font-size: 16px;
                }

            }

            .radio-button-group .label-container-radio__name-price {
                display: flex;
                justify-content: space-between;
            }

            .radio-button-group .label-container-radio__best-seller {
                display: flex;
                justify-content: space-between;
                color: #e74c3c;
            }

            .radio-button-group {
                .label-container-radio {
                    &:nth-child(1) {
                        background: #fef036;
                    }

                    &:hover {
                        background: #fef9ae;
                    }
                }

            }

            .share {
                position: absolute;
                transform: rotate3d(-10, -3, 0, 180deg);
                height: auto;
                width: 40px;
                top: -17px;
                left: -30px;

                @media screen and ($s-down) {
                    width: 24px;
                    top: 0;
                    left: -9px;
                }
            }
        }

        &__step-2 {
            h2 {
                margin: 15px 0 20px;
            }
            .select {
                .el-select {
                    outline: 1px inset #000;
                    input {
                        border-color: transparent;
                        font-size: 17px
                    }
                }
            }
        }

        &__step-3 {
            h2 {
                margin: 25px 0 20px;
            }

            .full-name {
                display: flex;

                .first-name {
                    width: 40%;
                    margin-right: 10px;
                }

                .last-name {
                    width: calc(60% - 11px);
                }
            }
        }

        &__step-3 {
            .input-container {
                margin-bottom: 15px;
            }

            .input-container.variant-1 input,
            .phone-input-container.variant-1 input {
                background-color: #ffffff;
                font-size: 14px;
                border-radius: unset;
                border: 1px solid #000000;
            }

        }

        &__step-4 {
            .step-title {
                margin-top: 50px;
            }

            .radio-button-group {
                display: flex;
            }

            .select {
                margin-bottom: 15px;

                .el-select {
                    outline: 1px inset #000;
                    input {
                        background-color: #ffffff;
                        border-color: transparent;
                        font-size: 17px
                    }
                }
            }

            .card-date {
                display: flex;
                flex-wrap: wrap;
                width: 70%;
                padding-right: 30px;
                margin-bottom: 10px;
            }

            .card-date > .label {
                width: 100%;
                margin-bottom: 6px;
            }

            .card-date > div {
                width: calc(40% - 5px);
                margin-right: 10px;
            }

            .cvv-field {
                width: calc(30%);
            }

            .el-select-dropdown__item {
                font-size: 17px;
                font-weight: 700;
            }

            .input-container.variant-1 input {
                background-color: #ffffff;
                border-radius: unset;
                border: 1px solid #000000;
            }

            .input-container {
                margin-bottom: 15px;
            }

            #product-image-body {
                max-height: 100px;
            }

            .purchase-button-text {
              box-sizing: border-box;
              color: rgb(255, 255, 255);
              cursor: pointer;
              text-align: center;
              text-shadow: rgba(0, 0, 0, 0.3) -1px -1px 0;
              text-transform: capitalize;
              column-rule-color: rgb(255, 255, 255);
              perspective-origin: 0 0;
              transform-origin: 0 0;
              caret-color: rgb(255, 255, 255);
              border: 0 none rgb(255, 255, 255);
              font: normal normal 700 normal 18px / 25.7143px "Noto Sans", sans-serif;
              outline: rgb(255, 255, 255) none 0;

              &:after {
                box-sizing: border-box;
                color: rgb(255, 255, 255);
                cursor: pointer;
                text-align: center;
                text-shadow: rgba(0, 0, 0, 0.3) -1px -1px 0;
                text-transform: capitalize;
                column-rule-color: rgb(255, 255, 255);
                caret-color: rgb(255, 255, 255);
                border: 0 none rgb(255, 255, 255);
                font: normal normal 700 normal 18px / 25.7143px "Noto Sans", sans-serif;
                outline: rgb(255, 255, 255) none 0;
              }

              &:before {
                box-sizing: border-box;
                color: rgb(255, 255, 255);
                cursor: pointer;
                text-align: center;
                text-shadow: rgba(0, 0, 0, 0.3) -1px -1px 0;
                text-transform: capitalize;
                column-rule-color: rgb(255, 255, 255);
                caret-color: rgb(255, 255, 255);
                border: 0 none rgb(255, 255, 255);
                font: normal normal 700 normal 18px / 25.7143px "Noto Sans", sans-serif;
                outline: rgb(255, 255, 255) none 0;
              }
            }

            .purchase-button-disabled {
              background-color: #fff;
              bottom: 0;
              left: 0;
              opacity: .5;
              position: absolute;
              right: 0;
              top: 0;
              z-index: 1;
            }
        }

        &__bottom {
            margin-top: 15px;
            display: flex;
            flex-direction: column;

            img {
                width: 80%;
                margin: 0 auto;
            }

            &__safe {
                p {
                    text-align: center;
                    font-size: 13px;
                    padding-top: 20px;
                }

                p i {
                    position: relative;
                    margin-right: 4px;
                    top: 2px;
                    font-size: 18px;
                    color: #409EFF;
                }
            }
        }
    }

    .smc7-popover-variant {
        .el-select-dropdown__item {
            height: auto;
        }

        .select__label {
            opacity: 1;

            & > div {
                display: flex;
                align-items: center;

                img {
                    height: 80px;
                    width: auto;
                    margin-right: 25px;
                }

                span {
                    font-size: 17px;
                    font-weight: 700;
                }
            }
        }
    }

    @media screen and ($s-down) {
        .smc7 {
            &__product {
                max-width: 100%;
                flex-direction: column;

                .image-wrapper {
                    width: 100%;
                }

                .advantages {
                    width: 100%;
                }
            }
            &__step-4 {
                margin-top: 10px;

                .step-title {
                    margin-top: 20px;
                }
            }
        }

    }
</style>
