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
          <Variant
            :$v="$v.vmc4Form.variant"
            :form="vmc4Form"
            name="variant" />
        </div>
        
        <form @submit.stop.prevent="isAllowNext(step)">
          <div class="step step-2" v-if="step === 2">
            <div class="full-name">
              <FirstName
                @check_for_leads_request="check_for_leads_request"
                :$v="$v.form.stepTwo.fname"
                :form="form.stepTwo"
                name="fname" />
              <LastName
                @check_for_leads_request="check_for_leads_request"
                :$v="$v.form.stepTwo.lname"
                :form="form.stepTwo"
                name="lname" />
            </div>
            <Email
              @check_for_leads_request="check_for_leads_request"
              :$v="$v.form.stepTwo.email"
              :form="form.stepTwo"
              name="email" />
            <Phone
              @check_for_leads_request="check_for_leads_request"
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
              v-if="$root.paypalEnabled"
              v-show="vmc4Form.installments === 1"
              :createOrder="paypalCreateOrder"
              :onApprove="paypalOnApprove"
              :$vvariant="$v.vmc4Form.variant"
              :$vdeal="$v.vmc4Form.deal"
              @click="paypalSubmit"
            >{{ paypalRiskFree }}</paypal-button>
            <p v-if="paypalPaymentError" id="paypal-payment-error" class="error-container" v-html="paypalPaymentError"></p>
            <template v-if="$root.hasAPM">
              <h3 v-html="textPaySecurelyAPM"></h3>
              <payment-providers-apm
                v-model="form.paymentProvider"
                @input="activateForm" />
            </template>
            <slot name="warranty" />
            <div v-if="form.paymentProvider && isFormShown" v-show="form.paymentProvider !== 'paypal'">
              <div class="card-info" v-if="form.paymentProvider === 'credit-card'">
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
                  :$v="$v.vmc4Form" />
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
                  :$v="$v.vmc4Form" />
                <DocumentNumber
                  :extraFields="extraFields"
                  :form="vmc4Form"
                  :$v="$v.vmc4Form" />
              </div>
              <div class="d-flex flex-wrap">
                <ZipCode
                  v-if="form.stepThree.country === 'br'"
                  :$v="$v.form.stepThree.zipCode"
                  :isLoading="isLoading"
                  @setBrazilAddress="setBrazilAddress"
                  :country="form.stepThree.country"
                  :form="form.stepThree"
                  :placeholder="true"
                  name="zipCode" />
                <Street
                  :$v="$v.form.stepThree.street"
                  :isLoading="isLoading"
                  :form="form.stepThree"
                  :placeholder="true"
                  name="street" />
                <Building
                  :extraFields="extraFields"
                  :placeholder="true"
                  :form="vmc4Form"
                  :$v="$v.vmc4Form" />
                <Complement
                  :isLoading="isLoading"
                  :extraFields="extraFields"
                  :placeholder="true"
                  :form="vmc4Form"
                  :$v="$v.vmc4Form" />
                <District
                  :isLoading="isLoading"
                  :extraFields="extraFields"
                  :placeholder="true"
                  :form="vmc4Form"
                  :$v="$v.vmc4Form" />
                <City
                  :$v="$v.form.stepThree.city"
                  :placeholder="true"
                  :isLoading="isLoading"
                  :form="form.stepThree"
                  name="city" />
                <State
                  class="input-container"
                  :country="form.stepThree.country"
                  :stateExtraField="stateExtraField"
                  :isLoading="isLoading"
                  :placeholder="true"
                  :form="vmc4Form"
                  :$v="$v.vmc4Form" />
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
                  :placeholder="true"
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
            </div>
          </div>

          <div class="buttons">
            <div class="form-navigation">
              <button type="submit" v-if="step !== 3" v-html="textNext" class="next-btn btn-active"></button>
              <button type="button" v-if="step > 1" class="back-btn" @click="animatePrevStep">&lt;&lt; <span v-html="textBack"></span></button>
            </div>
          </div>
        </form>

      </div>
    </div>
    <el-dialog
      @click="isOpenPromotionModal = false"
      class="deal-popup"
      :title="promo_modal_title"
      :lock-scroll="false"
      :visible.sync="isOpenPromotionModal"
    >
      <div class="deal-popup__content">
        <p class="error-container" v-html="promo_modal_text"></p>

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
	import { checkForLeadsRequest, sendCheckoutRequest, get3dsErrors } from "../../utils/checkout";
  import { paypalCreateOrder, paypalOnApprove } from '../../utils/emc1';
	import vmc4validation from "../../validation/vmc4-validation";
  import purchasMixin from '../../mixins/purchas';
  import Spinner from './preloaders/Spinner';
	import {fade} from "../../utils/common";
  import { queryParams } from  '../../utils/queryParams';
  import Installments from './extra-fields/Installments';
  import Variant from './common-fields/Variant';
  import FirstName from './common-fields/FirstName';
  import LastName from './common-fields/LastName';
  import Email from './common-fields/Email';
  import Phone from './common-fields/Phone';
  import Street from './common-fields/Street';
  import City from './common-fields/City';
  import ZipCode from './common-fields/ZipCode';
  import Country from './common-fields/Country';
  import CardHolder from './common-fields/CardHolder';
  import CardNumber from './common-fields/CardNumber';
  import CardDate from './common-fields/CardDate';
  import CVV from './common-fields/CVV';
  import Terms from './common-fields/Terms';
  import State from './extra-fields/State';
  import Building from './extra-fields/Building';
  import Complement from './extra-fields/Complement';
  import District from './extra-fields/District';
  import CardType from './extra-fields/CardType';
  import DocumentType from './extra-fields/DocumentType';
  import DocumentNumber from './extra-fields/DocumentNumber';
  import globals from '../../mixins/globals';
  import logger from '../../mixins/logger';


	export default {
		name: "PaymentFormVMC4",
    mixins: [
      queryToComponent,
      purchasMixin,
      globals,
      logger,
    ],
		components: {
      PayMethodItem,
      RadioButtonItemDeal,
      Spinner,
      Installments,
      Variant,
      FirstName,
      LastName,
      Email,
      Phone,
      Street,
      City,
      ZipCode,
      Country,
      CardHolder,
      CardNumber,
      CardDate,
      CVV,
      Terms,
      State,
      Building,
      Complement,
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
      'stateExtraField',
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
						country: js_data.countries.indexOf(js_data.country_code) !== -1
              ? js_data.country_code
              : null,
            street: null,
						city: null,
						zipCode: null,
            terms: null,
					},
					countryCodePhoneField: js_data.country_code,
          country: js_data.countries.indexOf(js_data.country_code) !== -1
            ? js_data.country_code
            : null,
					paymentProvider: null,
				},
			}
    },
    created() {
      if (!this.$root.paypalEnabled) {
        this.form.paymentProvider = 'credit-card';
        this.isFormShown = true;
      }

      if (this.queryParams['3ds'] && this.queryParams['3ds'] !== 'success') {
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
          this.form.stepThree.street = selectedProductData.street || this.form.stepThree.street;
          this.form.stepThree.city = selectedProductData.city || this.form.stepThree.city;
          this.form.stepThree.zipCode = selectedProductData.zipcode || this.form.stepThree.zipCode;
          this.form.stepThree.country = selectedProductData.country || this.form.stepThree.country;
        }
        catch (err) {
          
        }
      }

      if (this.queryParams['3ds'] === 'failure') {
        get3dsErrors().then(paymentError => {
          this.paymentError = paymentError;

          if (this.form.stepThree.country === 'br') {
            let ipqs = null; try { ipqs = JSON.parse(localStorage.getItem('3ds_ipqs')); } catch (err) {};

            this.log_data('BRAZIL: VMC4 - Credit Card - 3ds failure', {
              error: paymentError,
              form: { ...this.$v.form.$model, ...this.$v.vmc4Form.$model, stepThree: { ...this.$v.form.stepThree.$model, cardNumber: undefined } },
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

          const element = document.querySelector('.submit-btn');

          if (element && element.scrollIntoView) {
            element.scrollIntoView();
          }
        }, 1000);
      }

      this.restore_customer(
        (fname, lname, email, street, city, zipcode, country) => {
          this.form.stepTwo.fname = fname;
          this.form.stepTwo.lname = lname;
          this.form.stepTwo.email = email;
          this.form.stepThree.street = street;
          this.form.stepThree.city = city;
          this.form.stepThree.zipCode = zipcode;
          this.form.stepThree.country = country;
        },
        (countryCodePhoneField, phone) => {
          this.form.countryCodePhoneField = countryCodePhoneField;
          this.form.stepTwo.phone = phone;
        },
      );
    },
		computed: {
      isShowVariant() {
        return this.variantList.length > 1 && (!js_query_params.variant || js_query_params.variant === '0');
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
      textPaySecurelyAPM: () => t('checkout.pay_securely_apm'),
      textSubmitButton: () => t('checkout.payment_form.submit_button'),
      paypalRiskFree: () => t('checkout.paypal.risk_free'),
      textPaymentError: () => t('checkout.payment_error'),
      textNext: () => t('checkout.next'),
      textBack: () => t('checkout.back'),

      promo_modal_title() {
        if (this.$v.vmc4Form.deal.$invalid) {
          return t('checkout.main_deal.error_popup.title');
        } else if (this.$v.vmc4Form.variant.$invalid) {
          return t('checkout.select_variant');
        }
      },
      promo_modal_text() {
        if (this.$v.vmc4Form.deal.$invalid) {
          return t('checkout.main_deal.error_popup.message');
        } else if (this.$v.vmc4Form.variant.$invalid) {
          return t('checkout.select_variant');
        }
      },
		},
    watch: {
      'form.paymentProvider'(value) {
        window.selectedPayment = value;
        history.pushState({}, '', location.href);
        this.print_pixels('payment');
      },
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
        this.form.stepThree.street = res.address || this.form.stepThree.street;
        this.form.stepThree.city = res.city || this.form.stepThree.city;
        this.vmc4Form.state = res.state || this.vmc4Form.state;
        this.vmc4Form.district = res.district || this.vmc4Form.district;
        this.vmc4Form.complement = res.complement || this.vmc4Form.complement;
      },
      check_for_leads_request() {
        const form = this.form.stepTwo;

        const phone = form.phone
          ? this.dialCode + form.phone.replace(/[^0-9]/g, '')
          : '';

        checkForLeadsRequest(this.vmc4Form.variant, form.fname, form.lname, form.email, phone, !this.$v.form.stepTwo.phone.$invalid);
      },
			submit() {
        let ipqsResult = null;

				this.$v.form.$touch();
        this.$v.vmc4Form.$touch();

        if ((this.$v.form.$invalid || this.$v.vmc4Form.$invalid) && this.form.stepThree.country === 'br') {
          this.log_data('BRAZIL: VMC4 - Credit Card - form validation', {
            invalid: {
              ...Object.keys(this.$v.form.stepThree)
                .filter(name => name !== 'cardNumber' && !!this.$v.form.stepThree[name].$invalid)
                .reduce((acc, name) => { acc[name] = this.$v.form.stepThree.$model[name]; return acc; }, {}),
              ...Object.keys(this.$v.vmc4Form)
                .filter(name => !!this.$v.vmc4Form[name].$invalid)
                .reduce((acc, name) => { acc[name] = this.$v.vmc4Form.$model[name]; return acc; }, {}),
            },
            form: { ...this.$v.form.$model, ...this.$v.vmc4Form.$model, stepThree: { ...this.$v.form.stepThree.$model, cardNumber: undefined } },
          });
        }

        if (this.$v.form.$vmc4Form || this.$v.form.$invalid) {
          return;
        }

        if (this.isSubmitted) {
          return;
        }

        this.paymentError = '';
        this.isSubmitted = true;

        const phoneNumber = (this.form.stepTwo.phone || '').replace(/[^0-9]/g, '');
        const cardNumber = (this.form.stepThree.cardNumber || '').replace(/[^0-9]/g, '');

        if (this.form.stepTwo.emailForceInvalid) {
          if (this.form.stepThree.country === 'br') {
            this.log_data('BRAZIL: VMC4 - Credit Card - email blocked', {
              email: this.form.stepTwo.email,
              form: { ...this.$v.form.$model, ...this.$v.vmc4Form.$model, stepThree: { ...this.$v.form.stepThree.$model, cardNumber: undefined } },
            });
          }

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
          street: this.form.stepThree.street,
          city: this.form.stepThree.city,
          zipcode: this.form.stepThree.zipCode,
          country: this.form.stepThree.country,
        };

        this.$parent.setExtraFieldsForLocalStorage(data);
        this.setDataToLocalStorage(data);

        Promise.resolve()
          .then(() => {
            let data = {
              order_amount: this.getOrderAmount(this.vmc4Form.deal, this.vmc4Form.isWarrantyChecked),
              billing_first_name: this.form.stepTwo.fname,
              billing_last_name: this.form.stepTwo.lname,
              billing_country: this.form.stepThree.country,
              billing_address_1: this.form.stepThree.street,
              billing_city: this.form.stepThree.city,
              billing_region: this.extraFields.state
                ? this.vmc4Form.state
                : '',
              billing_postcode: this.form.stepThree.zipCode,
              billing_email: this.form.stepTwo.email,
              billing_phone: this.dialCode + phoneNumber,
            };

            if (this.form.paymentProvider === 'credit-card') {
              data.credit_card_bin = cardNumber.substr(0, 6);
              data.credit_card_expiration_month = this.form.stepThree.cardDate.split('/')[0];
              data.credit_card_expiration_year = this.form.stepThree.cardDate.split('/')[1];
              data.cvv_code = this.form.stepThree.cvv;

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
                street: this.form.stepThree.street,
              },
              ipqs: ipqsResult,
            };

            if (this.form.paymentProvider === 'credit-card') {
              data.card = {
                number: cardNumber,
                cvv: this.form.stepThree.cvv,
                month: this.form.stepThree.cardDate.split('/')[0],
                year: '20' + this.form.stepThree.cardDate.split('/')[1],
              };

              if (this.$root.isAffIDEmpty) {
                data.card.holder = this.form.stepThree.cardHolder;
              }
            }

            this.$parent.setExtraFieldsForCardPayment(data, this.form.paymentProvider);

            return sendCheckoutRequest(data, this.form.paymentProvider);
          })
          .then(res => {
            if (res.status !== 'ok' && this.form.stepThree.country === 'br') {
              this.log_data('BRAZIL: VMC4 - Credit Card - response', {
                error: res.paymentError,
                res: { ...res, paymentError: undefined },
                form: { ...this.$v.form.$model, ...this.$v.vmc4Form.$model, stepThree: { ...this.$v.form.stepThree.$model, cardNumber: undefined } },
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
      paypalSubmit() {
        
      },
			isAllowNext(currentStep) {
				const isStepOneInvalid = this.$v.vmc4Form.deal.$invalid || this.$v.vmc4Form.variant.$invalid;
				const isStepTwoInvalid = this.$v.form.stepTwo.$invalid;
				const isStepThreeInvalid =
					this.form.paymentProvider !== 'paypal' &&
          this.$v.form.stepThree.$invalid;

        if (currentStep === 1 && isStepOneInvalid) {
          this.$v.vmc4Form.deal.$touch();
          this.$v.vmc4Form.variant.$touch();
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
        let ipqsResult = null;

        this.form.paymentProvider = 'paypal';

        const currency = !js_query_params.cur || js_query_params.cur === '{aff_currency}'
          ? js_data.product.prices.currency
          : js_query_params.cur;

        this.setDataToLocalStorage({
          deal: this.vmc4Form.deal,
          variant: this.vmc4Form.variant,
          isWarrantyChecked: this.vmc4Form.isWarrantyChecked,
          paymentProvider: 'paypal',
        });

        this.paypalPaymentError = '';

        return Promise.resolve()
          .then(() => {
            const data = {
              order_amount: this.getOrderAmount(this.vmc4Form.deal, this.vmc4Form.isWarrantyChecked),
            };

            return ipqsCheck(data);
          })
          .then(result => {
            ipqsResult = result;
          })
          .then(() => {
            if (ipqsResult && ipqsResult.recent_abuse) {
              if (this.form.stepThree.country === 'br') {
                this.log_data('BRAZIL: VMC4 - PayPal - IPQS - recent_abuse', {
                  fraud_chance: ipqsResult.fraud_chance,
                  ipqs: ipqsResult,
                });
              }

              return Promise.reject({
                custom_error: t('checkout.abuse_error'),
              });
            }

            if (this.ipqs_paypal_restricted(ipqsResult)) {
              return Promise.reject({
                custom_error: t('checkout.payment_error.area_restriction'),
              });
            }

            return paypalCreateOrder({
              xsrfToken: document.head.querySelector('meta[name="csrf-token"]').content,
              sku_code: this.vmc4Form.variant,
              sku_quantity: this.vmc4Form.deal,
              is_warranty_checked: this.vmc4Form.isWarrantyChecked,
              page_checkout: document.location.href,
              cur: currency,
              offer: js_query_params.offer || null,
              affiliate: js_query_params.affiliate || null,
              ipqsResult,
            });
          })
          .then(res => {
            if (res.error && this.form.stepThree.country === 'br') {
              this.log_data('BRAZIL: VMC4 - PayPal - response', {
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
            this.paypalPaymentError = !err || !err.custom_error
              ? t('checkout.payment_error')
              : err.custom_error;
          });
      },
      paypalOnApprove(data) {
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
        position: relative;
      }
      .prices {
        margin-left: auto;
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
      .variant-field {
        margin-bottom: 20px;
      }
      .variant-field-label {
        font-size: 20px;
        font-weight: 700;
        margin-bottom: 8px;
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

      #building-field {
        padding-right: 10px;
        width: 50%;

        [dir="rtl"] & {
          padding-left: 10px;
          padding-right: 0;
        }
      }

      #complement-field {
        width: 50%;
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
      text-align: center;
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
