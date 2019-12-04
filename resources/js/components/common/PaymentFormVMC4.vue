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
            <FirstName
              :$v="$v.form.stepTwo.fname"
              :form="form.stepTwo"
              name="fname" />
            <LastName
              :$v="$v.form.stepTwo.lname"
              :form="form.stepTwo"
              name="lname" />
          </div>
          <Email
            :$v="$v.form.stepTwo.email"
            :form="form.stepTwo"
            name="email" />
          <Phone
            :$v="$v.form.stepTwo.phone"
            :ccform="form"
            ccname="countryCodePhoneField"
            :form="form.stepTwo"
            name="phone" />
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
            <div class="card-info">
              <CardHolder
                v-if="$root.isAffIDEmpty"
                :$v="$v.form.stepThree.cardHolder"
                :form="form.stepThree"
                :placeholder="true"
                name="cardHolder" />
              <CardType
                class="input-container"
                :extraFields="extraFields"
                :form="vmc4Form"
                :$v="$v" />
              <CardNumber
                :$v="$v.form.stepThree.cardNumber"
                :placeholder="true"
                placeholderText="**** **** **** ****"
                :paymentMethodURL="paymentMethodURL"
                @setPaymentMethodByCardNumber="value => $emit('setPaymentMethodByCardNumber', value)"
                :form="form.stepThree"
                name="cardNumber" />
              <CardDate
                :$v="$v.form.stepThree.cardDate"
                :form="form.stepThree"
                name="cardDate" />
              <CVV
                :$v="$v.form.stepThree.cvv"
                :form="form.stepThree"
                name="cvv" />
              <DocumentType
                class="input-container"
                :extraFields="extraFields"
                :form="vmc4Form"
                :$v="$v" />
              <DocumentNumber
                :extraFields="extraFields"
                :form="vmc4Form"
                :$v="$v" />
            </div>
            <div class="d-flex flex-column">
              <ZipCode
                v-if="form.stepThree.country === 'br'"
                :$v="$v.form.stepThree.zipCode"
                :isLoading="isLoading"
                @setBrazilAddress="setBrazilAddress"
                :country="form.stepThree.country"
                :form="form.stepThree"
                :placeholder="true"
                name="zipCode" />
              <District
                :extraFields="extraFields"
                :withPlaceholder="true"
                :form="vmc4Form"
                :$v="$v" />
              <City
                :$v="$v.form.stepThree.city"
                :placeholder="true"
                :isLoading="isLoading"
                :form="form.stepThree"
                name="city" />
              <State
                v-if="!extraFields.state"
                :$v="$v.form.stepThree.state"
                :placeholder="true"
                :isLoading="isLoading"
                :country="form.stepThree.country"
                :form="form.stepThree"
                name="state" />
              <EState
                v-else
                class="input-container"
                :country="form.stepThree.country"
                :extraFields="extraFields"
                :isLoading="isLoading"
                :form="vmc4Form"
                :$v="$v" />
              <ZipCode
                v-if="form.stepThree.country !== 'br'"
                :$v="$v.form.stepThree.zipCode"
                :isLoading="isLoading"
                @setBrazilAddress="setBrazilAddress"
                :country="form.stepThree.country"
                :form="form.stepThree"
                :placeholder="true"
                name="zipCode" />
              <Country
                :$v="$v.form.stepThree.country"
                :form="form.stepThree"
                name="country" />
            </div>
            <Terms
              v-if="$root.isAffIDEmpty"
              :$v="$v.form.stepThree.terms"
              :form="form.stepThree"
              name="terms" />
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
  import { t } from '../../utils/i18n';
  import { ipqsCheck } from '../../services/ipqs';
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
  import FirstName from './common-fields/FirstName';
  import LastName from './common-fields/LastName';
  import Email from './common-fields/Email';
  import Phone from './common-fields/Phone';
  import City from './common-fields/City';
  import State from './common-fields/State';
  import ZipCode from './common-fields/ZipCode';
  import Country from './common-fields/Country';
  import CardHolder from './common-fields/CardHolder';
  import CardNumber from './common-fields/CardNumber';
  import CardDate from './common-fields/CardDate';
  import CVV from './common-fields/CVV';
  import Terms from './common-fields/Terms';
  import EState from './extra-fields/State';
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
      FirstName,
      LastName,
      Email,
      Phone,
      City,
      State,
      ZipCode,
      Country,
      CardHolder,
      CardNumber,
      CardDate,
      CVV,
      Terms,
      EState,
      District,
      CardType,
      DocumentType,
      DocumentNumber,
    },
		validations: vmc4validation,
		props: [
      'productImage',
			'dealList',
			'variantList',
      'extraFields',
      'paymentMethodURL',
      'vmc4Form',
		],
		data() {
			return {
				step: 1,
				maxSteps: 3,
        isFormShown: false,
        isOpenPromotionModal: false,
        paymentError: '',
        paypalPaymentError: '',
        isSubmitted: false,
        ipqsResult: null,
        isLoading: {
          address: false,
        },
				form: {
					stepTwo: {
						fname: null,
						lname: null,
						email: null,
						phone: null,
					},
					stepThree: {
            cardHolder: null,
						cardNumber: null,
						cardDate: null,
						cvv: null,
						country: checkoutData.countryCode,
						city: null,
						state: null,
						zipCode: null,
            terms: null,
					},
					countryCodePhoneField: checkoutData.countryCode,
          country: checkoutData.countryCode,
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
      dialCode() {
        const allCountries = window.intlTelInputGlobals.getCountryData();
        const phoneCountryCode = this.form.countryCodePhoneField;
        const country = allCountries.filter(item => item.iso2 === phoneCountryCode).shift();

        return country ? country.dialCode : '1';
      },
      textChooseDeal: () => t('checkout.choose_deal'),
      textMainDealErrorPopupTitle: () => t('checkout.main_deal.error_popup.title'),
      textMainDealErrorPopupButton: () => t('checkout.main_deal.error_popup.button'),
      textSelectVariant: () => t('checkout.select_variant'),
      textContactInformation: () => t('checkout.contact_information'),
      textPaymentMethod: () => t('checkout.payment_method'),
      textPaySecurely: () => t('checkout.pay_securely'),
      textSubmitButton: () => t('checkout.payment_form.submit_button'),
      paypalRiskFree: () => t('checkout.paypal.risk_free'),
      textPaymentError: () => t('checkout.payment_error'),
      textNext: () => t('checkout.next'),
      textBack: () => t('checkout.back'),
		},
    watch: {
      'form.stepThree.country'(country) {
        this.form.country = this.form.stepThree.country;
        this.$parent.reloadPaymentMethods(country);
      },
    },
		methods: {
      activateForm() {
        this.isFormShown = true;
      },
      setBrazilAddress(res) {
        this.form.stepThree.city = res.city;
        this.form.stepThree.state = res.state;
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
        const cardNumber = this.form.stepThree.cardNumber.replace(/[^0-9]/g, '');

        if (this.form.stepTwo.emailForceInvalid) {
          return setTimeout(() => {
            this.paymentError = t('checkout.abuse_error');
            this.isSubmitted = false;
          }, 1000);
        }

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
              order_amount: this.getOrderAmount(this.vmc4Form.deal, this.vmc4Form.isWarrantyChecked),
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
              credit_card_expiration_month: this.form.stepThree.cardDate.split('/')[0],
              credit_card_expiration_year: this.form.stepThree.cardDate.split('/')[1],
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
                  month: this.form.stepThree.cardDate.split('/')[0],
                  year: '20' + this.form.stepThree.cardDate.split('/')[1],
                },
                ipqs: this.ipqsResult,
              };

              if (this.$root.isAffIDEmpty) {
                data.card.holder = this.form.stepThree.cardHolder;
              }

              this.$parent.setExtraFieldsForCardPayment(data);

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

            const data = {
              order_amount: this.getOrderAmount(this.vmc4Form.deal, this.vmc4Form.isWarrantyChecked),
            };

            return ipqsCheck(data);
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
              sku_code: this.vmc4Form.variant,
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
        flex-wrap: wrap;
      }

      #card-date-field {
        width: 130px;
      }

      #cvv-field {
        width: 120px;
        margin-left: 10px;

        [dir="rtl"] & {
          margin-left: 0;
          margin-right: 10px;
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

      .terms-checkbox {
        margin-top: 16px;
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
