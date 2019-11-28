<template>
    <form v-if="$v" class="flex-wrap payment-form" :class="{ 'is-brazil': countryCode === 'br' }">
        <h2>
          {{ firstTitle }}
        </h2>
        <text-field
            :validation="$v.form.fname"
            :validationMessage="textFirstNameRequired"
            theme="variant-1"
            :label="textFirstName"
            class="first-name"
            :rest="{
              autocomplete: 'name',
              name: 'name'
            }"
            v-model="paymentForm.fname"/>
        <text-field
            :validation="$v.form.lname"
            :validationMessage="textLastNameRequired"
            theme="variant-1"
            :label="textLastName"
            class="last-name"
            :rest="{
              autocomplete: 'family-name',
              name: 'family-name'
            }"
            v-model="paymentForm.lname"/>
<!--
        <text-field-with-placeholder
            :validation="$v.form.dateOfBirth"
            :validationMessage="textBirthdayRequired"
            v-if="countryCode === 'de'"
            :rest="{
              'format': 'dd/mm/yyyy',
            }"
            :placeholder="textBirthdayPlaceHolder"
            v-model="paymentForm.dateOfBirth"
            theme="variant-1"
            :label="textBirthday"
        />
-->
        <Email
          name="email"
          :form="paymentForm"
          :$v="$v.form.email" />
        <phone-field
            @onCountryChange="setCountryCodeByPhoneField"
            :validation="$v.form.phone"
            :validationMessage="textPhoneRequired"
            :countryCode="countryCode"
            theme="variant-1"
            :label="textPhone"
            :rest="{
              autocomplete: 'off',
              name: 'phone'
            }"
            v-model="paymentForm.phone"/>
        <h2>
          {{ secondTitle }}
        </h2>
        <div class="payment-form__delivery-address">
            <text-field
                :validation="$v.form.street"
                :validationMessage="textStreetRequired"
                v-loading="isLoading.address"
                element-loading-spinner="el-icon-loading"
                theme="variant-1 street"
                :label="textStreet"
                :rest="{
                  autocomplete: 'shipping street-address',
                  name: 'ship-address'
                }"
                v-model="paymentForm.street"/>
            <District
              :extraFields="extraFields"
              :form="paymentForm"
              :$v="$v" />
            <text-field
                :validation="$v.form.city"
                :validationMessage="textCityRequired"
                v-loading="isLoading.address"
                element-loading-spinner="el-icon-loading"
                theme="variant-1"
                :label="textCity"
                :rest="{
                    autocomplete: 'shipping locality',
                    name: 'city'
                }"
                v-model="paymentForm.city"/>
            <State
              v-if="extraFields.state"
              :country="paymentForm.country"
              :extraFields="extraFields"
              :isLoading="isLoading"
              :form="paymentForm"
              :$v="$v" />
            <text-field
                v-else
                v-loading="isLoading.address"
                element-loading-spinner="el-icon-loading"
                :validation="$v.form.state"
                :validationMessage="textStateRequired"
                theme="variant-1"
                :rest="{
                  autocomplete: 'shipping region',
                  name: 'ship-state'
                }"
                :label="textStateTitle"
                v-model="paymentForm.state" />
            <text-field
                :validation="$v.form.zipcode"
                :validationMessage="textZipcodeRequired"
                theme="variant-1"
                :rest="{
                  autocomplete: 'postal-code',
                  name: 'postal-code'
                }"
                :label="textZipcode"
                id="zip-code-field"
                v-model="paymentForm.zipcode"/>
            <Country
              :$v="$v.form.country"
              :form="paymentForm"
              name="country" />
        </div>
        <template v-if="paymentForm.paymentProvider !== 'bank-payment'">
            <h2>
              {{ thirdTitle }}
            </h2>
            <CardHolder
              v-if="$root.isAffIDEmpty"
              :$v="$v.form.cardHolder"
              :form="paymentForm"
              name="cardHolder" />
            <CardType
              :extraFields="extraFields"
              :form="paymentForm"
              :$v="$v" />
            <div id="payment-data-form">
                <text-field
                    :validation="$v.form.cardNumber"
                    :rest="{
                      pattern: '\\d*',
                      type: 'tel',
                      autocomplete: 'cc-number',
                        'data-bluesnap': 'encryptedCreditCard'
                      }"
                    :validationMessage="textCardNumberRequired"
                    class="card-number"
                    theme="variant-1"
                    :label="textCardNumber"
                    v-model="paymentForm.cardNumber"
                    :prefix="`<img src='${$parent.paymentMethodURL}' />`"
                    :postfix="`<i class='fa fa-lock'></i>`"
                />
                <label
                  class="card-date"
                  :class="{ 'with-error': $v.form && $v.form.month && $v.form.month.$dirty && $v.form.year && $v.form.year.$dirty && ($v.form.month.$invalid || $v.form.year.$invalid || isCardExpired) }">
                    <span class="label" v-html="textCardValidUntil"></span>
                    <Month
                      :$v="$v.form.month"
                      :form="paymentForm"
                      name="month" />
                    <Year
                      :$v="$v.form.year"
                      :form="paymentForm"
                      name="year" />
                    <span
                      class="error"
                      v-show="paymentForm.month && paymentForm.year && isCardExpired"
                      v-html="textCardExpired"></span>
                </label>
                <text-field
                    @click-postfix="openCVVModal"
                    :validation="$v.form.cvv"
                    :validationMessage="textCardCVVRequired"
                    class="cvv-field"
                    theme="variant-1"
                    :label="textCardCVV"
                    :rest="{
                      autocomplete: 'cc-csc',
                      'data-bluesnap': 'encryptedCvv'
                    }"
                    v-model="paymentForm.cvv"
                    postfix="<i class='fa fa-question-circle'></i>"
                />
            </div>
            <DocumentType
              :extraFields="extraFields"
              :form="paymentForm"
              :$v="$v" />
            <DocumentNumber
              :extraFields="extraFields"
              :form="paymentForm"
              :$v="$v" />
        </template>
        <!-- vmp41/42 block -->
        <span v-show="hasWarranty" class="warranty-field-button">
          <label for="warranty-field" class="label-container-checkbox">
            <i class="fa fa-arrow-left slide-left warranty-field-arrow"></i>
            <i class="fa fa-arrow-right slide-right warranty-field-arrow"></i>
            <span v-html="textWarranty"></span>: {{quantityOfInstallments}} {{warrantyPriceText}}
            <input id="warranty-field" type="checkbox" v-model="paymentForm.isWarrantyChecked">
            <span class="checkmark"></span>
          </label>
          <span class="warranty-field-icon">
            <img :src="$root.cdnUrl + '/assets/images/best-saller.png'" alt="">
          </span>
        </span>
        <Terms
          v-if="$root.isAffIDEmpty"
          :$v="$v.form.terms"
          :form="paymentForm"
          name="terms" />
        <p v-if="paymentError" id="payment-error" class="error-container" v-html="paymentError"></p>
        <button
            @click="submit"
            :disabled="isSubmitted"
            id="purchase-button"
            type="button"
            class="green-button-animated"
            :class="{ 'green-button-active': !isSubmitted }">
            <Spinner v-if="isSubmitted" />
            <div v-if="isSubmitted" class="purchase-button-disabled"></div>
            <span class="purchase-button-text" :style="{ visibility: isSubmitted ? 'hidden' : 'visible' }" v-html="textSubmitButton"></span>
        </button>
        <el-dialog
          @click="isOpenCVVModal = false"
          class="cvv-popup"
          :title="textCVVPopupTitle"
          :visible.sync="isOpenCVVModal">
            <div class="cvv-popup__content">
                <p v-html="textCVVPopupLine1"></p>
                <div><img :src="$root.cdnUrl + '/assets/images/cvv_popup.jpg'" alt=""></div>
                <p v-html="textCVVPopupLine2"></p>
            </div>
        </el-dialog>
    </form>
</template>

<script>
  import * as dateFns from 'date-fns';
  import apiUrlList from '../../constants/api-url'
  import { ipqsCheck } from '../../services/ipqs';
  import { t } from '../../utils/i18n';
  import { debounce } from '../../utils/common'
  import queryToComponent from '../../mixins/queryToComponent';
  import scrollToError from '../../mixins/formScrollToError';
  import { sendCheckoutRequest, get3dsErrors } from '../../utils/checkout';
  import purchasMixin from '../../mixins/purchas';
  import Spinner from './preloaders/Spinner';
  import Email from './common-fields/Email';
  import Country from './common-fields/Country';
  import CardHolder from './common-fields/CardHolder';
  import Month from './common-fields/Month';
  import Year from './common-fields/Year';
  import Terms from './common-fields/Terms';
  import State from './extra-fields/State';
  import District from './extra-fields/District';
  import CardType from './extra-fields/CardType';
  import DocumentType from './extra-fields/DocumentType';
  import DocumentNumber from './extra-fields/DocumentNumber';

  export default {
    name: 'PaymentForm',
    props: [
      'input',
      'countryCode',
      'paymentForm',
      '$v',
      'firstTitle',
      'secondTitle',
      'thirdTitle',
      'hasWarranty',
      'quantityOfInstallments',
      'warrantyPriceText',
      'extraFields',
    ],
    mixins: [
      queryToComponent,
      purchasMixin,
      scrollToError,
    ],
    components: {
      Spinner,
      Email,
      Country,
      CardHolder,
      Month,
      Year,
      Terms,
      State,
      District,
      CardType,
      DocumentType,
      DocumentNumber,
    },
    data () {
      return {
        isLoading: {
          address: false
        },
        ipqsResult: null,
        isOpenCVVModal: false,
        isSubmitted: false,
        paymentError: '',
      }
    },

    created() {
      if (this.queryParams['3ds'] === 'failure') {
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
      dialCode() {
        const allCountries = window.intlTelInputGlobals.getCountryData();
        const phoneCountryCode = this.paymentForm.countryCodePhoneField;
        const country = allCountries.filter(item => item.iso2 === phoneCountryCode).shift();

        return country ? country.dialCode : '1';
      },

      exp () {
        const { month, year } = this.paymentForm

        if (month && year) {
          return month.length === 2 ? month : '0'.concat(month) + '/' + String(year)
        } else {
          return null
        }
      },

      isCardExpired() {
        return !dateFns.isFuture(new Date(this.paymentForm.year, this.paymentForm.month));
      },

      textStateTitle() {
        return t('checkout.payment_form.state', {}, { country: this.paymentForm.country });
      },

      textStatePlaceholder() {
        return t('checkout.payment_form.state.placeholder', {}, { country: this.paymentForm.country });
      },

      textZipcode() {
        return t('checkout.payment_form.zipcode', {}, { country: this.paymentForm.country });
      },

      textFirstName: () => t('checkout.payment_form.first_name'),
      textFirstNameRequired: () => t('checkout.payment_form.first_name.required'),
      textLastName: () => t('checkout.payment_form.last_name'),
      textLastNameRequired: () => t('checkout.payment_form.last_name.required'),
      textBirthday: () => t('checkout.payment_form.birthday'),
      textBirthdayPlaceHolder: () => t('checkout.payment_form.birthday.placeholder'),
      textBirthdayRequired: () => t('checkout.payment_form.birthday.required'),
      textPhone: () => t('checkout.payment_form.phone'),
      textPhoneRequired: () => t('checkout.payment_form.phone.required'),
      textStreet: () => t('checkout.payment_form.street'),
      textStreetRequired: () => t('checkout.payment_form.street.required'),
      textStreetNumber: () => t('checkout.payment_form.street_number'),
      textStreetNumberRequired: () => t('checkout.payment_form.street_number.required'),
      textCity: () => t('checkout.payment_form.city'),
      textCityRequired: () => t('checkout.payment_form.city.required'),
      textStateRequired: () => t('checkout.payment_form.state.required'),
      textZipcodeRequired: () => t('checkout.payment_form.zipcode.required'),
      textCardNumber: () => t('checkout.payment_form.card_number'),
      textCardNumberRequired: () => t('checkout.payment_form.card_number.required'),
      textCardValidUntil: () => t('checkout.payment_form.card_valid_until'),
      textCardExpired: () => t('checkout.payment_form.card_expired'),
      textCardCVV: () => t('checkout.payment_form.card_cvv'),
      textCardCVVRequired: () => t('checkout.payment_form.card_cvv.required'),
      textWarranty: () => t('checkout.warranty'),
      textSubmitButton: () => t('checkout.payment_form.submit_button'),
      textCVVPopupTitle: () => t('checkout.payment_form.cvv_popup.title'),
      textCVVPopupLine1: () => t('checkout.payment_form.cvv_popup.line_1'),
      textCVVPopupLine2: () => t('checkout.payment_form.cvv_popup.line_2'),
      textPaymentError: () => t('checkout.payment_error'),
    },
    watch: {
      'paymentForm.cardNumber' (newVal, oldValue) {
        newVal = newVal || '';
        
        if (!newVal.replace(/\s/g, '').match(/^[0-9]{0,19}$/)) {
          this.paymentForm.cardNumber = oldValue;
        }

        this.$parent.setPaymentMethodByCardNumber(newVal);
      },
      'paymentForm.zipcode' (zipcode) {
        if (this.countryCode === 'br' && !this.$v.form.zipcode.$invalid) {
          this.getLocationByZipcode(zipcode)
        }
      },
/*
      'paymentForm.dateOfBirth' (val) {
        let result = ''
        const configForSlash = [2, 5]
        for (let i = 0; i < val.length; i++) {
          if (configForSlash.includes(i)) {
            result += '/'
          }

          if (!isNaN(val[i]) && val[i] !== ' ') {
            result += val[i]
          }
        }

        this.paymentForm.dateOfBirth = result
      },
*/
      'paymentForm.cvv' (newVal, oldValue) {
        if(this.paymentForm.cvv) {
            if(newVal.match(/^[0-9]{0,4}$/g)) {
                this.paymentForm.cvv = newVal;
            } else {
                this.paymentForm.cvv = oldValue;
            }
        }
      }
    },
    methods: {
      setCountryCodeByPhoneField (val) {
        if (val.iso2) {
          this.paymentForm.countryCodePhoneField = val.iso2;
        }
      },
      openCVVModal () {
        const node = document.querySelector('.cvv-popup .el-dialog')
        const listener = () => {
            this.isOpenCVVModal = false
        }
        node.removeEventListener('click', listener)
        node.addEventListener('click', listener)

        this.isOpenCVVModal = true
      },
      getLocationByZipcode: debounce(function (zipcode) {
        const { ebanxpay: { url, integration_key } } = apiUrlList
        this.isLoading.address = true

        return Promise.resolve()
          .then(() => {
            return fetch('https://cors-anywhere.herokuapp.com/' + url + '/ws/zipcode?integration_key=' + encodeURIComponent(integration_key) + '&zipcode=' + encodeURIComponent(zipcode), {
              method: 'post',
            });
          })
          .then(res => res.json())
          .then(res => {
            this.isLoading.address = false

            if (res.status === 'ERROR') return

            const { zipcode: { address, city, state } } = res

            this.$emit('setAddress', {
              street: address,
              city,
              state,
            })
          })
          .catch((err) => {
            console.error(err)
            this.isLoading.address = false
          })
      }, 333),
      submit () {
        const { paymentForm, exp } = this;

        this.$v.form.$touch();

        if (this.$v.form.deal.$invalid) {
          const element = document.querySelector('.main__deal');

          if (element && element.scrollIntoView) {
            element.scrollIntoView();
          }

          this.$emit('setPromotionalModal', true);
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

        const phoneNumber = paymentForm.phone.replace(/[^0-9]/g, '');
        const cardNumber = paymentForm.cardNumber.replace(/\s/g, '');

        if (paymentForm.emailForceInvalid) {
          return setTimeout(() => {
            this.paymentError = t('checkout.abuse_error');
            this.isSubmitted = false;
          }, 1000);
        }

        let data = {
          deal: paymentForm.deal,
          variant: paymentForm.variant,
          isWarrantyChecked: paymentForm.isWarrantyChecked,
          paymentProvider: paymentForm.paymentProvider,
          fname: paymentForm.fname,
          lname: paymentForm.lname,
          //dateOfBirth: paymentForm.dateOfBirth,
          email: paymentForm.email,
          phone: paymentForm.phone,
          countryCodePhoneField: paymentForm.countryCodePhoneField,
          street: paymentForm.street,
          city: paymentForm.city,
          state: paymentForm.state,
          zipcode: paymentForm.zipcode,
          country: paymentForm.country,
        };

        this.$parent.setExtraFieldsForLocalStorage(data);
        this.setDataToLocalStorage(data);

        Promise.resolve()
          .then(() => {
            if (this.ipqsResult) {
              return this.ipqsResult;
            }

            let data = {
              order_amount: this.getOrderAmount(paymentForm.deal, paymentForm.isWarrantyChecked),
              billing_first_name: paymentForm.fname,
              billing_last_name: paymentForm.lname,
              billing_country: paymentForm.country,
              billing_address_1: paymentForm.street,
              billing_city: paymentForm.city,
              billing_region: paymentForm.state,
              billing_postcode: paymentForm.zipcode,
              billing_email: paymentForm.email,
              billing_phone: this.dialCode + phoneNumber,
            };

            if (paymentForm.paymentProvider === 'credit-card') {
              data = {
                ...data,
                credit_card_bin: cardNumber.substr(0, 6),
                credit_card_hash: window.sha256(cardNumber),
                credit_card_expiration_month: ('0' + paymentForm.month).slice(-2),
                credit_card_expiration_year: ('' + paymentForm.year).substr(2, 2),
                cvv_code: paymentForm.cvv,
              };
            }

            return ipqsCheck(data);
          })
          .then(ipqsResult => {
            this.ipqsResult = ipqsResult;
          })
          .then(() => {
            /*
            if (this.ipqsResult && this.ipqsResult.recent_abuse) {
              return setTimeout(() => {
                this.paymentError = t('checkout.abuse_error');
                this.isSubmitted = false;
              }, 1000);
            }
            */

            if (paymentForm.paymentProvider === 'bank-payment') {
              this.isSubmitted = false;
              this.$emit('showCart');
              return;
            }

            if (paymentForm.paymentProvider === 'credit-card') {
              let data = {
                product: {
                  sku: paymentForm.variant,
                  qty: parseInt(paymentForm.deal, 10),
                  is_warranty_checked: paymentForm.isWarrantyChecked,
                },
                contact: {
                  phone: {
                    country_code: this.dialCode,
                    number: phoneNumber,
                  },
                  first_name: paymentForm.fname,
                  last_name: paymentForm.lname,
                  email: paymentForm.email,
                },
                address: {
                  city: paymentForm.city,
                  country: paymentForm.country,
                  zip: paymentForm.zipcode,
                  state: paymentForm.state,
                  street: paymentForm.street,
                },
                card: {
                  number: cardNumber,
                  cvv: paymentForm.cvv,
                  month: ('0' + paymentForm.month).slice(-2),
                  year: '' + paymentForm.year,
                },
                ipqs: this.ipqsResult,
              };

              if (this.$root.isAffIDEmpty) {
                data.card.holder = paymentForm.cardHolder;
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
          })
      }
    },
  };
</script>

<style lang="scss">
    @import "../../../sass/variables";

    .payment-form {
        h2 {
            width: 100%;
        }

        .cvv-popup {
          .el-dialog {
            margin-top: 10vh !important;
              max-width: 600px;
          }
          .el-dialog__header {
              display: flex;
              justify-content: center;
          }
          .el-dialog__title {
              text-align: center;
              font-size: 20px;
              font-weight: 700;
          }

           &__content {
               display: flex;
               flex-direction: column;
               align-items: center;

               p {
                 font-family: 'Noto Sans', sans-serif;
                 font-size: 17px;
                   width: 100%;
               }

               img {
                   max-width: 300px;
                   height: auto;
                   margin: 0 auto;
               }
           }
        }
        display: flex;

        .variant-1, .date-picker-manual {
            margin-bottom: 10px;
        }

        .variant-1 {
            .fa.fa-question-circle {
                cursor: pointer;
            }
        }

        &__delivery-address {
            display: flex;
            flex-wrap: wrap;
            width: 100%;

            .input-container, .select {
                order: 1;
            }

            .el-loading-mask {
                top: 26px;
                background-color: rgba(255, 255, 255, .5);

                .el-loading-spinner {
                    margin-top: 0;
                    transform: translateY(-50%);
                    top: 20px;
                }
            }
        }

        &.is-brazil {
            .payment-form__delivery-address {
                #zip-code-field {
                    order: 0;
                }
            }
        }

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

        .card-number {
            .prefix {
                & > img {
                    height: 22px;
                    width: auto;
                }
                input {
                    &:after {
                        content: '\f023';
                        display: block;
                        color: #555;
                        font-family: FontAwesome !important;
                        position: absolute;
                        top: 8px;
                        right: 15px;
                    }
                }
            }
        }

        .card-date {
            display: flex;
            flex-wrap: wrap;
            width: 70%;
            padding-right: 30px;
            margin-bottom: 10px;

            [dir="rtl"] & {
              padding-left: 30px;
              padding-right: 0;
            }

            &.with-error {
                & > .label {
                    color: #e74c3c;
                }
            }

            & > .label {
                width: 100%;
                margin-bottom: 6px;
            }

            & > div {
                width: calc(40% - 5px);
                margin-right: 10px;

                [dir="rtl"] & {
                  margin-left: 10px;
                  margin-right: 0;
                }

                &:last-child {
                    margin-left: 0;
                    margin-right: 0;
                    width: calc(60% - 5px);
                }
            }
        }

        .cvv-field {
            width: calc(30%);
        }

        @media screen and ($s-down) {
            .el-dialog {
                width: 90%;
                margin-top: 15% !important;
            }
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

        #payment-error {
          font-size: 17px;
          text-align: center;
          width: 100%;
        }
    }

  .warranty-field {
    &-button {
      width: 100%;
      position: relative;
      display: flex;
      padding: 0;
      margin: 40px 0;
      background: linear-gradient(to bottom, #f7f7f7, #dddddd);
      border: 1px solid #a5a5a5;
      transition: all .3s;
      &:hover, &:focus, &:active {
          background: linear-gradient(to bottom, #dddddd, #f7f7f7);
      }
      .label-container-checkbox {
          margin: 0;
          padding: 13px 10px 22px 75px;
          font-size: 18px;

          [dir="rtl"] & {
            padding: 13px 75px 22px 10px;
          }

          .checkmark {
              left: 40px;
              top: 13px;

              [dir="rtl"] & {
                left: auto;
                right: 40px;
              }
          }
      }

      .fa-arrow-left {
        display: none;
        position: absolute;
        right: 10px;
        top: 13px;
        z-index: 2;
        

        [dir="rtl"] & {
          display: block;
        }
      }

      .fa-arrow-right {
        position: absolute;
        left: 10px;
        top: 13px;
        z-index: 2;

        [dir="rtl"] & {
          display: none;
        }
      }
    }

    &-icon {
      position: absolute;
      right: -15px;
      top: -15px;
      width: 30px;
      height: 30px;

      [dir="rtl"] & {
        left: -15px;
        right: auto;
        transform: rotate(-24deg);
      }

      img {
        max-width: 100%;
      }
    }
  }

  #payment-data-form {
    display: flex;
    flex-wrap: wrap;
  }

  @media screen and ($s-down) {
    .cvv-popup {
      .el-dialog {
        width: 90%;
      }
    }
}
</style>
