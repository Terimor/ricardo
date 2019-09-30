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
          <slot name="installment" />
          <radio-button-group
            :withCustomLabels="false"
            v-model="form.deal"
            @input="setWarrantyPriceText"
            :list="list"
            />
          <h2 v-html="textSelectVariant"></h2>
          <select-field
              popperClass="smc7-popover-variant"
              v-model="form.variant"
              :rest="{
                placeholder: 'Variant'
              }"
              :list="variantList"/>
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
          <text-field
              :validation="$v.form.stepTwo.email"
              :validationMessage="textEmailRequired"
              theme="variant-1"
              :label="textEmail"
              v-model="form.stepTwo.email"/>
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
          <radio-button-group
            class="main__credit-card-switcher"
            v-model="form.paymentType"
            :list="mockData.creditCardRadioList"
          />
          <paypal-button
            :createOrder="paypalCreateOrder"
            :onApprove="paypalOnApprove"
            v-show="fullAmount"
            :$v="$v.form.deal"
            @click="paypalSubmit"
          >{{ paypalRiskFree }}</paypal-button>
          <slot name="warranty" />
          <form v-if="form.paymentType !== 'paypal' && form.paymentType">
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
                :prefix="`<img src='${cardUrl}' alt='Card Number' />`"
                :postfix="`<i class='fa fa-lock'></i>`"
            />
            <div class="card-info">
              <div class="d-flex">
                <div>
                  <div class="card-info__labels">
                    <span class="label" v-html="textCardValidUntil"></span>
                  </div>
                  <div class="card-date d-flex">
                    <select-field
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
                      :validation="$v.form.stepThree.year"
                      :validationMessage="textCardValidYearRequired"
                      :rest="{
                        placeholder: textCardValidYearPlaceholder
                      }"
                      theme="variant-1"
                      :list="Array.apply(null, Array(10)).map((_, ind) => ({ value: new Date().getFullYear() + ind }))"
                      v-model="form.stepThree.year"
                    />
                  </div>
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
                        maxlength: 4,
                        pattern: '\\d*',
                        type: 'tel',
                        autocomplete: 'cc-csc',
                        'data-bluesnap': 'encryptedCvv'
                      }"
                      v-model="form.stepThree.cvv"
                      postfix="<i class='fa fa-question-circle cursor-pointer'></i>"
                    />
                  </div>
                </div>
              </div>
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
              <text-field
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
                <div><img src="/images/cvv_popup.jpg" alt=""></div>
                <p v-html="textCVVPopupLine2"></p>
              </div>
            </el-dialog>
            <button
              v-if="form.paymentType !== 'paypal' && step === 3"
              @click="submit"
              :disabled="isSubmitted"
              :class="{ 'btn-active': !isSubmitted }"
              class="submit-btn"
              type="button"
            >
              <div v-if="isSubmitted" class="btn-disabled"></div>
              <span v-html="textSubmitButton"></span>
            </button>
          </form>
        </div>
        <div class="buttons">
          <!--
          <button
            v-if="form.paymentType === 'paypal' && step === 3"
            @click="submit"
            class="submit-btn paypal-btn"
            type="button"
          >
            <span class="purchase-button-text">Buy Now Risk-Free with</span>
            <img src="/images/cc-icons/paypal-highq.png" alt="Paypal">
          </button>
          -->
          <div class="form-navigation">
            <button @click="isAllowNext(step)" v-if="step !== 3" v-html="textNext" class="next-btn btn-active"></button>
            <button v-if="step > 1" class="back-btn" @click="step--">&lt;&lt; <span v-html="textBack"></span></button>
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
  import creditCardType from 'credit-card-type';
  import { check as ipqsCheck } from '../../services/ipqs';
	import RadioButtonItemDeal from "./RadioButtonItemDeal";
	import PayMethodItem from "./PayMethodItem";
  import queryToComponent from '../../mixins/queryToComponent';
	import { getCardUrl, preparePurchaseData, sendCheckoutRequest } from "../../utils/checkout";
  import { paypalCreateOrder, paypalOnApprove } from '../../utils/emc1';
	import vmc4validation from "../../validation/vmc4-validation";
  import setDataToLocalStorage from '../../mixins/purchas';
  import { goTo } from '../../utils/goTo';
	import {fade} from "../../utils/common";

	export default {
		name: "PaymentFormVMC4",
    mixins: [
      queryToComponent,
      setDataToLocalStorage,
    ],
		components: {PayMethodItem, RadioButtonItemDeal},
		validations: vmc4validation,
		props: [
			'countryList',
			'cardNames',
			'list',
			'variantList',
      'countryCode',
      'installments',
      'isWarrantyChecked',
			'checkoutData'
		],
		data() {
			return {
				step: 1,
				maxSteps: 3,
				isOpenCVVModal: false,
        isOpenPromotionModal: false,
        isSubmitted: false,
				form: {
					stepTwo: {
						fname: null,
						lname: null,
						email: null,
						phone: null,
					},
					stepThree: {
						cardNumber: '',
						month: null,
						year: null,
						cvv: null,
						country: checkoutData.countryCode.toUpperCase(),
						city: null,
						state: null,
						zipCode: null,
						cardType: null
					},
					countryCodePhoneField: checkoutData.countryCode,
					deal: null,
					variant: checkoutData.product.skus[0].code || "",
					//installments: 1,
					paymentType: null,
          cardType: null,
				},
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
        },
			}
    },
		computed: {
			cardUrl() {
				return getCardUrl(this.form.cardType)
			},
      fullAmount () {
        return this.installments == 1;
      },
      codeOrDefault () {
        return this.queryParams.product || this.checkoutData.product.skus[0].code;
      },
      dialCode() {
        const allCountries = window.intlTelInputGlobals.getCountryData();
        const phoneCountryCode = this.form.countryCodePhoneField.toLowerCase();
        const country = allCountries.filter(item => item.iso2 === phoneCountryCode).shift();

        return country ? country.dialCode : '1';
      },
      textChooseDeal: () => t('checkout.choose_deal'),
      textMainDealErrorPopupTitle: () => t('checkout.main_deal.error_popup.title'),
      textMainDealErrorPopupButton: () => t('checkout.main_deal.error_popup.button'),
      textSelectVariant: () => t('checkout.select_variant'),
      textContactInformation: () => t('checkout.contact_information'),
      textFirstName: () => t('checkout.payment_form.first_name'),
      textFirstNameRequired: () => t('checkout.payment_form.first_name.required'),
      textLastName: () => t('checkout.payment_form.last_name'),
      textLastNameRequired: () => t('checkout.payment_form.last_name.required'),
      textEmail: () => t('checkout.payment_form.email'),
      textEmailRequired: () => t('checkout.payment_form.email.required'),
      textPhone: () => t('checkout.payment_form.phone'),
      textPhoneRequired: () => t('checkout.payment_form.phone.required'),
      textPaymentMethod: () => t('checkout.payment_method'),
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
      textState: () => t('checkout.payment_form.state'),
      textStatePlaceholder: () => t('checkout.payment_form.state.placeholder'),
      textStateRequired: () => t('checkout.payment_form.state.required'),
      textZipcode: () => t('checkout.payment_form.zipcode'),
      textZipcodePlaceholder: () => t('checkout.payment_form.zipcode.placeholder'),
      textZipcodeRequired: () => t('checkout.payment_form.zipcode.required'),
      textCountry: () => t('checkout.payment_form.сountry'),
      textCountryPlaceholder: () => t('checkout.payment_form.сountry.placeholder'),
      textCountryRequired: () => t('checkout.payment_form.сountry.required'),
      textSubmitButton: () => t('checkout.payment_form.submit_button'),
      textCVVPopupTitle: () => t('checkout.payment_form.cvv_popup.title'),
      textCVVPopupLine1: () => t('checkout.payment_form.cvv_popup.line_1'),
      textCVVPopupLine2: () => t('checkout.payment_form.cvv_popup.line_2'),
      paypalRiskFree: () => t('checkout.paypal.risk_free'),
      textNext: () => t('checkout.next'),
      textBack: () => t('checkout.back'),
		},
		watch: {
      'form.stepThree.cardNumber' (cardNumber) {
        const creditCardTypeList = creditCardType(cardNumber)
        this.form.cardType = creditCardTypeList.length > 0 && cardNumber.length > 0
          ? creditCardTypeList[0].type
          : null
      },
			'form.variant'(val) {
				fade('out', 300, document.querySelector('#main-prod-image'), true)
					.then(() => {
						let productImageUrl = this.variantList.find(variant => variant.value === val).imageUrl;
						if(productImageUrl) {
							this.$emit('productImageChanged', productImageUrl)
            }
						fade('in', 300, document.querySelector('#main-prod-image'), true)
					});
			},
			'step'(val) {
				fade('out', 300, document.querySelector('.payment-form-vmc4'), true)
					.then(() => {
						fade('in', 300, document.querySelector('.payment-form-vmc4'), true)
					});
      },
      installments (val) {
        if (+val !== 1 && this.countryCode === 'MX') {
          this.form.stepThree.cardType = 'credit'
        }
      },
      list(value) {
        const qty = +this.queryParams.qty;
        const deal = value.find(({ quantity }) => qty === quantity);

        if (deal) {
          this.setWarrantyPriceText(qty);
          this.form.deal = qty;
        }
      },
		},
		methods: {
      setWarrantyPriceText(value) {
        this.$emit('setWarrantyPriceText', value)
      },
			submit() {
				this.$v.form.$touch();

        if (this.$v.form.$pending || this.$v.form.$error) {
          return;
        }

        if (this.isSubmitted) {
          return;
        }

        this.isSubmitted = true;

        let fields = {
          billing_first_name: this.form.stepTwo.fname,
          billing_last_name: this.form.stepTwo.lname,
          billing_country: this.form.stepThree.country,
          billing_city: this.form.stepThree.city,
          billing_region: this.form.stepThree.state,
          billing_postcode: this.form.stepThree.zipCode,
          billing_email: this.form.stepTwo.email,
          billing_phone: this.dialCode + this.form.stepTwo.phone,
          credit_card_bin: this.form.stepThree.cardNumber.substr(0, 6),
          credit_card_hash: sha256(this.form.stepThree.cardNumber),
          credit_card_expiration_month: ('0' + this.form.stepThree.month).slice(-2),
          credit_card_expiration_year: ('' + this.form.stepThree.year).substr(2, 2),
          cvv_code: this.form.stepThree.cvv,
        };

        this.setDataToLocalStorage(this.form.variant, this.form.deal, this.isWarrantyChecked);

        Promise.resolve()
          .then(() => ipqsCheck(fields))
          .then(ipqsResult => {
            if (this.form.paymentType === 'bank-payment') {
              this.isSubmitted = false;
              return;
            }

            if (this.form.paymentType === 'credit-card') {
              const data = {
                product: {
                  sku: this.form.variant,
                  qty: parseInt(this.form.deal, 10),
                },
                contact: {
                  phone: {
                    country_code: this.dialCode,
                    number: this.form.stepTwo.phone,
                  },
                  first_name: this.form.stepTwo.fname,
                  last_name: this.form.stepTwo.lname,
                  email: this.form.stepTwo.email,
                },
                address: {
                  city: this.form.stepThree.city,
                  country: this.form.stepThree.country.toLowerCase(),
                  zip: this.form.stepThree.zipCode,
                  state: this.form.stepThree.state,
                  street: 'none',
                },
                card: {
                  number: this.form.stepThree.cardNumber,
                  cvv: this.form.stepThree.cvv,
                  month: ('0' + this.form.stepThree.month).slice(-2),
                  year: '' + this.form.stepThree.year,
                  type: this.form.cardType,
                },
              };

              sendCheckoutRequest(data)
                .then(res => {
                  if (res.status === 'ok') {
                    localStorage.setItem('odin_order_id', res.order_id);
                    localStorage.setItem('order_currency', res.order_currency);

                    localStorage.setItem('order_id', res.order_id);
                    localStorage.setItem('odin_order_created_at', new Date());

                    goTo('/thankyou-promos/?order=' + res.order_id);
                  } else {
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
					this.form.countryCodePhoneField = val.iso2.toUpperCase()
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
				const isStepOneInvalid = this.$v.form.deal.$invalid;
				const isStepTwoInvalid = this.$v.form.stepTwo.$invalid;
				const isStepThreeInvalid =
					this.form.paymentType !== 'paypal' &&
          this.$v.form.stepThree.$invalid;

        if (currentStep === 1 && isStepOneInvalid) {
          this.$v.form.deal.$touch();
          this.isOpenPromotionModal = true;
        }
        else if (currentStep === 2 && isStepTwoInvalid) {
          this.$v.form.stepTwo.$touch();
        } else if (currentStep === 3 && isStepThreeInvalid) {
          this.$v.form.stepThree.$touch();
        } else {
          this.step++
        }
			},
      paypalCreateOrder() {
        const searchParams = new URL(document.location.href).searchParams;
        const currency = searchParams.get('cur') || checkoutData.product.prices.currency;

        this.setDataToLocalStorage(this.form.variant, this.form.deal, this.isWarrantyChecked);

        return paypalCreateOrder({
          xsrfToken: document.head.querySelector('meta[name="csrf-token"]').content,
          sku_code: this.codeOrDefault,
          sku_quantity: this.form.deal,
          is_warranty_checked: this.isWarrantyChecked,
          page_checkout: document.location.href,
          cur: currency,
          offer: searchParams.get('offer'),
          affiliate: searchParams.get('affiliate'),
        })
      },
      paypalOnApprove: paypalOnApprove,
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
        text-align: left;
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


        .card-date {
          margin-top: 6px;

          .select.variant-1:nth-child(1) {
            margin-right: 5px;
            width: 50%;
          }

          .select.variant-1:nth-child(2) {
            margin-right: 20px;
            width: 50%;
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
