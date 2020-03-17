<template>
    <div v-if="$v && !showPreloader">
        <div class="container smc7">
            <div class="row">
                <div class="container paper smc7__product">
                    <div class="col-md-7 image-wrapper">
                        <img
                          class="lazy"
                          id="product-image-head"
                          :data-src="productImage"
                          alt=""
                        >
                    </div>
                    <div
                            class="col-md-5 advantages"
                            v-html="product.description"
                    >
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="container">
                    <ProductOffer />
                </div>
            </div>

            <div class="row">
                <div class="col-md-7">
                    <div class="paper smc7__deal">
                        <div class="d-flex">
                            <div class="smc7__sale">
                                <SaleBadge />
                            </div>
                            <div class="smc7__deal__text">
                                <p v-html="freeShippingToday" />
                            </div>

                        </div>

                        <div class="smc7__step-1">
                            <h2 class="step1-title"><span>{{textStep}}</span> 1: <span>{{textChooseDeal}}</span></h2>

                            <Installments
                              popperClass="emc1-popover-variant"
                              :extraFields="extraFields"
                              :form="form" />

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
                                      :quantityOfInstallments="quantityOfInstallments"
                                      :item="{
                                        ...item,
                                        value: item.totalQuantity,
                                      }"
                                      :key="item.totalQuantity"
                                      :showShareArrow="item.totalQuantity === 5"/>
                            </radio-button-group>
                        </div>

                        <div class="smc7__step-2 variant-selection"
                          v-if="isShowVariant"
                        >
                            <h2><span>{{textStep}}</span> 2: <span>{{textSelectVariant}}</span></h2>
                            <Variant
                              :$v="$v.form.variant"
                              :form="form"
                              name="variant"
                              @change="onVariantChange" />
                        </div>

                        <div class="smc7__step-3">
                            <h2><span>{{textStep}}</span> {{ !isShowVariant ? 2 : 3  }}: <span>{{textContactInformation}}</span></h2>
                            <div class="full-name">
                              <FirstName
                                @check_for_leads_request="check_for_leads_request"
                                :$v="$v.form.fname"
                                :placeholder="true"
                                :form="form"
                                name="fname" />
                              <LastName
                                @check_for_leads_request="check_for_leads_request"
                                :$v="$v.form.lname"
                                :placeholder="true"
                                :form="form"
                                name="lname" />
                            </div>
                            <Email
                              @check_for_leads_request="check_for_leads_request"
                              :$v="$v.form.email"
                              :placeholder="true"
                              :form="form"
                              name="email" />
                            <Phone
                              @check_for_leads_request="check_for_leads_request"
                              :$v="$v.form.phone"
                              :ccform="form"
                              ccname="countryCodePhoneField"
                              :placeholder="true"
                              :form="form"
                              name="phone" />
                        </div>
                    </div>
                </div>
                <div class="col-md-5 smc7__step-4">
                    <div class="paper">
                        <div class="d-flex">
                            <div class="smc7__step-4__product">
                                <h2>{{ product.long_name }}</h2>
                                <p>{{ textGet }} {{ product.prices['1'].discount_percent }}% {{ textOffTodayFreeShipping }}</p>
                            </div>
                            <img id="product-image-body" :src="productImage" alt="Product image">
                        </div>
                        <h2 class="step-title"><span>{{textStep}}</span> {{ !isShowVariant ? 3 : 4  }}: <span>{{textContactInformation}}</span></h2>
                        <payment-form-smc7
                          :extraFields="extraFields"
                          :stateExtraField="stateExtraField"
                          :paymentMethodURL="paymentMethodURL"
                          @setPaymentMethodByCardNumber="setPaymentMethodByCardNumber"
                          :paymentForm="form"
                          :$v="$v" />
                        <Warranty
                          :form="form"
                          class="small" />
                        <PurchasAlreadyExists v-if="isPurchasAlreadyExists"/>
                        <template v-else>
                            <Terms
                              v-if="isSMC7p || $root.isAffIDEmpty"
                              :$v="$v.form.terms"
                              :form="form"
                              name="terms" />
                            <p v-if="paymentError" id="payment-error" class="error-container" v-html="paymentError"></p>
                            <button
                              :disabled="isSubmitted"
                              v-if="form.paymentProvider !== 'paypal'"
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
                              v-if="$root.paypalEnabled"
                              v-show="form.paymentProvider === 'paypal'"
                              :createOrder="paypalCreateOrder"
                              :onApprove="paypalOnApprove"
                              :$vterms="$v.form.terms"
                              :$vvariant="$v.form.variant"
                              :$vdeal="$v.form.deal"
                              @click="paypalSubmit"
                            >{{ paypalRiskFree }}</paypal-button>
                            <p v-if="paypalPaymentError" id="paypal-payment-error" class="error-container" v-html="paypalPaymentError"></p>
                        </template>
                        <div class="smc7__bottom">
                            <img
                              class="lazy"
                              :data-src="imageSafePayment.url"
                              :alt="imageSafePayment.title"
                              :title="imageSafePayment.title">
                            <div class="smc7__bottom__safe">
                                <p><i class="fa fa-lock"></i>{{ textSafeSSLEncryption }}</p>
                                <p>{{ textCreditCardInvoiced }}<br/>"{{ companyDescriptorPrefix }}{{ productData.billing_descriptor }}"</p>
                                <p v-if="$root.isAffIDEmpty" v-html="companyAddress"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <el-dialog
                @click="isOpenPromotionModal = false"
                class="cvv-popup"
                :title="promo_modal_title"
                :lock-scroll="false"
                :visible.sync="isOpenPromotionModal">
            <div class="cvv-popup__content">
                <p class="error-container">
                    {{ promo_modal_text }}
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
  import * as extraFields from '../mixins/extraFields';
  import { preparePurchaseData } from "../utils/checkout";
  import RadioButtonItemDeal from "./common/RadioButtonItemDeal";
  import PurchasAlreadyExists from './common/PurchasAlreadyExists';
  import Installments from './common/extra-fields/Installments';
  import Variant from './common/common-fields/Variant';
  import FirstName from './common/common-fields/FirstName';
  import LastName from './common/common-fields/LastName';
  import Email from './common/common-fields/Email';
  import Phone from './common/common-fields/Phone';
  import Terms from './common/common-fields/Terms';
  import Warranty from './common/Warranty';
  import SaleBadge from './common/SaleBadge';
  import ProductOffer from '../components/common/ProductOffer';
  import smc7validation from "../validation/smc7-validation";
  import globals from '../mixins/globals';
  import queryToComponent from '../mixins/queryToComponent';
  import scrollToError from '../mixins/formScrollToError';
  import blackFriday from '../mixins/blackFriday';
  import christmas from '../mixins/christmas';
  import {fade} from "../utils/common";
  import { t, timage } from '../utils/i18n';
  import purchasMixin from '../mixins/purchas';
  import { paypalCreateOrder, paypalOnApprove } from '../utils/emc1';
  import { ipqsCheck } from '../services/ipqs';
  import { checkForLeadsRequest, sendCheckoutRequest, get3dsErrors } from '../utils/checkout';
  import Spinner from './common/preloaders/Spinner';
  import { queryParams } from  '../utils/queryParams';
  import logger from '../mixins/logger';


  export default {
    name: 'smc7',
    components: {
      SaleBadge,
      Installments,
      RadioButtonItemDeal,
      ProductOffer,
      PurchasAlreadyExists,
      Warranty,
      Spinner,
      Variant,
      FirstName,
      LastName,
      Email,
      Phone,
      Terms,
    },
    validations: smc7validation,
    mixins: [
      globals,
      queryToComponent,
      extraFields.tplMixin,
      purchasMixin,
      scrollToError,
      blackFriday,
      christmas,
      logger,
    ],
    props: ['showPreloader', 'skusList'],
    data() {
      return {
        productImage: this.getProductImage(),
        disableAnimation: true,
        paypalPaymentError: '',
        form: {
          isWarrantyChecked: false,
          countryCodePhoneField: js_data.country_code,
          deal: null,
          fname: null,
          lname: null,
          email: null,
          phone: null,
          variant: js_data.product.skus.length === 1 || !js_data.product.is_choice_required
            ? js_data.product.skus[0] && js_data.product.skus[0].code || null
            : null,
          country: js_data.countries.indexOf(js_data.country_code) !== -1
            ? js_data.country_code
            : null,
          streetAndNumber: null,
          city: null,
          zipCode: null,
          paymentProvider: null,
          cardHolder: null,
          cardNumber: null,
          cardDate: null,
          cvv: null,
          terms: null,
        },
        isOpenPromotionModal: false,
        isOpenSpecialOfferModal: false,
        isSubmitted: false,
        paymentError: '',
      }
    },
    created() {
      if (this.queryParams['3ds'] && this.queryParams['3ds'] !== 'success') {
        try {
          const selectedProductData = JSON.parse(localStorage.getItem('selectedProductData')) || {};

          this.form.paymentProvider = selectedProductData.paymentProvider || this.form.paymentProvider;
          this.form.deal = parseInt(selectedProductData.deal, 10) || this.form.deal;
          this.form.variant = selectedProductData.variant || this.form.variant;
          this.form.isWarrantyChecked = selectedProductData.isWarrantyChecked || this.form.isWarrantyChecked;
          this.form.fname = selectedProductData.fname || this.form.fname;
          this.form.lname = selectedProductData.lname || this.form.lname;
          this.form.email = selectedProductData.email || this.form.email;
          this.form.phone = selectedProductData.phone || this.form.phone;
          this.form.countryCodePhoneField = selectedProductData.countryCodePhoneField || this.form.countryCodePhoneField;
          this.form.streetAndNumber = selectedProductData.streetAndNumber || this.form.streetAndNumber;
          this.form.city = selectedProductData.city || this.form.city;
          this.form.zipCode = selectedProductData.zipcode || this.form.zipCode;
          this.form.country = selectedProductData.country || this.form.country;
        }
        catch (err) {
          
        }
      }

      if (this.queryParams['3ds'] === 'failure') {
        get3dsErrors().then(paymentError => {
          this.paymentError = paymentError;

          if (this.form.country === 'br') {
            let ipqs = null; try { ipqs = JSON.parse(localStorage.getItem('3ds_ipqs')); } catch (err) {};

            this.log_data('BRAZIL: SMC7 - Credit Card - 3ds failure', {
              error: paymentError,
              form: { ...this.$v.form.$model, cardNumber: undefined },
              fraud_chance: ipqs ? ipqs.fraud_chance : null,
              ipqs,
            });
          }

          setTimeout(() => {
            const element = document.querySelector('#payment-error');

            if (element && element.scrollIntoView) {
              element.scrollIntoView();
            }
          }, 100);
        });
      }

      if (this.queryParams['3ds'] === 'pending' && this.queryParams['bs_pf_token']) {
        setTimeout(() => {
          this.isSubmitted = true;

          sendCheckoutRequest({ bs_3ds_pending: true })
            .then(res => {
              if (res.paymentError) {
                this.paymentError = res.paymentError;
                this.isSubmitted = false;
              }
            })
            .catch(err => {
              this.paymentError = t('checkout.payment_error');
              this.isSubmitted = false;
            });

          const element = document.querySelector('#purchase-button');

          if (element && element.scrollIntoView) {
            element.scrollIntoView();
          }
        }, 1000);
      }

      setTimeout(() => {
        this.disableAnimation = false;
      }, 1000);
    },
    computed: {
      companyAddress() {
        return js_data.company_address.replace(' - ', '<br>');
      },
      companyDescriptorPrefix() {
        return this.$root.isAffIDEmpty ? js_data.company_descriptor_prefix : '';
      },

      discount: () => t('checkout.header_banner.discount'),
      textStep: () => t('checkout.step'),
      textChooseDeal: () => t('checkout.choose_deal'),
      textArtcile: () => t('checkout.article'),
      textPrice: () => t('checkout.header_banner.price'),
      freeShippingToday: () => t('checkout.free_shipping_today'),
      textSelectVariant: () => t('checkout.select_variant'),
      textContactInformation: () => t('checkout.contact_information'),
      textSubmitButton: () => t('checkout.payment_form.submit_button'),
      paypalRiskFree: () => t('checkout.paypal.risk_free'),
      textCreditCardInvoiced: () => t('checkout.credit_card_invoiced'),
      textSafeSSLEncryption: () => t('checkout.safe_sll_encryption'),
      checkoutmainDealErrorText: () => t('checkout.main_deal.error'),
      okText: () => t('checkout.main_deal.error_popup.button'),
      textFirstNameRequired: () => t('checkout.payment_form.first_name.required'),
      textLastNameRequired: () => t('checkout.payment_form.last_name.required'),
      textPaymentError: () => t('checkout.payment_error'),
      textGet: () => t('checkout.get'),
      textOffTodayFreeShipping: () => t('checkout.off_today_free_shipping'),

      imageSafePayment: () => timage('safe_payment'),

      isSMC7p() {
        return js_query_params.tpl === 'smc7p';
      },

      isShowVariant() {
        return this.variantList.length > 1 && (!js_query_params.variant || js_query_params.variant === '0');
      },

      product() {
        return js_data.product;
      },
      productData() {
        return js_data.product;
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

      purchase() {
        const variant = this.form.variant || (js_data.product.skus[0] && js_data.product.skus[0].code) || null;

        return preparePurchaseData({
          purchaseList: this.productData.prices,
          product_name: this.productData.product_name,
          installments: this.form.installments,
          quantityToShow: [1, 2, 3, 4, 5],
          customOrder: true,
          variant,
        });
      },

      variantList() {
        return this.skusList.map((it) => ({
          label: it.name,
          text: `<div><img src="${it.quantity_image[1]}" alt=""><span>${it.name}</span></div>`,
          value: it.code,
          imageUrl: it.quantity_image[1]
        }));
      },

      promo_modal_title() {
        if (this.$v.form.deal.$invalid) {
          return t('checkout.main_deal.error_popup.title');
        } else if (this.$v.form.variant.$invalid) {
          return t('checkout.select_variant');
        }
      },

      promo_modal_text() {
        if (this.$v.form.deal.$invalid) {
          return t('checkout.main_deal.error_popup.message');
        } else if (this.$v.form.variant.$invalid) {
          return t('checkout.select_variant');
        }
      },

    },
    mounted() {
      this.refreshTopBlock();
      this.lazyload_update();
    },
    updated() {
      this.lazyload_update();
    },
    watch: {
      'form.deal'(value) {
        window.selectedOffer = value ? 1 : 0;
        history.pushState({}, '', location.href);
      },
      'form.paymentProvider'(value) {
        window.selectedPayment = value;
        history.pushState({}, '', location.href);
      },
      purchase() {
        this.refreshTopBlock();
      },
      'form.payment_method'() {
        if (this.form.payment_method === 'instant_transfer') {
          this.form.paymentProvider = 'paypal';
        } else if (this.form.payment_method === 'eps') {
          this.form.paymentProvider = 'apm';
        } else {
          this.form.paymentProvider = 'credit-card';
        }
      },
    },
    methods: {
      onVariantChange() {
        this.animateProductImage();
      },
      refreshTopBlock() {
        const oldPrice = document.querySelector('#old-price');
        const newPrice = document.querySelector('#new-price');
        let oldValueText, valueText;

        switch(this.form.installments) {
          case 3:
            oldValueText = js_data.product.prices[1].installments3_old_value_text;
            valueText = js_data.product.prices[1].installments3_value_text;
            break;
          case 6:
            oldValueText = js_data.product.prices[1].installments6_old_value_text;
            valueText = js_data.product.prices[1].installments6_value_text;
            break;
          case 1:
          default:
            oldValueText = js_data.product.prices[1].old_value_text;
            valueText = js_data.product.prices[1].value_text;
            break;
        }

        if (oldPrice) {
          document.querySelector('#old-price').innerHTML = this.quantityOfInstallments + oldValueText;
        }

        if (newPrice) {
          document.querySelector('#new-price').innerHTML = this.quantityOfInstallments + valueText;
        }
      },
      check_for_leads_request() {
        const phone = this.form.phone
          ? this.dialCode + this.form.phone.replace(/[^0-9]/g, '')
          : '';

        checkForLeadsRequest(this.form.variant, this.form.fname, this.form.lname, this.form.email, phone, !this.$v.form.phone.$invalid);
      },
      submit() {
        let ipqsResult = null;

        this.$v.form.$touch();

        if (this.$v.form.$invalid && this.form.country === 'br') {
          this.log_data('BRAZIL: SMC7 - Credit Card - form validation', {
            invalid: Object.keys(this.$v.form)
              .filter(name => name !== 'cardNumber' && !!this.$v.form[name].$invalid)
              .reduce((acc, name) => { acc[name] = this.$v.form.$model[name]; return acc; }, {}),
            form: { ...this.$v.form.$model, cardNumber: undefined },
          });
        }

        if (this.$v.form.deal.$invalid) {
          const element = document.querySelector('.smc7__deal');

          if (element && element.scrollIntoView) {
            element.scrollIntoView();
          }

          this.setPromotionalModal(true);
          return;
        } else if (this.$v.form.variant.$invalid) {
          const element = document.querySelector('.variant-selection');

          if (element && element.scrollIntoView) {
            element.scrollIntoView();
          }

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

        const phoneNumber = (this.form.phone || '').replace(/[^0-9]/g, '');
        const cardNumber = (this.form.cardNumber || '').replace(/[^0-9]/g, '');

        if (this.form.emailForceInvalid) {
          if (this.form.country === 'br') {
            this.log_data('BRAZIL: SMC7 - Credit Card - email blocked', {
              email: this.form.email,
              form: { ...this.$v.form.$model, cardNumber: undefined },
            });
          }

          return setTimeout(() => {
            this.paymentError = t('checkout.abuse_error');
            this.isSubmitted = false;
          }, 1000);
        }

        let data = {
          deal: this.form.deal,
          variant: this.form.variant,
          isWarrantyChecked: this.form.isWarrantyChecked,
          paymentProvider: this.form.paymentProvider,
          fname: this.form.fname,
          lname: this.form.lname,
          email: this.form.email,
          phone: this.form.phone,
          countryCodePhoneField: this.form.countryCodePhoneField,
          streetAndNumber: this.form.streetAndNumber,
          city: this.form.city,
          zipcode: this.form.zipCode,
          country: this.form.country,
        };

        this.setExtraFieldsForLocalStorage(data);
        this.setDataToLocalStorage(data);

        Promise.resolve()
          .then(() => {
            let data = {
              order_amount: this.getOrderAmount(this.form.deal, this.form.isWarrantyChecked),
              billing_first_name: this.form.fname,
              billing_last_name: this.form.lname,
              billing_country: this.form.country,
              billing_address_1: this.form.streetAndNumber,
              billing_city: this.form.city,
              billing_region: this.extraFields.state
                ? this.form.state
                : '',
              billing_postcode: this.form.zipCode,
              billing_email: this.form.email,
              billing_phone: this.dialCode + phoneNumber,
            };

            if (this.form.paymentProvider === 'credit-card') {
              data.credit_card_bin = cardNumber.substr(0, 6);
              data.credit_card_expiration_month = this.form.cardDate.split('/')[0];
              data.credit_card_expiration_year = this.form.cardDate.split('/')[1];
              data.cvv_code = this.form.cvv;

              if (window.sha256) {
                data.credit_card_hash = sha256(cardNumber);
              }
            }

            return ipqsCheck(data);
          })
          .then(result => {
            ipqsResult = result;
          })
          .then(() => {
            let data = {
              product: {
                sku: this.form.variant,
                qty: parseInt(this.form.deal, 10),
                is_warranty_checked: this.form.isWarrantyChecked,
              },
              contact: {
                phone: {
                  country_code: this.dialCode,
                  number: phoneNumber,
                },
                first_name: this.form.fname,
                last_name: this.form.lname,
                email: this.form.email,
              },
              address: {
                city: this.form.city,
                country: this.form.country,
                zip: this.form.zipCode,
                street: this.form.streetAndNumber,
              },
              ipqs: ipqsResult,
            };

            if (this.form.paymentProvider === 'credit-card') {
              data.card = {
                number: cardNumber,
                cvv: this.form.cvv,
                month: this.form.cardDate.split('/')[0],
                year: '20' + this.form.cardDate.split('/')[1],
              };

              if (this.$root.isAffIDEmpty) {
                data.card.holder = this.form.cardHolder;
              }
            }

            this.setExtraFieldsForCardPayment(data, this.form.paymentProvider);

            return sendCheckoutRequest(data, this.form.paymentProvider);
          })
          .then(res => {
            if (res.status !== 'ok' && this.form.country === 'br') {
              this.log_data('BRAZIL: SMC7 - Credit Card - response', {
                error: res.paymentError,
                res: { ...res, paymentError: undefined },
                form: { ...this.$v.form.$model, cardNumber: undefined },
                fraud_chance: ipqsResult.fraud_chance,
                ipqs: ipqsResult,
              });
            }

            if (res.paymentError) {
              this.paymentError = res.paymentError;
              this.isSubmitted = false;
            }
          })
          .catch(err => {
            this.paymentError = t('checkout.payment_error');
            this.isSubmitted = false;
          });
      },
      setPromotionalModal(val) {
        this.isOpenPromotionModal = val
      },
      paypalCreateOrder () {
        let ipqsResult = null;

        this.form.paymentProvider = 'paypal';

        const currency = !js_query_params.cur || js_query_params.cur === '{aff_currency}'
          ? js_data.product.prices.currency
          : js_query_params.cur;

        this.setDataToLocalStorage({
          deal: this.form.deal,
          variant: this.form.variant,
          isWarrantyChecked: this.form.isWarrantyChecked,
          paymentProvider: 'paypal',
        });

        this.paypalPaymentError = '';

        return Promise.resolve()
          .then(() => {
            const data = {
              order_amount: this.getOrderAmount(this.form.deal, this.form.isWarrantyChecked),
            };

            return ipqsCheck(data);
          })
          .then(result => {
            ipqsResult = result;
          })
          .then(() => {
            if (ipqsResult && ipqsResult.recent_abuse) {
              if (this.form.country === 'br') {
                this.log_data('BRAZIL: SMC7 - PayPal - IPQS - recent_abuse', {
                  fraud_chance: ipqsResult.fraud_chance,
                  ipqs: ipqsResult,
                });
              }

              return setTimeout(() => {
                this.paypalPaymentError = t('checkout.abuse_error');
              }, 1000);
            }

            return paypalCreateOrder({
              xsrfToken: document.head.querySelector('meta[name="csrf-token"]').content,
              sku_code: this.form.variant,
              sku_quantity: this.form.deal,
              is_warranty_checked: this.form.isWarrantyChecked,
              page_checkout: document.location.href,
              cur: currency,
              offer: js_query_params.offer || null,
              affiliate: js_query_params.affiliate || null,
              ipqsResult,
            });
          })
          .then(res => {
            if (res.error && this.form.country === 'br') {
              this.log_data('BRAZIL: SMC7 - PayPal - response', {
                error: res.paypalPaymentError,
                res: { ...res, paypalPaymentError: undefined },
                fraud_chance: ipqsResult.fraud_chance,
                ipqs: ipqsResult,
              });
            }

            if (res.paypalPaymentError) {
              this.paypalPaymentError = res.paypalPaymentError;
            }

            return res;
          })
          .catch(err => {
            this.paypalPaymentError = t('checkout.payment_error');
          });
      },

      paypalSubmit() {
        if (this.$v.form.deal.$invalid) {
          this.$v.form.deal.$touch();

          const element = document.querySelector('.smc7__deal');

          if (element && element.scrollIntoView) {
            element.scrollIntoView();
          }

          this.isOpenPromotionModal = true;
        } else if (this.$v.form.variant.$invalid) {
          this.$v.form.variant.$touch();

          const element = document.querySelector('.variant-selection');

          if (element && element.scrollIntoView) {
            element.scrollIntoView();
          }

          this.isOpenPromotionModal = true;
        } else if (this.$v.form.terms && this.$v.form.terms.$invalid) {
          const element = document.querySelector('.terms-checkbox');

          if (element && element.scrollIntoView) {
            element.scrollIntoView();
          }

          this.$v.form.terms.$touch();
        }
      },

      paypalOnApprove(data) {
        return paypalOnApprove(data);
      },

      getProductImage() {
        const isInitial = !this.productImage;
        const quantity = /*this.form && +this.form.deal || */1;

        const skus = Array.isArray(js_data.product.skus)
          ? js_data.product.skus
          : [];

        const variant = (this.form && this.form.variant) || (skus[0] && skus[0].code) || null;
        const skuVariant = skus.find && skus.find(sku => variant === sku.code) || null;

        const productImage = js_data.product.image[+(js_query_params.image || null) - 1] || js_data.product.image[0];
        const skuImage = skuVariant && (skuVariant.quantity_image[quantity] || skuVariant.quantity_image[1]) || productImage;

        return isInitial ? productImage : skuImage;
      },

      animateProductImage() {
        const newProductImage = this.getProductImage();

        if (newProductImage !== this.productImage) {
          if (!this.disableAnimation) {
            const imgPreload = new Image();
            imgPreload.src = newProductImage;

            fade('out', 300, document.querySelector('#product-image-head'), true)
              .then(() => {
                this.productImage = newProductImage;
                setTimeout(() => fade('in', 300, document.querySelector('#product-image-head'), true), 200);
              });

            fade('out', 300, document.querySelector('#product-image-body'), true)
              .then(() => {
                setTimeout(() => fade('in', 300, document.querySelector('#product-image-body'), true), 200);
              });
          } else {
            this.productImage = newProductImage;
          }
        }
      },
    },
  }
</script>

<style lang="scss">
    @import "../../sass/variables";

    .smc7.container {
        max-width: 970px;
    }

    .smc7__step-1__titles {
      display: flex;
      margin: 17px 8px 17px 12px;

      h3 {
        margin: 0;
        padding: 0;

        &:first-child {
          flex-grow: 1;
        }
      }
    }

    .smc7 {

      .variant-field-label {
        display: none;
      }

      .variant-field-input {
        border-color: #000;
        border-radius: 0;
      }

      .label-container-radio__discount {
        color: #16a085;
      }

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

                    [dir="rtl"] & {
                      padding: 15px 38px 15px 15px;
                    }

                    &:before {
                        position: absolute;
                        top: 18px;
                        left: 15px;
                        display: inline-block;
                        font: normal normal normal 14px/1 FontAwesome;
                        font-size: inherit;
                        content: "\f00c";
                        color: green;

                        [dir="rtl"] & {
                          left: auto;
                          right: 15px;
                          transform: scaleX(-1);
                        }
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
        }

        .el-select .el-input.is-focus .el-input__inner,
        .el-select .el-input__inner:focus {
            border-color: #C0C4CC;
        }

        &__step-1 {
            .step1-title {
              margin-top: 20px;
            }
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

                    [dir="rtl"] & {
                      text-align: left;
                    }
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

                [dir="rtl"] & {
                  left: auto;
                  right: -30px;
                  transform: rotate(145deg);
                }

                @media screen and ($s-down) {
                    width: 24px;
                    top: 0;
                    left: -9px;

                    [dir="rtl"] & {
                      left: auto;
                      right: -9px;
                    }
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

              #first-name-field {
                width: 40%;
                padding-right: 10px;

                [dir="rtl"] & {
                  padding-left: 10px;
                  padding-right: 0;
                }
              }

              #last-name-field {
                width: 60%;
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
                flex-direction: row;
                flex-wrap: wrap;
                width: 70%;
                padding-right: 30px;
                margin-bottom: 10px;

                [dir="rtl"] & {
                  padding-left: 30px;
                  padding-right: 0;
                }

                .select {
                  margin-bottom: 0;
                }
            }

            .card-date > .label {
                width: 100%;
                margin-bottom: 6px;
            }

            .card-date > div {
                flex-grow: 1;
                width: calc(40% - 5px);
                margin-right: 10px;

                [dir="rtl"] & {
                  margin-left: 10px;
                  margin-right: 0;
                }
            }

            .cvv-field {
                width: calc(30%);
            }

            .el-select-dropdown__item {
                font-size: 17px;
                font-weight: 700;
            }

            .input-container.variant-1 input,
            .select.variant-1 select {
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
                flex-shrink: 0;
            }

            &__safe {
              padding: 8px 0;

                p {
                    padding: 4px 0;
                    text-align: center;
                    font-size: 13px;
                }

                p i {
                    position: relative;
                    margin-right: 4px;
                    top: 2px;
                    font-size: 18px;
                    color: #409EFF;

                    [dir="rtl"] & {
                      margin-left: 4px;
                      margin-right: 0;
                    }
                }
            }
        }

        #warranty-field-button {
          margin-bottom: 15px;
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

                    [dir="rtl"] & {
                      margin-left: 25px;
                      margin-right: 0;
                    }
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
