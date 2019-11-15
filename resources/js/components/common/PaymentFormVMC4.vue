<template>
  <div v-if="$v">
    <div class="d-flex flex-column payment-form-vmc4">
      <div class="vmc4__deal">
      <div class="col-md-12">
        <h4 class="form-steps-title">
          <b>{{step}}/{{maxSteps}}</b>
          <span v-if="step === 1" v-html="textChooseDeal"></span>
          <span v-if="step === 2" v-html="textContactInformation"></span>
          <span v-if="step === 3" v-html="textPaymentMethod"></span>
        </h4>
        <div class="step step-1" v-if="step === 1">
          <Installments
            popperClass="emc1-popover-variant"
            :extraFields="extraFields"
            :form="vmc4Form" />
          <radio-button-group
            :withCustomLabels="false"
            v-model="vmc4Form.deal"
            :list="dealList"
            />
            <template v-if="isShowVariant">
              <h2 v-html="textSelectVariant"></h2>
              <select-field
                popperClass="smc7-popover-variant"
                v-model="vmc4Form.variant"
                :rest="{
                  placeholder: 'Variant'
                }"
                :list="variantList" />
            </template>
        </div>
        <div class="step step-2" v-if="step === 2">
          <div class="full-name">
            <text-field
                :validation="$v.form.stepTwo.fname"
                :validationMessage="textFirstNameRequired"
                theme="variant-1"
                :label="textFirstName"
                class="first-name"
                v-model="form.stepTwo.fname"/>
            <text-field
                :validation="$v.form.stepTwo.lname"
                :validationMessage="textLastNameRequired"
                theme="variant-1"
                :label="textLastName"
                class="last-name"
                v-model="form.stepTwo.lname"/>
          </div>
          <Email
            name="email"
            :form="form.stepTwo"
            :$v="$v.form.stepTwo.email" />
          <phone-field
              :validation="$v.form.stepTwo.phone"
              @onCountryChange="setCountryCodeByPhoneField"
              :validationMessage="textPhoneRequired"
              :countryCode="form.countryCodePhoneField"
              theme="variant-1"
              :label="textPhone"
              :rest="{
                autocomplete: 'off'
              }"
              v-model="form.stepTwo.phone"/>
        </div>
        <div class="step step-3" v-if="step === 3">
          <h3 v-html="textPaySecurely"></h3>
          <payment-provider-radio-list
            v-model="form.paymentProvider"
            @input="activateForm" />
          <paypal-button
            v-show="vmc4Form.installments === 1"
            :createOrder="paypalCreateOrder"
            :onApprove="paypalOnApprove"
            :$vdeal="$v.vmc4Form.deal"
            @click="paypalSubmit"
          >{{ paypalRiskFree }}</paypal-button>
          <p v-if="paypalPaymentError" id="paypal-payment-error" class="error-container" v-html="paypalPaymentError"></p>
          <slot name="warranty" />
          <form v-if="form.paymentProvider && isFormShown">
            <CardType
              class="input-container"
              :extraFields="extraFields"
              :form="vmc4Form"
              :$v="$v" />
            <text-field
                :validation="$v.form.stepThree.cardNumber"
                :rest="{
                  pattern: '\\d*',
                  type: 'tel',
                  placeholder: '**** **** **** ****',
                  autocomplete: 'cc-number',
                    'data-bluesnap': 'encryptedCreditCard'
                  }"
                :validationMessage="textCardNumberRequired"
                class="card-number"
                theme="variant-1"
                :label="textCardNumber"
                v-model="form.stepThree.cardNumber"
                :prefix="`<img src='${$parent.paymentMethodURL}' alt='Card Number' />`"
                :postfix="`<i class='fa fa-lock'></i>`"
            />
            <div class="card-info">
              <div class="d-flex input-container" :class="{ invalid: $v.form && $v.form.stepThree && $v.form.stepThree.month && $v.form.stepThree.month.$dirty && $v.form.stepThree.year && $v.form.stepThree.year.$dirty && ($v.form.stepThree.month.$invalid || $v.form.stepThree.year.$invalid || isCardExpired) }">
                <div style="flex-grow:1">
                  <div class="card-info__labels">
                    <span class="label" v-html="textCardValidUntil"></span>
                  </div>
                  <div class="card-date d-flex">
                    <select-field
                      :standart="true"
                      :validation="$v.form.stepThree.month"
                      :validationMessage="textCardValidMonthRequired"
                      :rest="{
                        placeholder: textCardValidMonthPlaceholder
                      }"
                      theme="variant-1"
                      :list="Array.apply(null, Array(12)).map((_, idx) => ({ value: idx + 1 }))"
                      v-model="form.stepThree.month"
                    />
                    <select-field
                      :standart="true"
                      :validation="$v.form.stepThree.year"
                      :validationMessage="textCardValidYearRequired"
                      :rest="{
                        placeholder: textCardValidYearPlaceholder
                      }"
                      theme="variant-1"
                      :list="Array.apply(null, Array(12)).map((_, ind) => ({ value: new Date().getFullYear() + ind }))"
                      v-model="form.stepThree.year"
                    />
                  </div>
                  <span
                    class="error"
                    v-if="form.stepThree.month && form.stepThree.year && isCardExpired"
                    v-html="textCardExpired"></span>
                </div>
                <div>
                  <div class="card-cvv">
                    <div class="card-info__labels">
                      <span class="label" v-html="textCardCVV"></span>
                    </div>
                    <text-field
                      :validation="$v.form.stepThree.cvv"
                      @click-postfix="openCVVModal"
                      :validationMessage="textCardCVVRequired"
                      class="cvv-field"
                      theme="variant-1"
                      :rest="{
                        autocomplete: 'cc-csc',
                        'data-bluesnap': 'encryptedCvv'
                      }"
                      v-model="form.stepThree.cvv"
                      postfix="<i class='fa fa-question-circle cursor-pointer'></i>"
                    />
                  </div>
                </div>
              </div>
              <DocumentType
                class="input-container"
                :extraFields="extraFields"
                :form="vmc4Form"
                :$v="$v" />
              <DocumentNumber
                :extraFields="extraFields"
                :form="vmc4Form"
                :$v="$v" />
              <District
                :extraFields="extraFields"
                :withPlaceholder="true"
                :form="vmc4Form"
                :$v="$v" />
              <text-field
                :validation="$v.form.stepThree.city"
                :validationMessage="textCityRequired"
                element-loading-spinner="el-icon-loading"
                theme="variant-1"
                :label="textCity"
                :rest="{
                  placeholder: textCityPlaceholder,
                  autocomplete: 'shipping locality'
                }"
                v-model="form.stepThree.city"/>
              <State
                v-if="extraFields.state"
                class="input-container"
                :country="form.stepThree.country"
                :extraFields="extraFields"
                :form="vmc4Form"
                :$v="$v" />
              <text-field
                v-else
                :validation="$v.form.stepThree.state"
                :validationMessage="textStateRequired"
                element-loading-spinner="el-icon-loading"
                theme="variant-1"
                :label="textState"
                :rest="{
                  placeholder: textStatePlaceholder,
                  autocomplete: 'shipping locality'
                }"
                v-model="form.stepThree.state"/>
              <text-field
                :validation="$v.form.stepThree.zipCode"
                :validationMessage="textZipcodeRequired"
                theme="variant-1"
                :label="textZipcode"
                :rest="{
                  placeholder: textZipcodePlaceholder
                }"
                id="zip-code-field"
                v-model="form.stepThree.zipCode"/>
              <select-field
                :standart="true"
                :validation="$v.form.stepThree.country"
                :validationMessage="textCountryRequired"
                theme="variant-1"
                :label="textCountry"
                class="country"
                :rest="{
                  placeholder: textCountryPlaceholder
                }"
                :list="countryList"
                v-model="form.stepThree.country"/>
            </div>
            <el-dialog
              @click="isOpenCVVModal = false"
              class="cvv-popup"
              :title="textCVVPopupTitle"
              :visible.sync="isOpenCVVModal"
            >
              <div class="cvv-popup__content">
                <p v-html="textCVVPopupLine1"></p>
                <div><img :src="$root.cdnUrl + '/assets/images/cvv_popup.jpg'" alt=""></div>
                <p v-html="textCVVPopupLine2"></p>
              </div>
            </el-dialog>
            <p v-if="paymentError" id="payment-error" class="error-container" v-html="paymentError"></p>
            <button
              @click="submit"
              :disabled="isSubmitted"
              :class="{ 'btn-active': !isSubmitted }"
              class="submit-btn"
              type="button"
            >
              <Spinner v-if="isSubmitted" />
              <div v-if="isSubmitted" class="btn-disabled"></div>
              <span :style="{ visibility: isSubmitted ? 'hidden' : 'visible' }" v-html="textSubmitButton"></span>
            </button>
          </form>
        </div>
        <div class="buttons">
          <div class="form-navigation">
            <button @click="isAllowNext(step)" v-if="step !== 3" v-html="textNext" class="next-btn btn-active"></button>
            <button v-if="step > 1" class="back-btn" @click="animatePrevStep">&lt;&lt; <span v-html="textBack"></span></button>
          </div>
        </div>
      </div>
    </div>
    <el-dialog
      @click="isOpenPromotionModal = false"
      class="deal-popup"
      :title="textMainDealErrorPopupTitle"
      :lock-scroll="false"
      :visible.sync="isOpenPromotionModal"
    >
      <div class="deal-popup__content">
        <button
          @click="isOpenPromotionModal = false"
          type="button"
          class="green-button-animated"
        >
          <span class="purchase-button-text" v-html="textMainDealErrorPopupButton"></span>
        </button>
      </div>
    </el-dialog>
    </div>
  </div>

</template>
<script>
  import * as dateFns from 'date-fns';
  import { t } from '../../utils/i18n';
  import { check as ipqsCheck } from '../../services/ipqs';
	import RadioButtonItemDeal from "./RadioButtonItemDeal";
	import PayMethodItem from "./PayMethodItem";
  import queryToComponent from '../../mixins/queryToComponent';
	import { sendCheckoutRequest, get3dsErrors } from "../../utils/checkout";
  import { paypalCreateOrder, paypalOnApprove } from '../../utils/emc1';
	import vmc4validation from "../../validation/vmc4-validation";
  import purchasMixin from '../../mixins/purchas';
  import Spinner from './preloaders/Spinner';
	import {fade} from "../../utils/common";
  import { queryParams } from  '../../utils/queryParams';
  import Installments from './extra-fields/Installments';
  import Email from './common-fields/Email';
  import State from './extra-fields/State';
  import District from './extra-fields/District';
  import CardType from './extra-fields/CardType';
  import DocumentType from './extra-fields/DocumentType';
  import DocumentNumber from './extra-fields/DocumentNumber';

  const searchParams = new URL(location).searchParams;

	export default {
		name: "PaymentFormVMC4",
    mixins: [
      queryToComponent,
      purchasMixin,
    ],
		components: {
      PayMethodItem,
      RadioButtonItemDeal,
      Spinner,
      Installments,
      Email,
      State,
      District,
      CardType,
      DocumentType,
      DocumentNumber,
    },
		validations: vmc4validation,
		props: [
      'productImage',
			'countryList',
			'dealList',
			'variantList',
      'countryCode',
      'extraFields',
      'vmc4Form',
		],
		data() {
			return {
				step: 1,
				maxSteps: 3,
        isFormShown: false,
				isOpenCVVModal: false,
        isOpenPromotionModal: false,
        paymentError: '',
        paypalPaymentError: '',
        isSubmitted: false,
        ipqsResult: null,
				form: {
					stepTwo: {
						fname: null,
						lname: null,
						email: null,
						phone: null,
					},
					stepThree: {
						cardNumber: null,
						month: null,
						year: null,
						cvv: null,
						country: checkoutData.countryCode,
						city: null,
						state: null,
						zipCode: null,
					},
					countryCodePhoneField: checkoutData.countryCode,
					paymentProvider: null,
				},
			}
    },
    created() {
      if (this.queryParams['3ds'] === 'failure') {
        try {
          const selectedProductData = JSON.parse(localStorage.getItem('selectedProductData')) || {};

          this.step = 3;
          this.isFormShown = true;
          this.vmc4Form.deal = selectedProductData.deal || this.vmc4Form.deal;
          this.vmc4Form.variant = selectedProductData.variant || this.vmc4Form.variant;
          this.form.paymentProvider = selectedProductData.paymentProvider || this.form.paymentProvider;
          this.form.stepTwo.fname = selectedProductData.fname || this.form.stepTwo.fname;
          this.form.stepTwo.lname = selectedProductData.lname || this.form.stepTwo.lname;
          this.form.stepTwo.email = selectedProductData.email || this.form.stepTwo.email;
          this.form.stepTwo.phone = selectedProductData.phone || this.form.stepTwo.phone;
          this.form.countryCodePhoneField = selectedProductData.countryCodePhoneField || this.form.countryCodePhoneField;
          this.form.stepThree.city = selectedProductData.city || this.form.stepThree.city;
          this.form.stepThree.state = selectedProductData.state || this.form.stepThree.state;
          this.form.stepThree.zipCode = selectedProductData.zipcode || this.form.stepThree.zipCode;
          this.form.stepThree.country = selectedProductData.country || this.form.stepThree.country;
        }
        catch (err) {
          
        }

        get3dsErrors().then(paymentError => {
          this.paymentError = paymentError;

          setTimeout(() => {
            const element = document.querySelector('#payment-error');

            if (element && element.scrollIntoView) {
              element.scrollIntoView();
            }
          }, 100);
        });
      }
    },
		computed: {
      isShowVariant() {
        return this.variantList.length > 1 && (!searchParams.has('variant') || +searchParams.get('variant') !== 0);
      },
      codeOrDefault () {
        return this.queryParams.product || (checkoutData.product.skus[0] && checkoutData.product.skus[0].code) || null;
      },
      dialCode() {
        const allCountries = window.intlTelInputGlobals.getCountryData();
        const phoneCountryCode = this.form.countryCodePhoneField;
        const country = allCountries.filter(item => item.iso2 === phoneCountryCode).shift();

        return country ? country.dialCode : '1';
      },
      isCardExpired() {
        return !dateFns.isFuture(new Date(this.form.stepThree.year, this.form.stepThree.month));
      },
      textState() {
        return t('checkout.payment_form.state', {}, { country: this.form.stepThree.country });
      },
      textStatePlaceholder() {
        return t('checkout.payment_form.state.placeholder', {}, { country: this.form.stepThree.country });
      },
      textZipcode() {
        return t('checkout.payment_form.zipcode', {}, { country: this.form.stepThree.country });
      },
      textZipcodePlaceholder() {
        return t('checkout.payment_form.zipcode.placeholder', {}, { country: this.form.stepThree.country });
      },
      textChooseDeal: () => t('checkout.choose_deal'),
      textMainDealErrorPopupTitle: () => t('checkout.main_deal.error_popup.title'),
      textMainDealErrorPopupButton: () => t('checkout.main_deal.error_popup.button'),
      textSelectVariant: () => t('checkout.select_variant'),
      textContactInformation: () => t('checkout.contact_information'),
      textPaymentMethod: () => t('checkout.payment_method'),
      textFirstName: () => t('checkout.payment_form.first_name'),
      textFirstNameRequired: () => t('checkout.payment_form.first_name.required'),
      textLastName: () => t('checkout.payment_form.last_name'),
      textLastNameRequired: () => t('checkout.payment_form.last_name.required'),
      textPhone: () => t('checkout.payment_form.phone'),
      textPhoneRequired: () => t('checkout.payment_form.phone.required'),
      textPaySecurely: () => t('checkout.pay_securely'),
      textCardNumber: () => t('checkout.payment_form.card_number'),
      textCardNumberRequired: () => t('checkout.payment_form.card_number.required'),
      textCardValidUntil: () => t('checkout.payment_form.card_valid_until'),
      textCardValidMonthRequired: () => t('checkout.payment_form.card_valid_month.required'),
      textCardValidMonthPlaceholder: () => t('checkout.payment_form.card_valid_month.placeholder'),
      textCardValidYearRequired: () => t('checkout.payment_form.card_valid_year.required'),
      textCardValidYearPlaceholder: () => t('checkout.payment_form.card_valid_year.placeholder'),
      textCardCVV: () => t('checkout.payment_form.card_cvv'),
      textCardCVVRequired: () => t('checkout.payment_form.card_cvv.required'),
      textCity: () => t('checkout.payment_form.city'),
      textCityPlaceholder: () => t('checkout.payment_form.city.placeholder'),
      textCityRequired: () => t('checkout.payment_form.city.required'),
      textStateRequired: () => t('checkout.payment_form.state.required'),
      textZipcodeRequired: () => t('checkout.payment_form.zipcode.required'),
      textCountry: () => t('checkout.payment_form.сountry'),
      textCountryPlaceholder: () => t('checkout.payment_form.сountry.placeholder'),
      textCountryRequired: () => t('checkout.payment_form.сountry.required'),
      textCardExpired: () => t('checkout.payment_form.card_expired'),
      textSubmitButton: () => t('checkout.payment_form.submit_button'),
      textCVVPopupTitle: () => t('checkout.payment_form.cvv_popup.title'),
      textCVVPopupLine1: () => t('checkout.payment_form.cvv_popup.line_1'),
      textCVVPopupLine2: () => t('checkout.payment_form.cvv_popup.line_2'),
      paypalRiskFree: () => t('checkout.paypal.risk_free'),
      textPaymentError: () => t('checkout.payment_error'),
      textNext: () => t('checkout.next'),
      textBack: () => t('checkout.back'),
		},
        watch: {
            'form.stepThree.country'(country) {
              this.$parent.reloadPaymentMethods(country);
            },
            'form.stepThree.cardNumber'(newVal, oldValue) {
                newVal = newVal || '';

                if (!newVal.replace(/\s/g, '').match(/^[0-9]{0,19}$/)) {
                  this.form.stepThree.cardNumber = oldValue;
                }

                this.$parent.setPaymentMethodByCardNumber(newVal);
            },
            'form.stepThree.cvv'(newVal, oldValue) {
                if (this.form.stepThree.cvv) {
                    if (newVal.match(/^[0-9]{0,4}$/g)) {
                        this.form.stepThree.cvv = newVal;
                    } else {
                        this.form.stepThree.cvv = oldValue;
                    }
                }
            }


        },
		methods: {
      activateForm() {
        this.isFormShown = true;
      },
			submit() {
				this.$v.form.$touch();
        this.$v.vmc4Form.$touch();

        if (this.$v.form.$vmc4Form || this.$v.form.$invalid) {
          return;
        }

        if (this.isSubmitted) {
          return;
        }

        this.paymentError = '';
        this.isSubmitted = true;

        const phoneNumber = this.form.stepTwo.phone.replace(/[^0-9]/g, '');
        const cardNumber = this.form.stepThree.cardNumber.replace(/\s/g, '');

        let data = {
          deal: this.vmc4Form.deal,
          variant: this.vmc4Form.variant,
          isWarrantyChecked: this.vmc4Form.isWarrantyChecked,
          installments: this.vmc4Form.installments,
          paymentProvider: this.form.paymentProvider,
          fname: this.form.stepTwo.fname,
          lname: this.form.stepTwo.lname,
          email: this.form.stepTwo.email,
          phone: this.form.stepTwo.phone,
          countryCodePhoneField: this.form.countryCodePhoneField,
          city: this.form.stepThree.city,
          state: this.form.stepThree.state,
          zipcode: this.form.stepThree.zipCode,
          country: this.form.stepThree.country,
        };

        this.$parent.setExtraFieldsForLocalStorage(data);
        this.setDataToLocalStorage(data);

        Promise.resolve()
          .then(() => {
            if (this.ipqsResult) {
              return this.ipqsResult;
            }

            const data = {
              billing_first_name: this.form.stepTwo.fname,
              billing_last_name: this.form.stepTwo.lname,
              billing_country: this.form.stepThree.country,
              billing_city: this.form.stepThree.city,
              billing_region: this.form.stepThree.state,
              billing_postcode: this.form.stepThree.zipCode,
              billing_email: this.form.stepTwo.email,
              billing_phone: this.dialCode + phoneNumber,
              credit_card_bin: cardNumber.substr(0, 6),
              credit_card_hash: window.sha256(cardNumber),
              credit_card_expiration_month: ('0' + this.form.stepThree.month).slice(-2),
              credit_card_expiration_year: ('' + this.form.stepThree.year).substr(2, 2),
              cvv_code: this.form.stepThree.cvv,
            };

            return ipqsCheck(data);
          })
          .then(ipqsResult => {
            this.ipqsResult = ipqsResult;
          })
          .then(ipqsResult => {
            /*
            if (this.ipqsResult && this.ipqsResult.recent_abuse) {
              return setTimeout(() => {
                this.paymentError = t('checkout.abuse_error');
                this.isSubmitted = false;
              }, 1000);
            }
            */

            if (this.form.paymentProvider === 'bank-payment') {
              this.isSubmitted = false;
              return;
            }

            if (this.form.paymentProvider === 'credit-card') {
              let data = {
                product: {
                  sku: this.vmc4Form.variant,
                  qty: parseInt(this.vmc4Form.deal, 10),
                  is_warranty_checked: this.vmc4Form.isWarrantyChecked,
                },
                contact: {
                  phone: {
                    country_code: this.dialCode,
                    number: phoneNumber,
                  },
                  first_name: this.form.stepTwo.fname,
                  last_name: this.form.stepTwo.lname,
                  email: this.form.stepTwo.email,
                },
                address: {
                  city: this.form.stepThree.city,
                  country: this.form.stepThree.country,
                  zip: this.form.stepThree.zipCode,
                  state: this.form.stepThree.state,
                  street: 'none',
                },
                card: {
                  number: cardNumber,
                  cvv: this.form.stepThree.cvv,
                  month: ('0' + this.form.stepThree.month).slice(-2),
                  year: '' + this.form.stepThree.year,
                },
                ipqs: this.ipqsResult,
              };

              this.$parent.setExtraFieldsForCardPayment(data);

              if (this.extraFields.district) {
                data.address.state = this.vmc4Form.state;
              }

              sendCheckoutRequest(data)
                .then(res => {
                  if (res.paymentError) {
                    this.paymentError = res.paymentError;
                    this.isSubmitted = false;
                  }
                });
            }
          });
			},
      paypalSubmit() {
        
      },
			setCountryCodeByPhoneField(val) {
				if (val.iso2) {
					this.form.countryCodePhoneField = val.iso2;
				}
			},
			openCVVModal() {
				const node = document.querySelector('.cvv-popup .el-dialog');
				const listener = () => {
					this.isOpenCVVModal = false
				};
				node.removeEventListener('click', listener);
				node.addEventListener('click', listener);

				this.isOpenCVVModal = true
			},
			isAllowNext(currentStep) {
				const isStepOneInvalid = this.$v.vmc4Form.deal.$invalid;
				const isStepTwoInvalid = this.$v.form.stepTwo.$invalid;
				const isStepThreeInvalid =
					this.form.paymentProvider !== 'paypal' &&
          this.$v.form.stepThree.$invalid;

        if (currentStep === 1 && isStepOneInvalid) {
          this.$v.vmc4Form.deal.$touch();
          this.isOpenPromotionModal = true;
        }
        else if (currentStep === 2 && isStepTwoInvalid) {
          this.$v.form.stepTwo.$touch();
        } else if (currentStep === 3 && isStepThreeInvalid) {
          this.$v.form.stepThree.$touch();
        } else {
          this.animateNextStep();
        }
			},
      animateNextStep() {
        fade('out', 300, document.querySelector('.payment-form-vmc4'), true)
          .then(() => {
              this.step++;
              fade('in', 300, document.querySelector('.payment-form-vmc4'), true)
          });
      },
      animatePrevStep() {
        fade('out', 300, document.querySelector('.payment-form-vmc4'), true)
          .then(() => {
              this.step--;
              fade('in', 300, document.querySelector('.payment-form-vmc4'), true)
          });
      },
      paypalCreateOrder() {
        const currency = !searchParams.get('cur') || searchParams.get('cur') === '{aff_currency}'
          ? checkoutData.product.prices.currency
          : searchParams.get('cur');

        this.setDataToLocalStorage({
          deal: this.vmc4Form.deal,
          variant: this.vmc4Form.variant,
          isWarrantyChecked: this.vmc4Form.isWarrantyChecked,
          paymentProvider: 'paypal',
        });

        this.paypalPaymentError = '';

        return Promise.resolve()
          .then(() => {
            if (this.ipqsResult) {
              return this.ipqsResult;
            }

            return ipqsCheck();
          })
          .then(ipqsResult => {
            this.ipqsResult = ipqsResult;
          })
          .then(() => {
            if (this.ipqsResult && this.ipqsResult.recent_abuse) {
              return setTimeout(() => {
                this.paypalPaymentError = t('checkout.abuse_error');
              }, 1000);
            }

            return paypalCreateOrder({
              xsrfToken: document.head.querySelector('meta[name="csrf-token"]').content,
              sku_code: this.codeOrDefault,
              sku_quantity: this.vmc4Form.deal,
              is_warranty_checked: this.vmc4Form.isWarrantyChecked,
              page_checkout: document.location.href,
              cur: currency,
              offer: searchParams.get('offer'),
              affiliate: searchParams.get('affiliate'),
              ipqsResult: this.ipqsResult,
            });
          })
          .then(res => {
            if (res.paypalPaymentError) {
              this.paypalPaymentError = res.paypalPaymentError;
            }

            return res;
          });
      },
      paypalOnApprove(data) {
        this.form.paymentProvider = 'paypal';
        return paypalOnApprove(data);
      },
		},
	}
</script>
<style lang="scss">
@import "../../../sass/variables";

  .payment-form-vmc4 {
    .d-flex {
      display: flex;
    }

    .form-steps-title {
      text-align: center;
    }

    .el-input{
      &__inner{
        border-radius: 0;
      }
    }

    .input-container.variant-1 input,
    .phone-input-container.variant-1 input {
      background-color: #ffffff;
      font-size: 14px;
      border-radius: 0;
      border: 0;
      border-bottom: 1px solid #d2d2d2;

      &:focus {
        box-shadow: none;
      }
    }

    .step-1 {
      .radio-button-deal {
        font-size: 16px;
        padding: 20px 20px 20px 45px;
        border: none;
        cursor: pointer;
        margin: 5px 0;

        &:hover {
          background: #fef5eb;
        }
      }

      .radio-button-deal .checkmark {
        top: 20px;
      }

      .label-container-radio {
        border: none;
        margin: 5px 0;
      }
      .label-container-radio:hover {
        background-color: #fef5eb;
        border: none;
      }

      .label-container-radio input:checked ~ .checkmark:after {
        display: block;
      }

      .radio-button-group {
        margin: 24px 0;
      }

      .main-row {
        display: flex;
        justify-content: space-between;
        position: relative;
      }
      .discount {
        margin-left: 4px;
      }
      .prices {
        margin-right: 50px;
      }
      .red {
        color: $red;
      }
      .best-seller {
        position: absolute;
        top: -26px;
        right: 50px;

      }
    }

    .step-2 {
      .full-name {
        display: flex;
        margin-bottom: 15px;

        .first-name {
          width: 40%;
          margin-right: 10px;

          [dir="rtl"] & {
            margin-left: 10px;
            margin-right: 0;
          }
        }

        .last-name {
          width: calc(60% - 11px);
        }
      }
    }

    .step-3 {
      h3 {
        color: #0a0f0a;
        margin-bottom: 10px;
        padding: 0;
      }

      .pay-method-item img {
        max-height: 45px;
        margin-right: 15px;
      }

      .prefix > img {
        height: 22px;
        width: auto;
      }

      .card-info {
        display: flex;
        flex-direction: column;
        align-items: center;

        .d-flex {
          flex-direction: row;
        }

        .card-date {
          margin-top: 6px;

          .select.variant-1:nth-child(1) {
            margin-right: 5px;
            width: 50%;

            [dir="rtl"] & {
              margin-left: 5px;
              margin-right: 0;
            }
          }

          .select.variant-1:nth-child(2) {
            margin-right: 20px;
            width: 50%;

            [dir="rtl"] & {
              margin-left: 20px;
              margin-right: 0;
            }
          }

          .card-cvv {
            width: 30%;
            .input-container {
              .label {
                margin-bottom: 0;
              }
            }
          }
        }
      }

      .el-input {
        &__inner {
          background: #ffffff;
          border-radius: 0;
          border: 0;
          border-bottom: 1px solid #d2d2d2;
        }
      }

      #payment-error {
        margin: 16px 0 0px;
      }
    }

    .cursor-pointer:hover {
      cursor: pointer;
    }

    .buttons button:not(.back-btn),
    .submit-btn {
      width: 100%;
      border: none;
      border-radius: 3px;
      position: relative;
      padding: 18px 30px;
      margin: 10px 1px;
      font-size: 16px;
      font-weight: 400;
      text-transform: uppercase;
      letter-spacing: 0;
      will-change: box-shadow, transform;
      -webkit-transition: box-shadow .2s cubic-bezier(.4, 0, 1, 1), background-color .2s cubic-bezier(.4, 0, .2, 1);
      transition: box-shadow .2s cubic-bezier(.4, 0, 1, 1), background-color .2s cubic-bezier(.4, 0, .2, 1);
      box-shadow: 0 2px 2px 0 rgba(76, 175, 80, .14), 0 3px 1px -2px rgba(76, 175, 80, .2), 0 1px 5px 0 rgba(76, 175, 80, .12);
      background-color: #4caf50;
      color: #fff;

      &.btn-active {
        cursor: pointer;
      }

      &.btn-active:hover {
        opacity: 1;
        -webkit-transition: box-shadow .5s, background .5s ease;
        transition: box-shadow .5s, background .5s ease;
        box-shadow: -2px 2px 18px #4caf50;
        -moz-box-shadow: -2px 2px 18px #4caf50;
        -webkit-box-shadow: -2px 2px 18px #4caf50;
      }

      .spinner {
        left: 50%;
        margin-left: -32px;
        margin-top: -32px;
        position: absolute;
        top: 50%;
        transform: scale(.6);
        z-index: 0;
      }

      .btn-disabled {
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

    .buttons button.paypal-btn {
      display: flex;
      align-items: center;
      justify-content: center;
      background-color: #ffc438;
      border: 3px solid #feae01;
      color: #000000;
      font-weight: 700;

      & > img {
        margin-left: 5px;
      }

      &:hover {
        box-shadow: -2px 2px 18px #feae01;
      }
    }

    .form-navigation {
      display: flex;
      flex-direction: column;
      align-items: center;

      .back-btn {
        width: 50%;
        outline: none;
        border: none;
        position: relative;
        padding: 18px 30px;
        margin: 10px 1px;
        font-size: 16px;
        font-weight: 400;
        letter-spacing: 0;
        cursor: pointer;
        background-color: transparent;
        text-align: center;
        color: #337ABE;
      }
    }

    .deal-popup {
      .el-dialog {
        width: 600px;
        max-width: 100%;
      }
      .green-button-animated {
        display: table;
        width: auto;
        height: 67px;
        margin: auto;
        padding-left: 80px;
        padding-right: 80px;
      }
    }
  }
</style>
